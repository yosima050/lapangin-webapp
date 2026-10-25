<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Perlengkapan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PerlengkapanController extends Controller
{
    public function index()
    {
        $perlengkapans = Perlengkapan::orderBy('nama')->paginate(10);

        return view('Admin.perlengkapan.index', compact('perlengkapans'));
    }

    public function create()
    {
        return view('Admin.perlengkapan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')
                ->store('perlengkapan', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active');

        Perlengkapan::create($validated);

        return redirect()
            ->route('admin.perlengkapan.index')
            ->with('success', 'Perlengkapan berhasil ditambahkan.');
    }

    public function edit(Perlengkapan $perlengkapan)
    {
        return view('Admin.perlengkapan.edit', compact('perlengkapan'));
    }

    public function update(Request $request, Perlengkapan $perlengkapan)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'nullable|boolean',
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

        $perlengkapan->delete();

        return redirect()
            ->route('admin.perlengkapan.index')
            ->with('success', 'Perlengkapan berhasil dihapus.');
    }
}
