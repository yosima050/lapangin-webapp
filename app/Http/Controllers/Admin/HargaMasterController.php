<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HargaMasterRequest;
use App\Models\HargaMaster;
use App\Models\Lapangan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HargaMasterController extends Controller
{
    public function index(Request $request): View
    {
        $query = HargaMaster::with('lapangan')->orderBy('lapangan_id');

        if ($request->filled('lapangan_id')) {
            $query->where('lapangan_id', $request->query('lapangan_id'));
        }

        if ($request->filled('jenis_hari')) {
            $query->where('jenis_hari', $request->query('jenis_hari'));
        }

        $hargaMasters = $query->paginate(15)->withQueryString();
        $lapangans = Lapangan::orderBy('nama')->get();

        return view('Admin.harga.index', compact('hargaMasters', 'lapangans'));
    }

    public function create(Request $request): View
    {
        $lapangans = Lapangan::orderBy('nama')->get();
        $selectedLapanganId = $request->query('lapangan_id');

        return view('Admin.harga.create', compact('lapangans', 'selectedLapanganId'));
    }

    public function store(HargaMasterRequest $request): RedirectResponse
    {
        HargaMaster::create($request->validated());

        return redirect()->route('admin.harga.index')
            ->with('success', 'Aturan tarif harga berhasil ditambahkan ke database PostgreSQL!');
    }

    public function edit(HargaMaster $harga): View
    {
        $lapangans = Lapangan::orderBy('nama')->get();

        return view('Admin.harga.edit', [
            'hargaMaster' => $harga,
            'lapangans' => $lapangans,
        ]);
    }

    public function update(HargaMasterRequest $request, HargaMaster $harga): RedirectResponse
    {
        $harga->update($request->validated());

        return redirect()->route('admin.harga.index')
            ->with('success', 'Aturan tarif harga berhasil diperbarui!');
    }

    public function destroy(HargaMaster $harga): RedirectResponse
    {
        $harga->delete();

        return redirect()->route('admin.harga.index')
            ->with('success', 'Aturan tarif harga berhasil dihapus!');
    }
}
