@extends('layouts.app')

@section('title', 'Katalog Lapangan')

@section('content')
<div
    x-data="{
        sport: '{{ request('kategori', 'Semua') }}',
        date: '{{ request('tanggal', now()->toDateString()) }}',
        dp: false,
        parking: false,
        indoor: false
    }"
    class="min-h-screen bg-[#f8f9ff] text-[#0b1c30]"
>
    {{-- Hero --}}
  <section class="px-4 sm:px-6 lg:px-8 pt-8 pb-8">
        <div class="max-w-7xl mx-auto">

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-[#1a146b] max-w-3xl">
                Temukan Arena
                <span class="text-[#416900]">
                    Olahraga Favoritmu
                </span>
            </h1>

            <p class="mt-4 max-w-2xl text-sm sm:text-base leading-7 text-gray-600">
                Booking instan terintegrasi, jadwal live terupdate otomatis 24 jam
                dengan konfirmasi langsung tanpa antre.
            </p>

        </div>
    </section>

    {{-- Filter --}}
    <section class="sticky top-20 z-30 bg-[#f8f9ff]/95 backdrop-blur-md border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">

            <div class="flex flex-col gap-4">

                {{-- Category --}}
                <div class="flex flex-wrap items-center gap-2">

                    <span class="text-sm font-bold text-gray-700 mr-1">
                        Kategori:
                    </span>

                    @php
                        $currentKategori = request('kategori', 'Semua');
                    @endphp

                    <button
                        type="button"
                        @click="sport = 'Semua'"
                        :class="sport === 'Semua'
                            ? 'bg-[#1a146b] text-white shadow-sm'
                            : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'"
                        class="px-4 py-2 rounded-full text-sm font-semibold transition inline-flex items-center cursor-pointer"
                    >
                        Semua
                    </button>

                    @foreach (($kategoriList ?? collect($katalog)->pluck('kategori')->filter()->unique()) as $kat)
                        @if(filled($kat))
                            <button
                                type="button"
                                @click="sport = '{{ $kat }}'"
                                :class="sport === '{{ $kat }}'
                                    ? 'bg-[#1a146b] text-white shadow-sm'
                                    : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'"
                                class="px-4 py-2 rounded-full text-sm font-semibold transition inline-flex items-center cursor-pointer"
                            >
                                {{ $kat }}
                            </button>
                        @endif
                    @endforeach

                </div>

                {{-- Date + Options --}}
                <div class="flex flex-col lg:flex-row lg:items-center gap-3">

                    {{-- Date --}}
                    <form method="GET" action="{{ route('katalog.index') }}" class="flex items-center gap-2">
                        @if(request('kategori'))
                            <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                        @endif
                        <label
                            for="tanggal"
                            class="text-sm font-bold text-gray-700"
                        >
                            Tanggal:
                        </label>

                        <input
                            id="tanggal"
                            name="tanggal"
                            type="date"
                            min="{{ now('Asia/Jakarta')->toDateString() }}"
                            value="{{ $tanggal }}"
                            onchange="this.form.submit()"
                            class="px-4 py-2 rounded-xl border border-gray-200 bg-white text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#acf847]"
                        >
                    </form>

                    {{-- Quick Filters --}}
                    <div class="flex flex-wrap gap-2">

                        <button
                            type="button"
                            @click="dp = !dp"
                            :class="dp
                                ? 'bg-[#eaf7d8] text-[#416900] border-[#acf847]'
                                : 'bg-white text-gray-700 border-gray-200'"
                            class="px-4 py-2 rounded-xl border text-sm font-semibold transition"
                        >
                            Bisa DP 50%
                        </button>

                        <button
                            type="button"
                            @click="parking = !parking"
                            :class="parking
                                ? 'bg-[#eaf7d8] text-[#416900] border-[#acf847]'
                                : 'bg-white text-gray-700 border-gray-200'"
                            class="px-4 py-2 rounded-xl border text-sm font-semibold transition"
                        >
                            Parkir Mobil
                        </button>

                        <button
                            type="button"
                            @click="indoor = !indoor"
                            :class="indoor
                                ? 'bg-[#eaf7d8] text-[#416900] border-[#acf847]'
                                : 'bg-white text-gray-700 border-gray-200'"
                            class="px-4 py-2 rounded-xl border text-sm font-semibold transition"
                        >
                            AC / Indoor
                        </button>

                    </div>

                </div>

            </div>
        </div>
    </section>

    {{-- Venue Grid --}}
    <section class="px-4 sm:px-6 lg:px-8 py-10">
        <div class="max-w-7xl mx-auto">

            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-[#1a146b]">
                        Venue Pilihan
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Temukan lapangan terbaik di sekitarmu.
                    </p>
                </div>

                <select
                    class="hidden sm:block px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-700 focus:outline-none"
                >
                    <option>Terdekat dari Lokasimu</option>
                    <option>Rating Tertinggi</option>
                    <option>Harga Terendah</option>
                    <option>Slot Malam Kosong Terbanyak</option>
                </select>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                @forelse ($katalog as $item)
                    <article
                        x-show="sport === 'Semua' || '{{ $item['kategori'] }}' === sport"
                        x-transition
                        onclick="window.location.href = '{{ route('katalog.show', $item['lapangan_id']) }}'"
                        class="cursor-pointer bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden group flex flex-col"
                    >
                        <div class="grid grid-cols-1 md:grid-cols-[240px_1fr] flex-1">

                            {{-- Image Lapangan Asli dari PostgreSQL / Upload --}}
                            <div class="relative h-60 md:h-full min-h-[220px] bg-gray-900 overflow-hidden shrink-0">
                                <img
                                    src="{{ $item['foto_url'] ?? ('https://ui-avatars.com/api/?name='.urlencode($item['lapangan_nama']).'&background=1a146b&color=acf847&size=512') }}"
                                    alt="{{ $item['lapangan_nama'] }}"
                                   class="w-full h-full object-cover object-center transition duration-300 group-hover:scale-105"
                                    onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($item['lapangan_nama']) }}&background=1a146b&color=acf847&size=512';"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent md:hidden"></div>

                                <span class="absolute top-4 left-4 px-3 py-1.5 rounded-full bg-[#1a146b]/90 backdrop-blur-md text-[#acf847] text-xs font-bold shadow-sm">
                                    {{ $item['kategori'] }}
                                </span>
                            </div>

                            {{-- Content Lapangan & Tarif --}}
                            <div class="p-6 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wider text-[#416900]">
                                                {{ $item['kategori'] }}
                                            </p>
                                            <h3 class="mt-1 text-lg font-extrabold text-[#1a146b] group-hover:text-indigo-800 transition">
                                                {{ $item['lapangan_nama'] }}
                                            </h3>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-xs font-bold">
                                                <svg class="w-3.5 h-3.5 fill-amber-400 text-amber-400" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                <span>5.0</span>
                                            </span>
                                        </div>
                                    </div>

                                    @if (!empty($item['deskripsi']))
                                        <p class="mt-2 text-xs text-gray-500 line-clamp-2 leading-relaxed">
                                            {{ $item['deskripsi'] }}
                                        </p>
                                    @endif

                                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
                                        <span class="material-symbols-outlined text-sm text-[#1a146b]">schedule</span>
                                        <span>Jam Buka: {{ $item['jam_operasional']['jam_buka'] }} – {{ $item['jam_operasional']['jam_tutup'] }} WIB</span>
                                    </div>
                                </div>

                                <div class="mt-5 pt-4 border-t border-gray-100">
                                    <div class="flex items-end justify-between gap-3">
                                        <div>
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Tarif Mulai</p>
                                            <div class="flex items-baseline gap-1">
                                                <span class="text-lg font-extrabold text-[#0b1c30]">
                                                    Rp {{ number_format($item['harga_termurah'] ?? ($item['harga_weekday'] ?? 100000), 0, ',', '.') }}
                                                </span>
                                                <span class="text-xs text-gray-500 font-medium">/jam</span>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#eaf7d8] text-[#416900]">
                                                <span class="w-2 h-2 rounded-full bg-[#416900]"></span>
                                                {{ collect($item['slots'])->where('is_tersedia', true)->count() }} slot kosong
                                            </span>
                                        </div>
                                    </div>

                                    <a
                                        href="{{ route('katalog.show', $item['lapangan_id']) }}"
                                        class="mt-4 flex items-center justify-center w-full px-4 py-3 rounded-xl bg-[#1a146b] hover:bg-[#312e81] text-white text-xs font-bold shadow-sm transition"
                                    >
                                        Lihat Detail &amp; Jadwal Lengkap →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full py-16 text-center bg-white rounded-2xl border border-gray-100 p-8">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-indigo-50 text-[#1a146b] flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">Venue tidak ditemukan</h3>
                        <p class="mt-1 text-sm text-gray-500">Belum ada data lapangan yang terdaftar untuk kategori ini.</p>
                    </div>
                @endforelse

            </div>

        </div>
    </section>

    {{-- Benefits --}}
    <section class="px-4 sm:px-6 lg:px-8 pb-12">
        <div class="max-w-7xl mx-auto">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h3 class="font-extrabold text-[#1a146b]">
                        Garansi Slot Resmi
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-gray-500">
                        Jadwal yang tampil diperbarui secara berkala agar
                        informasi slot tetap akurat.
                    </p>
                </div>

                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h3 class="font-extrabold text-[#1a146b]">
                        DP 50% Ringan
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-gray-500">
                        Pilihan pembayaran fleksibel untuk memudahkan proses
                        booking lapangan.
                    </p>
                </div>

                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h3 class="font-extrabold text-[#1a146b]">
                        Bebas Reschedule
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-gray-500">
                        Pengguna dapat melakukan perubahan jadwal sesuai
                        kebijakan venue.
                    </p>
                </div>

            </div>

        </div>
    </section>
</div>
@endsection
