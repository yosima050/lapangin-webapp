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
        $kategori = $request->query('kategori');
        $tanggal = $request->query('tanggal', now()->toDateString());

        $query = Lapangan::query();

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        $lapangan = $query->get();

        $dataKatalog = $lapangan->map(function ($item) use ($tanggal) {
            return $this->jadwalService->getSlotStatus((string) $item->id, $tanggal);
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $dataKatalog
            ]);
        }

        return view('katalog.index', [
            'katalog' => $dataKatalog,
            'kategori' => $kategori,
            'tanggal' => $tanggal
        ]);
    }

    public function show(Request $request, $id)
    {
        // Ambil data lapangan dari database
        $lapangan = Lapangan::findOrFail($id);
        
        // Ambil tanggal dari request (default: hari ini)
        $tanggal = $request->query('tanggal', now()->toDateString());

        // Ambil status ketersediaan jam dari JadwalService Anda
        $jadwalData = $this->jadwalService->getSlotStatus((string) $id, $tanggal);

        return view('lapangan.show', [
            'lapangan' => $lapangan,
            'jadwalData' => $jadwalData,
            'tanggalDipilih' => $tanggal
        ]);
    }
}