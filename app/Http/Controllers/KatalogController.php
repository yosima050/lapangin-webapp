<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JadwalService;
use App\Models\Lapangan;
use App\Models\Perlengkapan;

class KatalogController extends Controller
{
    protected $jadwalService;

    public function __construct(JadwalService $jadwalService)
    {
        $this->jadwalService = $jadwalService;
    }

    public function index(Request $request)
    {
        $todayWib = \Carbon\Carbon::now('Asia/Jakarta')->toDateString();

        $validated = $request->validate([
            'kategori' => ['nullable', 'string'],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $kategori = $validated['kategori'] ?? null;
        $rawTanggal = ! empty($validated['tanggal']) ? $validated['tanggal'] : $todayWib;

        // Cegah pemilihan tanggal kemarin / lampau
        $tanggal = ($rawTanggal < $todayWib) ? $todayWib : $rawTanggal;

        // Ambil semua lapangan aktif agar filter Alpine.js di browser instan tanpa reload
        $lapangan = Lapangan::with('hargaMasters')->where('is_active', true)->get();
        $kategoriList = Lapangan::where('is_active', true)
            ->distinct()
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->orderBy('kategori')
            ->pluck('kategori');

        $dataKatalog = $lapangan->map(function ($item) use ($tanggal) {
            $slotData = $this->jadwalService->getSlotStatus((string) $item->id, $tanggal);

            $slotData['foto_url'] = $item->foto_url;
            $slotData['deskripsi'] = $item->deskripsi;
            $slotData['harga_weekday'] = $item->hargaMasters->whereNull('tanggal_khusus')->where('jenis_hari', 'weekday')->first()?->harga;
            $slotData['harga_weekend'] = $item->hargaMasters->whereNull('tanggal_khusus')->where('jenis_hari', 'weekend')->first()?->harga;
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
        $validated = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
        ]);

        // Ambil data lapangan aktif dari database beserta master tarif
        $lapangan = Lapangan::with('hargaMasters')->where('is_active', true)->findOrFail($id);

        $todayWib = \Carbon\Carbon::now('Asia/Jakarta')->toDateString();
        $rawTanggal = ! empty($validated['tanggal']) ? $validated['tanggal'] : $todayWib;
        $tanggal = ($rawTanggal < $todayWib) ? $todayWib : $rawTanggal;

        // Ambil status ketersediaan jam dari JadwalService
        $jadwalData = $this->jadwalService->getSlotStatus((string) $id, $tanggal);

        $selectedDateCarbon = \Carbon\Carbon::parse($tanggal)->startOfDay();
        $today = now()->startOfDay();

        $datesCarousel = [];
        for ($i = 0; $i < 7; $i++) {
            $cDate = $today->copy()->addDays($i);
            $datesCarousel[] = [
                'date_str' => $cDate->toDateString(),
                'day_num' => $cDate->format('d'),
                'day_name' => $cDate->translatedFormat('D'),
                'month_name' => $cDate->translatedFormat('M'),
                'label_top' => $i === 0 ? 'HARI INI' : ($i === 1 ? 'BESOK' : strtoupper($cDate->translatedFormat('D'))),
                'is_selected' => $cDate->toDateString() === $tanggal,
            ];
        }

        $slotsSiang = [];
        $slotsMalam = [];
        foreach ($jadwalData['slots'] as $slot) {
            $jamHour = (int) substr($slot['jam_mulai'], 0, 2);
            if ($jamHour < 18) {
                $slotsSiang[] = $slot;
            } else {
                $slotsMalam[] = $slot;
            }
        }

        $hargaWeekday = $lapangan->hargaMasters
            ->whereNull('tanggal_khusus')
            ->where('jenis_hari', 'weekday')
            ->first()?->harga;

        $hargaWeekend = $lapangan->hargaMasters
            ->whereNull('tanggal_khusus')
            ->where('jenis_hari', 'weekend')
            ->first()?->harga;

        $isWeekend = in_array($selectedDateCarbon->dayOfWeek, [0, 6]);
        $baseRate = (float) ($jadwalData['base_rate'] ?? ($isWeekend ? ($hargaWeekend ?? 175000) : ($hargaWeekday ?? 125000)));
        $primeRate = (float) ($jadwalData['prime_rate'] ?? ($baseRate + 40000));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'tanggal' => $tanggal,
                'formattedDate' => $selectedDateCarbon->translatedFormat('l, d F Y'),
                'monthYear' => $selectedDateCarbon->translatedFormat('F Y'),
                'isWeekend' => $isWeekend,
                'baseRate' => $baseRate,
                'primeRate' => $primeRate,
                'slotsSiang' => $slotsSiang,
                'slotsMalam' => $slotsMalam,
                'datesCarousel' => $datesCarousel,
            ]);
        }

