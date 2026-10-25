<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HargaKhusus;
use App\Models\Lapangan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HargaKhususController extends Controller
{
    public function index(): View
    {
        $hargaKhusus = HargaKhusus::with('lapangan')
            ->orderByDesc('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        $lapangans = Lapangan::orderBy('nama')->get();

        return view('Admin.harga_khusus.index', compact(
            'hargaKhusus',
            'lapangans'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateHargaKhusus($request);

        $this->pastikanTidakBentrok($validated);

        HargaKhusus::create($validated);

        return redirect()
            ->route('admin.harga-khusus.index')
            ->with('success', 'Harga khusus berhasil ditambahkan.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $hargaKhusus = HargaKhusus::findOrFail($id);

        $validated = $this->validateHargaKhusus($request);

        $this->pastikanTidakBentrok($validated, $hargaKhusus->id);

        $hargaKhusus->update($validated);

        return redirect()
            ->route('admin.harga-khusus.index')
            ->with('success', 'Harga khusus berhasil diperbarui.');
    }

    public function toggleStatus(string $id): RedirectResponse
    {
        $hargaKhusus = HargaKhusus::findOrFail($id);

        $hargaKhusus->is_active = ! $hargaKhusus->is_active;
        $hargaKhusus->save();

        return redirect()
            ->route('admin.harga-khusus.index')
            ->with('success', 'Status harga khusus berhasil diperbarui.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $hargaKhusus = HargaKhusus::findOrFail($id);
        $hargaKhusus->delete();

        return redirect()
            ->route('admin.harga-khusus.index')
            ->with('success', 'Harga khusus berhasil dihapus.');
    }

    private function validateHargaKhusus(Request $request): array
    {
        $validated = $request->validate([
            'lapangan_id' => [
                'required',
                'uuid',
                'exists:lapangans,id',
            ],
            'tanggal' => [
                'required',
                'date',
            ],
            'jam_mulai' => [
                'required',
                'date_format:H:i',
            ],
            'jam_selesai' => [
                'required',
                'date_format:H:i',
                'after:jam_mulai',
            ],
            'harga' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],
            'keterangan' => [
                'nullable',
                'string',
                'max:150',
            ],
        ]);

        $validated['is_active'] = true;

        return $validated;
    }

    private function pastikanTidakBentrok(
        array $data,
        ?string $kecualiId = null
    ): void {
        $query = HargaKhusus::query()
            ->where('lapangan_id', $data['lapangan_id'])
            ->whereDate('tanggal', $data['tanggal'])
            ->where('is_active', true)
            ->where('jam_mulai', '<', $data['jam_selesai'])
            ->where('jam_selesai', '>', $data['jam_mulai']);

        if ($kecualiId !== null) {
            $query->where('id', '!=', $kecualiId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'jam_mulai' => 'Jadwal harga khusus bertumpang tindih dengan aturan aktif lain untuk lapangan dan tanggal yang sama.',
            ]);
        }
    }
}