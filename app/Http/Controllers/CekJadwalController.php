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

        // Query seluruh lapangan aktif sekali saja (bebas query berulang)
        $allActiveLapangans = Lapangan::with('hargaMasters')
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        $kategoriList = $allActiveLapangans->pluck('kategori')
            ->filter(fn ($k) => $k !== null && $k !== '')
            ->unique()
            ->sort()
            ->values();

        $allLapangans = $allActiveLapangans->map(fn ($l) => (object) [
            'id' => $l->id,
            'nama' => $l->nama,
            'kategori' => $l->kategori,
        ]);

        $lapangans = $allActiveLapangans;
        if ($kategori && $kategori !== 'Semua') {
            $lapangans = $lapangans->where('kategori', $kategori)->values();
        }
        if (!empty($selectedLapanganId)) {
            $lapangans = $lapangans->where('id', $selectedLapanganId)->values();
        }

        // Generate 7 hari cepat navigasi kalender (geser jendela jika tanggal dipilih > 6 hari dari hari ini)
        $currentDateCarbon = Carbon::parse($tanggal, 'Asia/Jakarta')->startOfDay();
        $todayCarbon = Carbon::parse($todayWib, 'Asia/Jakarta')->startOfDay();
        $windowStart = $currentDateCarbon->gt($todayCarbon->copy()->addDays(6))
            ? $currentDateCarbon->copy()
            : $todayCarbon->copy();

        $dateOptions = [];
        for ($i = 0; $i < 7; $i++) {
            $d = $windowStart->copy()->addDays($i);
            $dateStr = $d->toDateString();
            $diffDays = (int) $todayCarbon->diffInDays($d, false);
            $labelTop = ($diffDays === 0)
                ? 'HARI INI'
                : (($diffDays === 1) ? 'BESOK' : strtoupper($d->locale('id')->translatedFormat('D')));

            $dateOptions[] = [
                'date' => $dateStr,
                'label_top' => $labelTop,
                'day_num' => $d->format('d'),
                'month_name' => $d->locale('id')->translatedFormat('M'),
                'is_active' => ($dateStr === $tanggal),
            ];
        }

        // Ambil data matriks slot per lapangan secara batch (bebas N+1)
        $bulkSlotData = $this->jadwalService->getBulkSlotStatus($lapangans, $tanggal);
        $matrix = [];
        $totalSlotTersedia = 0;
        $totalSlotDibooking = 0;
        $totalSlotLewat = 0;

        foreach ($lapangans as $lap) {
            $slotData = $bulkSlotData[(string) $lap->id] ?? [];

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
                'dateOptions' => $dateOptions,
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

