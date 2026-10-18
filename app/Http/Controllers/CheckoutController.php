<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use App\Models\HargaMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'lapangan_id' => 'required|uuid|exists:lapangans,id',
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'durasi' => 'required|integer|min:1|max:4',
        ]);

        $lapangan = Lapangan::with('hargaMasters')
            ->findOrFail($data['lapangan_id']);

        $baseDate = Carbon::parse($data['tanggal'])->startOfDay();
        $jamBuka = $baseDate->copy()->setTimeFromTimeString(substr($lapangan->jam_buka, 0, 5));
        $jamTutupStr = substr($lapangan->jam_tutup, 0, 5);
        $jamTutup = ($jamTutupStr === '24:00' || $jamTutupStr === '00:00')
            ? $baseDate->copy()->addDay()->startOfDay()
            : $baseDate->copy()->setTimeFromTimeString($jamTutupStr);

        $mulai = $baseDate->copy()->setTimeFromTimeString($data['jam_mulai']);
        $durasi = (int) $data['durasi'];
        $selesai = $mulai->copy()->addHours($durasi);

        // Verify requested interval falls within operating hours
        if ($mulai->lt($jamBuka) || $selesai->gt($jamTutup)) {
            abort(422, 'Interval jadwal yang dipilih berada di luar jam operasional lapangan.');
        }

        // Verify slots are not already occupied
        $isOccupied = \App\Models\Jadwal::where('lapangan_id', $lapangan->id)
            ->whereDate('tanggal', $data['tanggal'])
            ->whereNotIn('status', ['dibatalkan', 'cancelled', 'rejected'])
            ->get()
            ->contains(function ($jadwal) use ($baseDate, $mulai, $selesai) {
                $jMulai = $baseDate->copy()->setTimeFromTimeString(substr($jadwal->jam_mulai, 0, 5));
                $jSelesaiStr = substr($jadwal->jam_selesai, 0, 5);
                $jSelesai = ($jSelesaiStr === '24:00' || $jSelesaiStr === '00:00')
                    ? $baseDate->copy()->addDay()->startOfDay()
                    : $baseDate->copy()->setTimeFromTimeString($jSelesaiStr);

                return $mulai->lt($jSelesai) && $selesai->gt($jMulai);
            });

        if ($isOccupied) {
            abort(422, 'Slot jadwal yang dipilih telah terisi atau tidak tersedia.');
        }

        $dayOfWeek = Carbon::parse($data['tanggal'])->dayOfWeek;
        $isWeekend = in_array($dayOfWeek, [0, 6]);
        $jenisHari = $isWeekend ? 'weekend' : 'weekday';

        $totalHarga = 0;
        $hourlyRates = [];

        for ($i = 0; $i < $durasi; $i++) {
            $slotStart = $mulai->copy()->addHours($i);
            $slotEnd = $slotStart->copy()->addHour();

            // Match custom rate on this date matching the hourly interval
            $customPrice = $lapangan->hargaMasters
                ->filter(function ($hm) use ($data, $baseDate, $slotStart, $slotEnd) {
                    if (!$hm->tanggal_khusus || $hm->tanggal_khusus->toDateString() !== $data['tanggal']) {
                        return false;
                    }
                    if (!$hm->jam_mulai || !$hm->jam_selesai) {
                        return true;
                    }
                    $hmMulai = $baseDate->copy()->setTimeFromTimeString(substr($hm->jam_mulai, 0, 5));
                    $hmSelesaiStr = substr($hm->jam_selesai, 0, 5);
                    $hmSelesai = ($hmSelesaiStr === '24:00' || $hmSelesaiStr === '00:00')
                        ? $baseDate->copy()->addDay()->startOfDay()
                        : $baseDate->copy()->setTimeFromTimeString($hmSelesaiStr);

                    return $slotStart->gte($hmMulai) && $slotEnd->lte($hmSelesai);
                })
                ->first();

            if ($customPrice) {
                $rate = (float) $customPrice->harga;
            } else {
                // Match default rate for the day type matching the hourly interval
                $defaultPrice = $lapangan->hargaMasters
                    ->filter(function ($hm) use ($jenisHari, $baseDate, $slotStart, $slotEnd) {
                        if ($hm->tanggal_khusus !== null || $hm->jenis_hari !== $jenisHari) {
                            return false;
                        }
                        if (!$hm->jam_mulai || !$hm->jam_selesai) {
                            return true;
                        }
                        $hmMulai = $baseDate->copy()->setTimeFromTimeString(substr($hm->jam_mulai, 0, 5));
                        $hmSelesaiStr = substr($hm->jam_selesai, 0, 5);
                        $hmSelesai = ($hmSelesaiStr === '24:00' || $hmSelesaiStr === '00:00')
                            ? $baseDate->copy()->addDay()->startOfDay()
                            : $baseDate->copy()->setTimeFromTimeString($hmSelesaiStr);

                        return $slotStart->gte($hmMulai) && $slotEnd->lte($hmSelesai);
                    })
                    ->first();

                if (!$defaultPrice) {
                    $defaultPrice = $lapangan->hargaMasters
                        ->whereNull('tanggal_khusus')
                        ->where('jenis_hari', $jenisHari)
                        ->first();
                }

                $rate = (float) ($defaultPrice?->harga ?? 150000);
            }

            $hourlyRates[] = $rate;
            $totalHarga += $rate;
        }

        $hargaPerJam = $durasi > 0 ? ($totalHarga / $durasi) : 0;

        $pelanggan = Auth::user();

        $dpPersen = 50;
        $nominalDp = round($totalHarga * $dpPersen / 100);
        $sisaPembayaran = $totalHarga - $nominalDp;

        return view('checkout.index', [
            'lapangan' => $lapangan,
            'tanggal' => $data['tanggal'],
            'jamMulai' => $mulai->format('H:i'),
            'jamSelesai' => $selesai->format('H:i'),
            'durasi' => $durasi,
            'hargaPerJam' => $hargaPerJam,
            'totalHarga' => $totalHarga,
            'dpPersen' => $dpPersen,
            'nominalDp' => $nominalDp,
            'sisaPembayaran' => $sisaPembayaran,
            'pelanggan' => $pelanggan
                ]);
                
            }
        }
