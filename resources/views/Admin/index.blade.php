@extends('layouts.admin')

@section('title', 'Dashboard Admin - LapangIn')
@section('page-title', 'Dashboard Utama')

@section('content')
    <div class="space-y-8">

        {{-- Welcome Banner --}}
        <div class="bg-gradient-to-r from-indigo-900 to-indigo-700 rounded-3xl p-8 text-white shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-xs font-semibold uppercase tracking-wider">
                    Panel Manajemen
                </span>
                <h2 class="text-2xl md:text-3xl font-extrabold mt-3">
                    Selamat Datang, {{ Auth::user()->nama ?? 'Admin' }}!
                </h2>
                <p class="text-indigo-200 mt-1 max-w-xl text-sm">
                    Kelola data lapangan, atur tarif dasar weekday/weekend, dan tentukan harga khusus tanggal merah dengan aman ke database PostgreSQL.
                </p>
            </div>

            <div class="flex flex-wrap gap-3 shrink-0">
                <a href="{{ route('admin.lapangan.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-lime-400 hover:bg-lime-500 text-indigo-950 font-bold text-sm shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Tambah Lapangan Baru</span>
                </a>
                <a href="{{ route('admin.harga.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm backdrop-blur transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Tambah Aturan Tarif</span>
                </a>
            </div>
        </div>

        {{-- Statistics Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm hover:shadow transition">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Lapangan</p>
                    <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                    </span>
                </div>
                <p class="text-3xl font-extrabold text-indigo-950 mt-3">{{ $totalLapangan }}</p>
                <a href="{{ route('admin.lapangan.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 mt-2 inline-block">
                    Kelola Lapangan →
                </a>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm hover:shadow transition">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Aturan Tarif Harga</p>
                    <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="text-3xl font-extrabold text-indigo-950 mt-3">{{ $totalHargaMaster }}</p>
                <a href="{{ route('admin.harga.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800 mt-2 inline-block">
                    Kelola Tarif Master →
                </a>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm hover:shadow transition">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Jadwal Hari Ini</p>
                    <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.253M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/></svg>
                    </span>
                </div>
                <p class="text-3xl font-extrabold text-indigo-950 mt-3">{{ $totalBookingHariIni }}</p>
                <p class="text-xs text-gray-400 mt-2">Live jadwal terdaftar</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm hover:shadow transition">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Pelanggan Terdaftar</p>
                    <span class="w-10 h-10 rounded-xl bg-sky-50 text-sky-700 flex items-center justify-center">
                        <svg class="w-5 h-5 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    </span>
                </div>
                <p class="text-3xl font-extrabold text-indigo-950 mt-3">{{ $totalPelanggan }}</p>
                <p class="text-xs text-gray-400 mt-2">Akun pelanggan aktif</p>
            </div>

        </div>

        {{-- Lapangan Terbaru Section --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Daftar Lapangan Terbaru</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Ringkasan data lapangan dan tarif dasar operasional</p>
                </div>
                <a href="{{ route('admin.lapangan.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                    Lihat Semua ({{ $totalLapangan }})
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-3.5 font-semibold">Lapangan</th>
                            <th class="px-6 py-3.5 font-semibold">Kategori</th>
                            <th class="px-6 py-3.5 font-semibold">Jam Operasional</th>
                            <th class="px-6 py-3.5 font-semibold">Tarif Weekday</th>
                            <th class="px-6 py-3.5 font-semibold">Tarif Weekend</th>
                            <th class="px-6 py-3.5 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($lapangansTerbaru as $lapangan)
                            @php
                                $weekdayPrice = $lapangan->hargaMasters->whereNull('tanggal_khusus')->where('jenis_hari', 'weekday')->first()?->harga;
                                $weekendPrice = $lapangan->hargaMasters->whereNull('tanggal_khusus')->where('jenis_hari', 'weekend')->first()?->harga;
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $lapangan->foto_url }}" alt="{{ $lapangan->nama }}" class="w-12 h-12 rounded-xl object-cover border border-gray-100 shadow-sm">
                                        <div>
                                            <p class="font-bold text-gray-900">{{ $lapangan->nama }}</p>
                                            <p class="text-xs text-gray-400 truncate max-w-xs">{{ $lapangan->deskripsi ?: 'Tidak ada deskripsi' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">
                                        {{ $lapangan->kategori }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-gray-700">
                                    {{ substr($lapangan->jam_buka, 0, 5) }} - {{ substr($lapangan->jam_tutup, 0, 5) }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-900">
                                    {{ $weekdayPrice ? 'Rp ' . number_format($weekdayPrice, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-emerald-700">
                                    {{ $weekendPrice ? 'Rp ' . number_format($weekendPrice, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('admin.lapangan.edit', $lapangan->id) }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                                        Edit
                                    </a>
                                    <a href="{{ route('admin.lapangan.show', $lapangan->id) }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                    Belum ada data lapangan. Silakan klik tombol "Tambah Lapangan Baru".
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection