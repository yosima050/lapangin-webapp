@extends('layouts.admin')

@section('title', 'Manajemen Harga Master - Admin LapangIn')
@section('page-title', 'Manajemen Harga Master')

@section('content')
    <div class="space-y-6">

        {{-- Header & Action --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-900">Aturan Tarif Harga Master</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Atur tarif dasar operasional weekday, weekend, serta tarif khusus tanggal merah untuk setiap lapangan.
                </p>
            </div>

            <a href="{{ route('admin.harga.create') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-indigo-900 hover:bg-indigo-800 text-white font-bold text-sm shadow-sm transition">
                <span>➕</span> Tambah Aturan Tarif Baru
            </a>
        </div>

        {{-- Filter Box --}}
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <form method="GET" action="{{ route('admin.harga.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                <div>
                    <label for="lapangan_id" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">
                        Filter Lapangan
                    </label>
                    <select name="lapangan_id" id="lapangan_id" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-indigo-600 outline-none">
                        <option value="">-- Semua Lapangan --</option>
                        @foreach ($lapangans as $lap)
                            <option value="{{ $lap->id }}" {{ request('lapangan_id') == $lap->id ? 'selected' : '' }}>
                                {{ $lap->nama }} ({{ $lap->kategori }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="jenis_hari" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">
                        Filter Jenis Hari
                    </label>
                    <select name="jenis_hari" id="jenis_hari" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-indigo-600 outline-none">
                        <option value="">-- Semua Hari --</option>
                        <option value="weekday" {{ request('jenis_hari') == 'weekday' ? 'selected' : '' }}>Weekday (Senin-Jumat)</option>
                        <option value="weekend" {{ request('jenis_hari') == 'weekend' ? 'selected' : '' }}>Weekend (Sabtu-Minggu)</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-indigo-900 hover:bg-indigo-800 text-white font-bold text-sm rounded-xl transition">
                        Filter
                    </button>
                    <a href="{{ route('admin.harga.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm rounded-xl transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Table Container --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Lapangan</th>
                            <th class="px-6 py-4 font-semibold">Jenis Hari / Tanggal</th>
                            <th class="px-6 py-4 font-semibold">Jam Mulai - Selesai</th>
                            <th class="px-6 py-4 font-semibold">Tarif Sewa (Rp)</th>
                            <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($hargaMasters as $harga)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $harga->lapangan?->foto_url }}" alt="{{ $harga->lapangan?->nama }}" class="w-10 h-10 rounded-xl object-cover border border-gray-100 shrink-0">
                                        <div>
                                            <p class="font-bold text-gray-900">{{ $harga->lapangan?->nama ?: 'Lapangan Terhapus' }}</p>
                                            <p class="text-xs text-gray-400">{{ $harga->lapangan?->kategori }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($harga->tanggal_khusus)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700">
                                            📅 Tanggal Merah: {{ $harga->tanggal_khusus->format('d M Y') }}
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
                                <td class="px-6 py-4 font-extrabold text-indigo-950">
                                    Rp {{ number_format($harga->harga, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('admin.harga.edit', $harga->id) }}" class="p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Edit">
                                            ✏️
                                        </a>
                                        <form method="POST" action="{{ route('admin.harga.destroy', $harga->id) }}"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus aturan tarif ini?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                    Belum ada data tarif master yang cocok dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($hargaMasters->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $hargaMasters->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
