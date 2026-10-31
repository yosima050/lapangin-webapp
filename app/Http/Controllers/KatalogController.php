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
            'tanggal' => ['nullable', 'date'],
        ]);

        $kategori = $validated['kategori'] ?? $request->query('kategori');
        $rawTanggal = !empty($validated['tanggal']) ? $validated['tanggal'] : $todayWib;

        // Cegah pemilihan tanggal kemarin / lampau
        $tanggal = ($rawTanggal < $todayWib) ? $todayWib : $rawTanggal;

        // Ambil semua lapangan agar filter Alpine.js di browser instan tanpa reload
        $lapangan = Lapangan::with('hargaMasters')->get();
        $kategoriList = Lapangan::distinct()
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

        $todayWib = \Carbon\Carbon::now('Asia/Jakarta')->toDateString();
        $rawTanggal = $request->query('tanggal', $todayWib);
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
        $baseRate = (float) ($isWeekend ? ($hargaWeekend ?? 175000) : ($hargaWeekday ?? 125000));
        $primeRate = (float) ($baseRate + 40000);

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

        // Ambil data perlengkapan dan list lapangan hanya jika request awal (non-ajax)
        $perlengkapans = Perlengkapan::where('is_active', true)->get();
        $semuaLapangan = Lapangan::all();

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
        ]);
    }
}