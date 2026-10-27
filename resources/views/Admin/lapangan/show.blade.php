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
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                    </svg>
                    Edit Lapangan
                </a>
                <a href="{{ route('admin.harga.create', ['lapangan_id' => $lapangan->id]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-900 text-white font-bold text-xs rounded-xl hover:bg-indigo-800 transition">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Aturan Tarif
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
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                            </svg>
                                            Tanggal Merah ({{ $harga->tanggal_khusus->format('d M Y') }})
                                        </span>
                                    @elseif ($harga->jenis_hari === 'weekend')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                                            </svg>
                                            Weekend (Sabtu-Minggu)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                            Weekday (Senin-Jumat)
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
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.harga.destroy', $harga->id) }}"
                                              onsubmit="return confirm('Hapus aturan tarif harga ini?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Hapus Tarif">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
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
