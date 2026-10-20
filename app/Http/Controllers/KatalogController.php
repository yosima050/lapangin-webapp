<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JadwalService;
use App\Models\Lapangan;

class KatalogController extends Controller
{
    protected $jadwalService;

    public function __construct(JadwalService $jadwalService)
    {
        $this->jadwalService = $jadwalService;
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'kategori' => ['nullable', 'string'],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $kategori = $validated['kategori'] ?? $request->query('kategori');
        $tanggal = !empty($validated['tanggal']) ? $validated['tanggal'] : now()->toDateString();

        $query = Lapangan::with('hargaMasters');

        if ($kategori && $kategori !== 'Semua') {
            $query->where('kategori', $kategori);
        }

        $lapangan = $query->get();
        $kategoriList = Lapangan::distinct()->whereNotNull('kategori')->orderBy('kategori')->pluck('kategori');

        $dataKatalog = $lapangan->map(function ($item) use ($tanggal) {
            $slotData = $this->jadwalService->getSlotStatus((string) $item->id, $tanggal);

            $slotData['foto_url'] = $item->foto_url;
            $slotData['deskripsi'] = $item->deskripsi;
            $slotData['harga_weekday'] = $item->hargaMasters->whereNull('tanggal_khusus')->where('jenis_hari', 'weekday')->first()?->harga;
            $slotData['harga_weekend'] = $item->hargaMasters->whereNull('tanggal_khusus')->where('jenis_hari', 'weekend')->first()?->harga;
            $baseDate = \Carbon\Carbon::parse($tanggal)->startOfDay();
            $dayOfWeek = $baseDate->dayOfWeek;
            $isWeekend = in_array($dayOfWeek, [0, 6]);
            $jenisHari = $isWeekend ? 'weekend' : 'weekday';

            $prioritizedRates = [];
            $hoursToCheck = [];

            if (!empty($slotData['slots'])) {
                foreach ($slotData['slots'] as $slot) {
                    $slotStart = $baseDate->copy()->setTimeFromTimeString(substr($slot['jam_mulai'], 0, 5));
                    $slotSelesaiStr = substr($slot['jam_selesai'], 0, 5);
                    $slotEnd = ($slotSelesaiStr === '24:00' || $slotSelesaiStr === '00:00')
                        ? $baseDate->copy()->addDay()->startOfDay()
                        : $baseDate->copy()->setTimeFromTimeString($slotSelesaiStr);
                    $hoursToCheck[] = [$slotStart, $slotEnd];
                }
            } else {
                for ($h = 0; $h < 24; $h++) {
                    $slotStart = $baseDate->copy()->addHours($h);
                    $slotEnd = $slotStart->copy()->addHour();
                    $hoursToCheck[] = [$slotStart, $slotEnd];
                }
            }

            foreach ($hoursToCheck as [$slotStart, $slotEnd]) {
                $customPrice = $item->hargaMasters->first(function ($hm) use ($tanggal, $baseDate, $slotStart, $slotEnd) {
                    if (!$hm->tanggal_khusus || $hm->tanggal_khusus->toDateString() !== $tanggal) {
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
                });

                if ($customPrice) {
                    $prioritizedRates[] = (float) $customPrice->harga;
                } else {
                    $defaultPrice = $item->hargaMasters->first(function ($hm) use ($jenisHari, $baseDate, $slotStart, $slotEnd) {
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
                    });

                    if ($defaultPrice) {
                        $prioritizedRates[] = (float) $defaultPrice->harga;
                    }
                }
            }

            if (!empty($prioritizedRates)) {
                $minApplicable = min($prioritizedRates);
            } else {
                $minApplicable = $item->hargaMasters->whereNull('tanggal_khusus')->min('harga');
            }

            $slotData['harga_termurah'] = $minApplicable ?? 100000;
            $slotData['lapangan'] = $item;

            return $slotData;
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $dataKatalog,
                'kategori_list' => $kategoriList,
            ]);
        }

        return view('katalog.index', [
            'katalog' => $dataKatalog,
            'kategori' => $kategori,
            'kategoriList' => $kategoriList,
            'tanggal' => $tanggal,
        ]);
    }

    public function show(Request $request, $id)
    {
        // Ambil data lapangan dari database beserta master tarif
        $lapangan = Lapangan::with('hargaMasters')->findOrFail($id);

        // Ambil tanggal dari request (default: hari ini)
        $tanggal = $request->query('tanggal', now()->toDateString());

        // Ambil status ketersediaan jam dari JadwalService
        $jadwalData = $this->jadwalService->getSlotStatus((string) $id, $tanggal);

        $hargaWeekday = $lapangan->hargaMasters
            ->whereNull('tanggal_khusus')
            ->where('jenis_hari', 'weekday')
            ->first()?->harga;

        $hargaWeekend = $lapangan->hargaMasters
            ->whereNull('tanggal_khusus')
            ->where('jenis_hari', 'weekend')
            ->first()?->harga;

        return view('lapangan.show', [
            'lapangan' => $lapangan,
            'jadwalData' => $jadwalData,
            'tanggalDipilih' => $tanggal,
            'hargaWeekday' => $hargaWeekday,
            'hargaWeekend' => $hargaWeekend,
        ]);
    }
}