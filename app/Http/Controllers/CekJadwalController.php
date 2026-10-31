<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use App\Services\JadwalService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CekJadwalController extends Controller
{
    protected JadwalService $jadwalService;

    public function __construct(JadwalService $jadwalService)
    {
        $this->jadwalService = $jadwalService;
    }

    public function index(Request $request)
    {
        $todayWib = Carbon::now('Asia/Jakarta')->toDateString();

        $validated = $request->validate([
            'kategori' => ['nullable', 'string'],
            'tanggal' => ['nullable', 'date'],
            'lapangan_id' => ['nullable', 'string'],
        ]);

        $kategori = $validated['kategori'] ?? 'Semua';
        $rawTanggal = !empty($validated['tanggal']) ? $validated['tanggal'] : $todayWib;
        $tanggal = ($rawTanggal < $todayWib) ? $todayWib : $rawTanggal;
        $selectedLapanganId = $validated['lapangan_id'] ?? null;

        // Query Lapangan
        $query = Lapangan::with('hargaMasters');

        if ($kategori && $kategori !== 'Semua') {
            $query->where('kategori', $kategori);
        }

        if (!empty($selectedLapanganId)) {
            $query->where('id', $selectedLapanganId);
        }

        $lapangans = $query->orderBy('nama')->get();
        $kategoriList = Lapangan::distinct()
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->orderBy('kategori')
            ->pluck('kategori');
        $allLapangans = Lapangan::orderBy('nama')->get(['id', 'nama', 'kategori']);

        // Generate 7 hari cepat navigasi kalender
        $currentDateCarbon = Carbon::parse($tanggal, 'Asia/Jakarta')->startOfDay();
        $todayCarbon = Carbon::parse($todayWib, 'Asia/Jakarta')->startOfDay();

        $dateOptions = [];
        for ($i = 0; $i < 7; $i++) {
            $d = $todayCarbon->copy()->addDays($i);
            $dateStr = $d->toDateString();
            $labelTop = ($i === 0) ? 'HARI INI' : (($i === 1) ? 'BESOK' : strtoupper($d->locale('id')->translatedFormat('D')));
            
            $dateOptions[] = [
                'date' => $dateStr,
                'label_top' => $labelTop,
                'day_num' => $d->format('d'),
                'month_name' => $d->locale('id')->translatedFormat('M'),
                'is_active' => ($dateStr === $tanggal),
            ];
        }

        // Ambil data matriks slot per lapangan
        $matrix = [];
        $totalSlotTersedia = 0;
        $totalSlotDibooking = 0;
        $totalSlotLewat = 0;

        foreach ($lapangans as $lap) {
            $slotData = $this->jadwalService->getSlotStatus((string) $lap->id, $tanggal);

            $lapanganSlots = $slotData['slots'] ?? [];
            $tersediaCount = 0;
            $bookedCount = 0;
            $lewatCount = 0;

            foreach ($lapanganSlots as $s) {
                if ($s['is_tersedia']) {
                    $tersediaCount++;
                    $totalSlotTersedia++;
                } elseif ($s['status'] === 'lewat_jam') {
                    $lewatCount++;
                    $totalSlotLewat++;
                } else {
                    $bookedCount++;
                    $totalSlotDibooking++;
                }
            }

            $matrix[] = [
                'lapangan' => $lap,
                'slots' => $lapanganSlots,
                'jam_operasional' => $slotData['jam_operasional'],
                'tersedia_count' => $tersediaCount,
                'booked_count' => $bookedCount,
                'lewat_count' => $lewatCount,
            ];
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'tanggal' => $tanggal,
                'matrix' => $matrix,
            ]);
        }

        return view('jadwal.index', [
            'matrix' => $matrix,
            'kategori' => $kategori,
            'kategoriList' => $kategoriList,
            'allLapangans' => $allLapangans,
            'selectedLapanganId' => $selectedLapanganId,
            'tanggal' => $tanggal,
            'today' => $todayWib,
            'dateOptions' => $dateOptions,
            'totalSlotTersedia' => $totalSlotTersedia,
            'totalSlotDibooking' => $totalSlotDibooking,
            'totalSlotLewat' => $totalSlotLewat,
        ]);
    }
}

