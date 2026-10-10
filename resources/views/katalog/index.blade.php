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
    <section class="px-4 sm:px-6 lg:px-8 pt-10 pb-8">
        <div class="max-w-7xl mx-auto">

            <div class="inline-flex items-center gap-2 px-3 py-1.5 mb-5 rounded-full bg-[#eaf7d8] text-[#416900] text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-[#416900]"></span>
                {{ count($katalog) }} Venue Siap Pakai
            </div>

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

                    <button
                        type="button"
                        @click="sport = 'Semua'"
                        :class="sport === 'Semua'
                            ? 'bg-[#1a146b] text-white'
                            : 'bg-white text-gray-700 border border-gray-200'"
                        class="px-4 py-2 rounded-full text-sm font-semibold transition"
                    >
                        Semua <span class="opacity-70">({{ count($katalog) }})</span>
                    </button>

                    <button
                        type="button"
                        @click="sport = 'Futsal'"
                        :class="sport === 'Futsal'
                            ? 'bg-[#1a146b] text-white'
                            : 'bg-white text-gray-700 border border-gray-200'"
                        class="px-4 py-2 rounded-full text-sm font-semibold transition"
                    >
                        Futsal
                    </button>

                    <button
                        type="button"
                        @click="sport = 'Badminton'"
                        :class="sport === 'Badminton'
                            ? 'bg-[#1a146b] text-white'
                            : 'bg-white text-gray-700 border border-gray-200'"
                        class="px-4 py-2 rounded-full text-sm font-semibold transition"
                    >
                        Badminton
                    </button>

                    <button
                        type="button"
                        @click="sport = 'Mini Soccer'"
                        :class="sport === 'Mini Soccer'
                            ? 'bg-[#1a146b] text-white'
                            : 'bg-white text-gray-700 border border-gray-200'"
                        class="px-4 py-2 rounded-full text-sm font-semibold transition"
                    >
                        Mini Soccer
                    </button>

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
                        {{-- Filter Alpine.js tetap berjalan karena membaca atribut data --}}
                        x-show="sport === 'Semua' || '{{ $item['kategori'] }}' === sport"
                        x-transition
                        {{-- LINK DINAMIS menggunakan UUID asli dari database --}}
                        onclick="window.location.href = '{{ route('katalog.show', $item['lapangan_id']) }}'"
                        class="cursor-pointer bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition overflow-hidden"
                    >
                        <div class="grid grid-cols-1 md:grid-cols-[220px_1fr]">

                            {{-- Image Placeholder --}}
                            <div class="relative h-56 md:h-full min-h-[240px] bg-gray-100 overflow-hidden">
                                <img
                                    src="https://ui-avatars.com/api/?name={{ urlencode($item['lapangan_nama']) }}&background=E0E7FF&color=3730A3&size=512"
                                    alt="{{ $item['lapangan_nama'] }}"
                                    class="w-full h-full object-cover"
                                >
                                <span class="absolute top-4 left-4 px-3 py-1.5 rounded-full bg-white/95 text-[#1a146b] text-xs font-bold shadow-sm">
                                    {{ $item['kategori'] }}
                                </span>
                            </div>

                            {{-- Content --}}
                            <div class="p-5 flex flex-col">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide text-[#416900]">
                                            {{ $item['kategori'] }}
                                        </p>
                                        <h3 class="mt-1 text-lg font-extrabold text-[#1a146b]">
                                            {{ $item['lapangan_nama'] }}
                                        </h3>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="font-bold text-sm text-gray-900">★ 5.0</div>
                                    </div>
                                </div>

                                <p class="mt-3 text-sm text-gray-500">
                                    Jam Buka: {{ $item['jam_operasional']['jam_buka'] }} - {{ $item['jam_operasional']['jam_tutup'] }}
                                </p>

                                <div class="mt-5 pt-4 border-t border-gray-100">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs text-gray-400">Jadwal hari ini</p>
                                            <p class="text-sm font-bold text-[#416900]">
                                                {{ collect($item['slots'])->where('is_tersedia', true)->count() }} slot kosong
                                            </p>
                                        </div>
                                    </div>

                                    <a
                                        href="{{ route('katalog.show', $item['lapangan_id']) }}"
                                        class="mt-4 flex items-center justify-center w-full px-4 py-3 rounded-xl bg-[#1a146b] text-white text-sm font-bold hover:bg-[#312e81] transition"
                                    >
                                        Lihat Detail & Jadwal
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full py-16 text-center">
                        <h3 class="text-lg font-bold text-gray-700">Venue tidak ditemukan</h3>
                        <p class="mt-2 text-sm text-gray-500">Belum ada data lapangan di database.</p>
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
