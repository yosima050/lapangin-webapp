<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use App\Models\HargaMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

        $mulai = Carbon::createFromFormat('H:i', $data['jam_mulai']);
        $selesai = $mulai->copy()->addHours((int) $data['durasi']);

        $dayOfWeek = Carbon::parse($data['tanggal'])->dayOfWeek;
        $isWeekend = in_array($dayOfWeek, [0, 6]);

        $customPrice = $lapangan->hargaMasters
            ->firstWhere('tanggal_khusus', $data['tanggal']);

        if ($customPrice) {
            $hargaPerJam = (float) $customPrice->harga;
        } else {
            $defaultPrice = $lapangan->hargaMasters
                ->whereNull('tanggal_khusus')
                ->where('jenis_hari', $isWeekend ? 'weekend' : 'weekday')
                ->first();

            $hargaPerJam = (float) ($defaultPrice?->harga ?? 150000);
        }

        $totalHarga = $hargaPerJam * (int) $data['durasi'];

        return view('checkout.index', [
            'lapangan' => $lapangan,
            'tanggal' => $data['tanggal'],
            'jamMulai' => $mulai->format('H:i'),
            'jamSelesai' => $selesai->format('H:i'),
            'durasi' => (int) $data['durasi'],
            'hargaPerJam' => $hargaPerJam,
            'totalHarga' => $totalHarga,
        ]);
    }
}
