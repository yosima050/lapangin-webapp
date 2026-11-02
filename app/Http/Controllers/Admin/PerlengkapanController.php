<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lapangan;
use App\Models\Perlengkapan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PerlengkapanController extends Controller
{
    public function index()
    {
        $perlengkapans = Perlengkapan::with('lapangans')->orderBy('nama')->paginate(10);

        return view('Admin.perlengkapan.index', compact('perlengkapans'));
    }

    public function create()
    {
        $lapangans = Lapangan::orderBy('nama')->get();

        return view('Admin.perlengkapan.create', compact('lapangans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'nullable|boolean',
            'lapangan_ids' => 'nullable|array',
            'lapangan_ids.*' => 'uuid|exists:lapangans,id',
        ]);

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')
                ->store('perlengkapan', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active');

        $perlengkapan = Perlengkapan::create($validated);

        if ($request->has('lapangan_ids')) {
            $perlengkapan->lapangans()->sync($request->input('lapangan_ids', []));
        }

        return redirect()
            ->route('admin.perlengkapan.index')
            ->with('success', 'Perlengkapan berhasil ditambahkan.');
    }

    public function edit(Perlengkapan $perlengkapan)
    {
        $perlengkapan->load('lapangans');
        $lapangans = Lapangan::orderBy('nama')->get();

        return view('Admin.perlengkapan.edit', compact('perlengkapan', 'lapangans'));
    }

    public function update(Request $request, Perlengkapan $perlengkapan)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'nullable|boolean',
            'lapangan_ids' => 'nullable|array',
            'lapangan_ids.*' => 'uuid|exists:lapangans,id',
        ]);

        if ($request->hasFile('gambar')) {
            if ($perlengkapan->gambar) {
                Storage::disk('public')->delete($perlengkapan->gambar);
            }

            $validated['gambar'] = $request->file('gambar')
                ->store('perlengkapan', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active');

        $perlengkapan->update($validated);

        // Sinkronisasi pilihan lapangan
        $perlengkapan->lapangans()->sync($request->input('lapangan_ids', []));

        return redirect()
            ->route('admin.perlengkapan.index')
            ->with('success', 'Perlengkapan berhasil diperbarui.');
    }

    public function destroy(Perlengkapan $perlengkapan)
    {
        if ($perlengkapan->gambar) {
            \Illuminate\Support\Facades\Storage::disk('public')
                ->delete($perlengkapan->gambar);
        }

        $perlengkapan->lapangans()->detach();
        $perlengkapan->delete();

        return redirect()
            ->route('admin.perlengkapan.index')
            ->with('success', 'Perlengkapan berhasil dihapus.');
    }
}
