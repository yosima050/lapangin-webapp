<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HargaKhusus;
use App\Models\Lapangan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HargaKhususController extends Controller
{
    public function index(): View
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // Tarif khusus yang aktif & mendatang (tanggal >= hari ini)
        $hargaKhususAktif = HargaKhusus::with('lapangan')
            ->whereDate('tanggal', '>=', $today)
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        // Tarif khusus yang sudah lewat / selesai (tanggal < hari ini)
        $hargaKhususSelesai = HargaKhusus::with('lapangan')
            ->whereDate('tanggal', '<', $today)
            ->orderByDesc('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        // Siapkan data kalender untuk visualisasi
        $allHargaKhusus = HargaKhusus::with('lapangan')->get();
        $calendarEvents = [];
        foreach ($allHargaKhusus as $hk) {
            $tgl = $hk->tanggal->format('Y-m-d');
            if (! isset($calendarEvents[$tgl])) {
                $calendarEvents[$tgl] = [];
            }
            $calendarEvents[$tgl][] = [
                'id' => $hk->id,
                'lapangan' => $hk->lapangan?->nama ?? 'Semua Lapangan',
                'jam_mulai' => substr($hk->jam_mulai, 0, 5),
                'jam_selesai' => substr($hk->jam_selesai, 0, 5),
                'harga' => (float) $hk->harga,
                'keterangan' => $hk->keterangan ?: 'Harga Khusus',
                'is_active' => (bool) $hk->is_active,
                'is_past' => $tgl < $today,
            ];
        }

        $lapangans = Lapangan::orderBy('nama')->get();

        // Tetap kirim $hargaKhusus sebagai $hargaKhususAktif untuk backwards-compatibility
        $hargaKhusus = $hargaKhususAktif;

        return view('Admin.harga_khusus.index', compact(
            'hargaKhusus',
            'hargaKhususAktif',
            'hargaKhususSelesai',
            'calendarEvents',
            'lapangans',
            'today'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->input('lapangan_id') === 'all') {
            return $this->storeAllLapangans($request);
        }

        $validated = $this->validateHargaKhusus($request);

        $this->pastikanTidakBentrok($validated);

        HargaKhusus::create($validated);

        return redirect()
            ->route('admin.harga-khusus.index')
            ->with('success', 'Harga khusus berhasil ditambahkan.');
    }

    private function storeAllLapangans(Request $request): RedirectResponse
    {
        if ($request->filled('jam_mulai')) {
            $request->merge(['jam_mulai' => substr((string) $request->jam_mulai, 0, 5)]);
        }
        if ($request->filled('jam_selesai')) {
            $request->merge(['jam_selesai' => substr((string) $request->jam_selesai, 0, 5)]);
        }

        $baseValidated = $request->validate([
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'harga' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'keterangan' => ['nullable', 'string', 'max:150'],
        ]);
        $baseValidated['is_active'] = $request->boolean('is_active', true);

        $lapangans = Lapangan::all();
        if ($lapangans->isEmpty()) {
            return redirect()
                ->route('admin.harga-khusus.index')
                ->withErrors(['lapangan_id' => 'Belum ada data lapangan yang tersedia.']);
        }

        // Cek konflik untuk semua lapangan terlebih dahulu
        $bentrokList = [];
        foreach ($lapangans as $lap) {
            $isBentrok = HargaKhusus::query()
                ->where('lapangan_id', $lap->id)
                ->whereDate('tanggal', $baseValidated['tanggal'])
                ->where('is_active', true)
                ->where('jam_mulai', '<', $baseValidated['jam_selesai'])
                ->where('jam_selesai', '>', $baseValidated['jam_mulai'])
                ->exists();

            if ($isBentrok) {
                $bentrokList[] = $lap->nama;
            }
        }

        if (!empty($bentrokList)) {
            throw ValidationException::withMessages([
                'lapangan_id' => 'Jadwal harga khusus bertumpang tindih pada lapangan: ' . implode(', ', $bentrokList) . '.',
            ]);
        }

        DB::transaction(function () use ($lapangans, $baseValidated) {
            foreach ($lapangans as $lap) {
                HargaKhusus::create(array_merge($baseValidated, ['lapangan_id' => $lap->id]));
            }
        });

        return redirect()
            ->route('admin.harga-khusus.index')
            ->with('success', 'Harga khusus berhasil ditambahkan untuk semua lapangan (' . $lapangans->count() . ' lapangan).');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $hargaKhusus = HargaKhusus::findOrFail($id);

        $validated = $this->validateHargaKhusus($request);

        $this->pastikanTidakBentrok($validated, $hargaKhusus->id);

        // Jika request tidak membawa field is_active, pertahankan nilai sebelumnya
        if (! $request->has('is_active')) {
            $validated['is_active'] = $hargaKhusus->is_active;
        }

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
        if ($request->filled('jam_mulai')) {
            $request->merge(['jam_mulai' => substr((string) $request->jam_mulai, 0, 5)]);
        }
        if ($request->filled('jam_selesai')) {
            $request->merge(['jam_selesai' => substr((string) $request->jam_selesai, 0, 5)]);
        }

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

        $validated['is_active'] = $request->boolean('is_active', true);

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