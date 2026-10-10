@extends('layouts.admin')

@section('title', 'Detail Lapangan - ' . $lapangan->nama)
@section('page-title', 'Detail Lapangan & Tarif')

@section('content')
    <div class="max-w-5xl mx-auto space-y-8">

        {{-- Top Navigation --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.lapangan.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-indigo-900 transition">
                ← Kembali ke Daftar Lapangan
            </a>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.lapangan.edit', $lapangan->id) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 font-bold text-xs rounded-xl hover:bg-indigo-100 transition">
                    ✏️ Edit Lapangan
                </a>
                <a href="{{ route('admin.harga.create', ['lapangan_id' => $lapangan->id]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-900 text-white font-bold text-xs rounded-xl hover:bg-indigo-800 transition">
                    ➕ Tambah Aturan Tarif
                </a>
            </div>
        </div>

        {{-- Detail Card --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-3">

                {{-- Foto Lapangan --}}
                <div class="bg-gray-100 h-64 md:h-auto min-h-[250px]">
                    <img src="{{ $lapangan->foto_url }}" alt="{{ $lapangan->nama }}" class="w-full h-full object-cover">
                </div>

                {{-- Metadata --}}
                <div class="p-8 md:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-indigo-100 text-indigo-800">
                            {{ $lapangan->kategori }}
                        </span>
                        <span class="text-xs font-mono text-gray-400">
                            ID: {{ $lapangan->id }}
                        </span>
                    </div>

                    <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900">
                        {{ $lapangan->nama }}
                    </h2>

                    <p class="text-sm text-gray-600 leading-relaxed">
                        {{ $lapangan->deskripsi ?: 'Belum ada deskripsi yang ditambahkan untuk venue ini.' }}
                    </p>

                    <div class="pt-4 border-t border-gray-100 grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-2xl">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Jam Buka</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ substr($lapangan->jam_buka, 0, 5) }} WIB</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-2xl">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Jam Tutup</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ substr($lapangan->jam_tutup, 0, 5) }} WIB</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Tabel Seluruh Aturan Harga (Harga Master) --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-indigo-950">Daftar Tarif Master Lapangan Ini</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Tabel referensi harga weekday, weekend, dan tanggal merah di PostgreSQL</p>
                </div>
                <a href="{{ route('admin.harga.create', ['lapangan_id' => $lapangan->id]) }}"
                   class="text-xs font-bold text-indigo-700 hover:text-indigo-900 bg-indigo-50 px-3 py-2 rounded-xl transition">
                    + Tambah Tarif
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Jenis Hari / Tanggal</th>
                            <th class="px-6 py-4 font-semibold">Rentang Jam</th>
                            <th class="px-6 py-4 font-semibold">Tarif Sewa</th>
                            <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($lapangan->hargaMasters as $harga)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    @if ($harga->tanggal_khusus)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700">
                                            📅 Tanggal Merah ({{ $harga->tanggal_khusus->format('d M Y') }})
                                        </span>
                                    @elseif ($harga->jenis_hari === 'weekend')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                                            🎉 Weekend (Sabtu-Minggu)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">
                                            💼 Weekday (Senin-Jumat)
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-gray-700">
                                    {{ substr($harga->jam_mulai, 0, 5) }} - {{ substr($harga->jam_selesai, 0, 5) }}
                                </td>
                                <td class="px-6 py-4 font-bold text-indigo-950">
                                    Rp {{ number_format($harga->harga, 0, ',', '.') }} <span class="text-xs font-normal text-gray-400">/ jam</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('admin.harga.edit', $harga->id) }}" class="p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Edit Tarif">
                                            ✏️
                                        </a>
                                        <form method="POST" action="{{ route('admin.harga.destroy', $harga->id) }}"
                                              onsubmit="return confirm('Hapus aturan tarif harga ini?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Hapus Tarif">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                                    Belum ada aturan tarif master untuk lapangan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
