<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLapanganRequest;
use App\Http\Requests\UpdateLapanganRequest;
use App\Models\HargaMaster;
use App\Models\Lapangan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LapanganController extends Controller
{
    public function index(): View
    {
        $lapangans = Lapangan::with(['hargaMasters'])->orderBy('nama')->get();
        $averageRate = round((float)(HargaMaster::whereNull('tanggal_khusus')->avg('harga') ?? 150000));
        $minRate = round((float)(HargaMaster::whereNull('tanggal_khusus')->min('harga') ?? 60000));
        $maxRate = round((float)(HargaMaster::whereNull('tanggal_khusus')->max('harga') ?? 200000));

        return view('Admin.lapangan.index', compact('lapangans', 'averageRate', 'minRate', 'maxRate'));
    }

    public function create(): View
    {
        return view('Admin.lapangan.create');
    }

    public function store(StoreLapanganRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $data = $request->safe()->only(['nama', 'kategori', 'jam_buka', 'jam_tutup', 'deskripsi']);

            if ($request->hasFile('foto')) {
                $data['foto'] = $request->file('foto')->store('lapangan', 'public');
            }

            $lapangan = Lapangan::create($data);

            // Simpan tarif dasar Weekday
            HargaMaster::create([
                'lapangan_id' => $lapangan->id,
                'jenis_hari' => 'weekday',
                'tanggal_khusus' => null,
                'jam_mulai' => $lapangan->jam_buka,
                'jam_selesai' => $lapangan->jam_tutup,
                'harga' => $request->validated('harga_weekday'),
            ]);

            // Simpan tarif dasar Weekend
            HargaMaster::create([
                'lapangan_id' => $lapangan->id,
                'jenis_hari' => 'weekend',
                'tanggal_khusus' => null,
                'jam_mulai' => $lapangan->jam_buka,
                'jam_selesai' => $lapangan->jam_tutup,
                'harga' => $request->validated('harga_weekend'),
            ]);

            // Simpan tarif khusus tanggal merah (jika diisi)
            if ($request->filled('harga_tanggal_merah') && $request->filled('tanggal_khusus')) {
                $date = Carbon::parse($request->validated('tanggal_khusus'));
                $jenisHari = $date->isWeekend() ? 'weekend' : 'weekday';

                HargaMaster::create([
                    'lapangan_id' => $lapangan->id,
                    'jenis_hari' => $jenisHari,
                    'tanggal_khusus' => $request->validated('tanggal_khusus'),
                    'jam_mulai' => $lapangan->jam_buka,
                    'jam_selesai' => $lapangan->jam_tutup,
                    'harga' => $request->validated('harga_tanggal_merah'),
                ]);
            }
        });

        return redirect()->route('admin.lapangan.index')
            ->with('success', 'Data lapangan baru beserta tarif dasar berhasil disimpan dengan aman ke PostgreSQL!');
    }

    public function show(Lapangan $lapangan): View
    {
        $lapangan->load('hargaMasters');

        return view('Admin.lapangan.show', compact('lapangan'));
    }

    public function edit(Lapangan $lapangan): View
    {
        $lapangan->load('hargaMasters');

        $hargaWeekday = $lapangan->hargaMasters
            ->whereNull('tanggal_khusus')
            ->where('jenis_hari', 'weekday')
            ->first()?->harga;

        $hargaWeekend = $lapangan->hargaMasters
            ->whereNull('tanggal_khusus')
            ->where('jenis_hari', 'weekend')
            ->first()?->harga;

        $hargaKhusus = $lapangan->hargaMasters
            ->whereNotNull('tanggal_khusus')
            ->first();

        return view('Admin.lapangan.edit', compact('lapangan', 'hargaWeekday', 'hargaWeekend', 'hargaKhusus'));
    }

    public function update(UpdateLapanganRequest $request, Lapangan $lapangan): RedirectResponse
    {
        DB::transaction(function () use ($request, $lapangan) {
            $data = $request->safe()->only(['nama', 'kategori', 'jam_buka', 'jam_tutup', 'deskripsi']);

            if ($request->hasFile('foto')) {
                if ($lapangan->foto && !str_starts_with($lapangan->foto, 'http') && Storage::disk('public')->exists($lapangan->foto)) {
                    Storage::disk('public')->delete($lapangan->foto);
                }
                $data['foto'] = $request->file('foto')->store('lapangan', 'public');
            }

            $lapangan->update($data);

            // Update tarif weekday jika diberikan
            if ($request->filled('harga_weekday')) {
                HargaMaster::updateOrCreate(
                    [
                        'lapangan_id' => $lapangan->id,
                        'jenis_hari' => 'weekday',
                        'tanggal_khusus' => null,
                    ],
                    [
                        'jam_mulai' => $lapangan->jam_buka,
                        'jam_selesai' => $lapangan->jam_tutup,
                        'harga' => $request->validated('harga_weekday'),
                    ]
                );
            }

            // Update tarif weekend jika diberikan
            if ($request->filled('harga_weekend')) {
                HargaMaster::updateOrCreate(
                    [
                        'lapangan_id' => $lapangan->id,
                        'jenis_hari' => 'weekend',
                        'tanggal_khusus' => null,
                    ],
                    [
                        'jam_mulai' => $lapangan->jam_buka,
                        'jam_selesai' => $lapangan->jam_tutup,
                        'harga' => $request->validated('harga_weekend'),
                    ]
                );
            }

            // Update tarif tanggal merah jika diisi
            if ($request->filled('harga_tanggal_merah') && $request->filled('tanggal_khusus')) {
                $date = Carbon::parse($request->validated('tanggal_khusus'));
                $jenisHari = $date->isWeekend() ? 'weekend' : 'weekday';

                HargaMaster::updateOrCreate(
                    [
                        'lapangan_id' => $lapangan->id,
                        'tanggal_khusus' => $request->validated('tanggal_khusus'),
                    ],
                    [
                        'jenis_hari' => $jenisHari,
                        'jam_mulai' => $lapangan->jam_buka,
                        'jam_selesai' => $lapangan->jam_tutup,
                        'harga' => $request->validated('harga_tanggal_merah'),
                    ]
                );
            }
        });

        return redirect()->route('admin.lapangan.index')
            ->with('success', 'Data lapangan dan tarif dasar berhasil diperbarui!');
    }

    public function destroy(Lapangan $lapangan): RedirectResponse
    {
        if ($lapangan->foto && !str_starts_with($lapangan->foto, 'http') && Storage::disk('public')->exists($lapangan->foto)) {
            Storage::disk('public')->delete($lapangan->foto);
        }

        $lapangan->delete();

        return redirect()->route('admin.lapangan.index')
            ->with('success', 'Lapangan beserta seluruh tarif harga berhasil dihapus!');
    }
}