        // Ambil perlengkapan aktif yang tersedia untuk lapangan ini (atau tersedia untuk semua lapangan jika tidak ada pembatasan relasi)
        $perlengkapans = Perlengkapan::where('is_active', true)
            ->where(function ($q) use ($id) {
                $q->whereHas('lapangans', fn ($l) => $l->where('lapangan_id', $id))
                  ->orDoesntHave('lapangans');
            })
            ->orderBy('nama')
            ->get();

        $semuaLapangan = Lapangan::all();

        // Precompute jadwal 7 hari ke depan agar client bisa render instan tanpa loading
        $initialDaysCache = [];
        foreach ($datesCarousel as $d) {
            $cDateStr = $d['date_str'];
            $cDateCarbon = \Carbon\Carbon::parse($cDateStr)->startOfDay();
            $cIsWeekend = in_array($cDateCarbon->dayOfWeek, [0, 6]);

            if ($cDateStr === $tanggal) {
                $initialDaysCache[$cDateStr] = [
                    'slotsSiang' => $slotsSiang,
                    'slotsMalam' => $slotsMalam,
                    'formattedDate' => $cDateCarbon->translatedFormat('l, d F Y'),
                    'monthYear' => $cDateCarbon->translatedFormat('F Y'),
                    'baseRate' => $baseRate,
                    'primeRate' => $primeRate,
                ];
            } else {
                $cSlotData = $this->jadwalService->getSlotStatus((string) $id, $cDateStr);
                $cSlotsSiang = [];
                $cSlotsMalam = [];
                foreach ($cSlotData['slots'] as $s) {
                    $jamH = (int) substr($s['jam_mulai'], 0, 2);
                    if ($jamH < 18) {
                        $cSlotsSiang[] = $s;
                    } else {
                        $cSlotsMalam[] = $s;
                    }
                }

                $cBaseRate = (float) ($cSlotData['base_rate'] ?? ($cIsWeekend ? ($hargaWeekend ?? 175000) : ($hargaWeekday ?? 125000)));
                $cPrimeRate = (float) ($cSlotData['prime_rate'] ?? ($cBaseRate + 40000));

                $initialDaysCache[$cDateStr] = [
                    'slotsSiang' => $cSlotsSiang,
                    'slotsMalam' => $cSlotsMalam,
                    'formattedDate' => $cDateCarbon->translatedFormat('l, d F Y'),
                    'monthYear' => $cDateCarbon->translatedFormat('F Y'),
                    'baseRate' => $cBaseRate,
                    'primeRate' => $cPrimeRate,
                ];
            }
        }

        return view('lapangan.show', [
            'lapangan' => $lapangan,
            'jadwalData' => $jadwalData,
            'tanggalDipilih' => $tanggal,
            'hargaWeekday' => $hargaWeekday,
            'hargaWeekend' => $hargaWeekend,
            'perlengkapans' => $perlengkapans,
            'semuaLapangan' => $semuaLapangan,
            'datesCarousel' => $datesCarousel,
            'slotsSiang' => $slotsSiang,
            'slotsMalam' => $slotsMalam,
            'baseRate' => $baseRate,
            'primeRate' => $primeRate,
            'initialDaysCache' => $initialDaysCache,
        ]);
    }
}