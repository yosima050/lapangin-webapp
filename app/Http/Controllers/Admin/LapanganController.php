<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLapanganRequest;
use App\Http\Requests\UpdateLapanganRequest;
use App\Models\HargaMaster;
use App\Models\Lapangan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

            $jamBuka = strlen($lapangan->jam_buka) === 5 ? $lapangan->jam_buka . ':00' : $lapangan->jam_buka;
            $jamTutup = strlen($lapangan->jam_tutup) === 5 ? $lapangan->jam_tutup . ':00' : $lapangan->jam_tutup;

            $hargaWeekday = $request->validated('harga_weekday');
            $hargaWeekdayMalam = $request->validated('harga_weekday_malam');
            $hargaWeekend = $request->validated('harga_weekend');
            $hargaWeekendMalam = $request->validated('harga_weekend_malam');

            // Simpan tarif dasar Weekday (Pagi/Siang vs Malam)
            if ($hargaWeekdayMalam !== null && $hargaWeekdayMalam !== '') {
                HargaMaster::create([
                    'lapangan_id' => $lapangan->id,
                    'jenis_hari' => 'weekday',
                    'tanggal_khusus' => null,
                    'jam_mulai' => $jamBuka ?: '07:00:00',
                    'jam_selesai' => '18:00:00',
                    'harga' => $hargaWeekday,
                ]);

                HargaMaster::create([
                    'lapangan_id' => $lapangan->id,
                    'jenis_hari' => 'weekday',
                    'tanggal_khusus' => null,
                    'jam_mulai' => '18:00:00',
                    'jam_selesai' => $jamTutup ?: '24:00:00',
                    'harga' => $hargaWeekdayMalam,
                ]);
            } else {
                HargaMaster::create([
                    'lapangan_id' => $lapangan->id,
                    'jenis_hari' => 'weekday',
                    'tanggal_khusus' => null,
                    'jam_mulai' => $jamBuka,
                    'jam_selesai' => $jamTutup,
                    'harga' => $hargaWeekday,
                ]);
            }

            // Simpan tarif dasar Weekend (Pagi/Siang vs Malam)
            if ($hargaWeekendMalam !== null && $hargaWeekendMalam !== '') {
                HargaMaster::create([
                    'lapangan_id' => $lapangan->id,
                    'jenis_hari' => 'weekend',
                    'tanggal_khusus' => null,
                    'jam_mulai' => $jamBuka ?: '07:00:00',
                    'jam_selesai' => '18:00:00',
                    'harga' => $hargaWeekend,
                ]);

                HargaMaster::create([
                    'lapangan_id' => $lapangan->id,
                    'jenis_hari' => 'weekend',
                    'tanggal_khusus' => null,
                    'jam_mulai' => '18:00:00',
                    'jam_selesai' => $jamTutup ?: '24:00:00',
                    'harga' => $hargaWeekendMalam,
                ]);
            } else {
                HargaMaster::create([
                    'lapangan_id' => $lapangan->id,
                    'jenis_hari' => 'weekend',
                    'tanggal_khusus' => null,
                    'jam_mulai' => $jamBuka,
                    'jam_selesai' => $jamTutup,
                    'harga' => $hargaWeekend,
                ]);
            }

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

        $hargaWeekdayPagi = $lapangan->hargaMasters
            ->whereNull('tanggal_khusus')
            ->where('jenis_hari', 'weekday')
            ->where('jam_mulai', '<', '18:00:00')
            ->first()?->harga
            ?? $lapangan->hargaMasters
                ->whereNull('tanggal_khusus')
                ->where('jenis_hari', 'weekday')
                ->first()?->harga;

        $hargaWeekdayMalam = $lapangan->hargaMasters
            ->whereNull('tanggal_khusus')
            ->where('jenis_hari', 'weekday')
            ->where('jam_mulai', '>=', '18:00:00')
            ->first()?->harga;

        $hargaWeekendPagi = $lapangan->hargaMasters
            ->whereNull('tanggal_khusus')
            ->where('jenis_hari', 'weekend')
            ->where('jam_mulai', '<', '18:00:00')
            ->first()?->harga
            ?? $lapangan->hargaMasters
                ->whereNull('tanggal_khusus')
                ->where('jenis_hari', 'weekend')
                ->first()?->harga;

        $hargaWeekendMalam = $lapangan->hargaMasters
            ->whereNull('tanggal_khusus')
            ->where('jenis_hari', 'weekend')
            ->where('jam_mulai', '>=', '18:00:00')
            ->first()?->harga;

        $hargaWeekday = $hargaWeekdayPagi;
        $hargaWeekend = $hargaWeekendPagi;

        $hargaKhusus = $lapangan->hargaMasters
            ->whereNotNull('tanggal_khusus')
            ->first();

        return view('Admin.lapangan.edit', compact(
            'lapangan',
            'hargaWeekday',
            'hargaWeekend',
            'hargaWeekdayPagi',
            'hargaWeekdayMalam',
            'hargaWeekendPagi',
            'hargaWeekendMalam',
            'hargaKhusus'
        ));
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

            $jamBuka = strlen($lapangan->jam_buka) === 5 ? $lapangan->jam_buka . ':00' : $lapangan->jam_buka;
            $jamTutup = strlen($lapangan->jam_tutup) === 5 ? $lapangan->jam_tutup . ':00' : $lapangan->jam_tutup;

            $hargaWeekday = $request->validated('harga_weekday');
            $hargaWeekdayMalam = $request->validated('harga_weekday_malam');
            $hargaWeekend = $request->validated('harga_weekend');
            $hargaWeekendMalam = $request->validated('harga_weekend_malam');

            // Update tarif weekday jika diberikan
            if ($request->filled('harga_weekday')) {
                if ($request->filled('harga_weekday_malam')) {
                    HargaMaster::updateOrCreate(
                        [
                            'lapangan_id' => $lapangan->id,
                            'jenis_hari' => 'weekday',
                            'tanggal_khusus' => null,
                            'jam_mulai' => $jamBuka ?: '07:00:00',
                            'jam_selesai' => '18:00:00',
                        ],
                        ['harga' => $hargaWeekday]
                    );

                    HargaMaster::updateOrCreate(
                        [
                            'lapangan_id' => $lapangan->id,
                            'jenis_hari' => 'weekday',
                            'tanggal_khusus' => null,
                            'jam_mulai' => '18:00:00',
                            'jam_selesai' => $jamTutup ?: '24:00:00',
                        ],
                        ['harga' => $hargaWeekdayMalam]
                    );
                } else {
                    $existingWeekday = HargaMaster::where('lapangan_id', $lapangan->id)
                        ->where('jenis_hari', 'weekday')
                        ->whereNull('tanggal_khusus')
                        ->first();

                    if ($existingWeekday) {
                        $existingWeekday->update(['harga' => $hargaWeekday]);
                    } else {
                        HargaMaster::create([
                            'lapangan_id' => $lapangan->id,
                            'jenis_hari' => 'weekday',
                            'tanggal_khusus' => null,
                            'jam_mulai' => $jamBuka,
                            'jam_selesai' => $jamTutup,
                            'harga' => $hargaWeekday,
                        ]);
                    }
                }
            }

            // Update tarif weekend jika diberikan
            if ($request->filled('harga_weekend')) {
                if ($request->filled('harga_weekend_malam')) {
                    HargaMaster::updateOrCreate(
                        [
                            'lapangan_id' => $lapangan->id,
                            'jenis_hari' => 'weekend',
                            'tanggal_khusus' => null,
                            'jam_mulai' => $jamBuka ?: '07:00:00',
                            'jam_selesai' => '18:00:00',
                        ],
                        ['harga' => $hargaWeekend]
                    );

                    HargaMaster::updateOrCreate(
                        [
                            'lapangan_id' => $lapangan->id,
                            'jenis_hari' => 'weekend',
                            'tanggal_khusus' => null,
                            'jam_mulai' => '18:00:00',
                            'jam_selesai' => $jamTutup ?: '24:00:00',
                        ],
                        ['harga' => $hargaWeekendMalam]
                    );
                } else {
                    $existingWeekend = HargaMaster::where('lapangan_id', $lapangan->id)
                        ->where('jenis_hari', 'weekend')
                        ->whereNull('tanggal_khusus')
                        ->first();

                    if ($existingWeekend) {
                        $existingWeekend->update(['harga' => $hargaWeekend]);
                    } else {
                        HargaMaster::create([
                            'lapangan_id' => $lapangan->id,
                            'jenis_hari' => 'weekend',
                            'tanggal_khusus' => null,
                            'jam_mulai' => $jamBuka,
                            'jam_selesai' => $jamTutup,
                            'harga' => $hargaWeekend,
                        ]);
                    }
                }
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

    public function updateTarif(Request $request, Lapangan $lapangan): RedirectResponse
    {
        $validated = $request->validate([
            'harga_weekday' => ['required', 'numeric', 'min:0'],
            'harga_weekday_malam' => ['nullable', 'numeric', 'min:0'],
            'harga_weekend' => ['required', 'numeric', 'min:0'],
            'harga_weekend_malam' => ['nullable', 'numeric', 'min:0'],
        ], [
            'harga_weekday.required' => 'Tarif weekday pagi wajib diisi.',
            'harga_weekday.min' => 'Tarif weekday pagi tidak boleh minus.',
            'harga_weekday_malam.min' => 'Tarif weekday malam tidak boleh minus.',
            'harga_weekend.required' => 'Tarif weekend pagi wajib diisi.',
            'harga_weekend.min' => 'Tarif weekend pagi tidak boleh minus.',
            'harga_weekend_malam.min' => 'Tarif weekend malam tidak boleh minus.',
        ]);

        $jamBuka = strlen($lapangan->jam_buka) === 5 ? $lapangan->jam_buka . ':00' : $lapangan->jam_buka;
        $jamTutup = strlen($lapangan->jam_tutup) === 5 ? $lapangan->jam_tutup . ':00' : $lapangan->jam_tutup;

        $hargaWeekday = $validated['harga_weekday'];
        $hargaWeekdayMalam = $validated['harga_weekday_malam'] ?? null;
        $hargaWeekend = $validated['harga_weekend'];
        $hargaWeekendMalam = $validated['harga_weekend_malam'] ?? null;

        // Weekday
        if ($hargaWeekdayMalam !== null && $hargaWeekdayMalam !== '' && (float) $hargaWeekdayMalam !== (float) $hargaWeekday) {
            HargaMaster::updateOrCreate(
                [
                    'lapangan_id' => $lapangan->id,
                    'jenis_hari' => 'weekday',
                    'tanggal_khusus' => null,
                    'jam_mulai' => $jamBuka ?: '07:00:00',
                    'jam_selesai' => '18:00:00',
                ],
                ['harga' => $hargaWeekday]
            );

            HargaMaster::updateOrCreate(
                [
                    'lapangan_id' => $lapangan->id,
                    'jenis_hari' => 'weekday',
                    'tanggal_khusus' => null,
                    'jam_mulai' => '18:00:00',
                    'jam_selesai' => $jamTutup ?: '24:00:00',
                ],
                ['harga' => $hargaWeekdayMalam]
            );
        } else {
            $existingWeekday = HargaMaster::where('lapangan_id', $lapangan->id)
                ->where('jenis_hari', 'weekday')
                ->whereNull('tanggal_khusus')
                ->first();

            if ($existingWeekday) {
                $existingWeekday->update([
                    'jam_mulai' => $jamBuka ?: '07:00:00',
                    'jam_selesai' => $jamTutup ?: '24:00:00',
                    'harga' => $hargaWeekday,
                ]);

                HargaMaster::where('lapangan_id', $lapangan->id)
                    ->where('jenis_hari', 'weekday')
                    ->whereNull('tanggal_khusus')
                    ->where('id', '!=', $existingWeekday->id)
                    ->delete();
            } else {
                HargaMaster::create([
                    'lapangan_id' => $lapangan->id,
                    'jenis_hari' => 'weekday',
                    'tanggal_khusus' => null,
                    'jam_mulai' => $jamBuka ?: '07:00:00',
                    'jam_selesai' => $jamTutup ?: '24:00:00',
                    'harga' => $hargaWeekday,
                ]);
            }
        }

        // Weekend
        if ($hargaWeekendMalam !== null && $hargaWeekendMalam !== '' && (float) $hargaWeekendMalam !== (float) $hargaWeekend) {
            HargaMaster::updateOrCreate(
                [
                    'lapangan_id' => $lapangan->id,
                    'jenis_hari' => 'weekend',
                    'tanggal_khusus' => null,
                    'jam_mulai' => $jamBuka ?: '07:00:00',
                    'jam_selesai' => '18:00:00',
                ],
                ['harga' => $hargaWeekend]
            );

            HargaMaster::updateOrCreate(
                [
                    'lapangan_id' => $lapangan->id,
                    'jenis_hari' => 'weekend',
                    'tanggal_khusus' => null,
                    'jam_mulai' => '18:00:00',
                    'jam_selesai' => $jamTutup ?: '24:00:00',
                ],
                ['harga' => $hargaWeekendMalam]
            );
        } else {
            $existingWeekend = HargaMaster::where('lapangan_id', $lapangan->id)
                ->where('jenis_hari', 'weekend')
                ->whereNull('tanggal_khusus')
                ->first();

            if ($existingWeekend) {
                $existingWeekend->update([
                    'jam_mulai' => $jamBuka ?: '07:00:00',
                    'jam_selesai' => $jamTutup ?: '24:00:00',
                    'harga' => $hargaWeekend,
                ]);

                HargaMaster::where('lapangan_id', $lapangan->id)
                    ->where('jenis_hari', 'weekend')
                    ->whereNull('tanggal_khusus')
                    ->where('id', '!=', $existingWeekend->id)
                    ->delete();
            } else {
                HargaMaster::create([
                    'lapangan_id' => $lapangan->id,
                    'jenis_hari' => 'weekend',
                    'tanggal_khusus' => null,
                    'jam_mulai' => $jamBuka ?: '07:00:00',
                    'jam_selesai' => $jamTutup ?: '24:00:00',
                    'harga' => $hargaWeekend,
                ]);
            }
        }

        return redirect()->route('admin.lapangan.index')
            ->with('success', "Tarif sewa master untuk {$lapangan->nama} berhasil disimpan!");
    }

    public function toggleStatus(Lapangan $lapangan): RedirectResponse
    {
        $lapangan->is_active = ! ($lapangan->is_active ?? true);
        $lapangan->save();

        $status = $lapangan->is_active ? 'diaktifkan' : 'dinonaktifkan sementara';

        return redirect()->route('admin.lapangan.index')
            ->with('success', "Status lapangan {$lapangan->nama} berhasil {$status}.");
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
