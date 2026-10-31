@extends('layouts.app')

@section('title', 'LapangIn - Booking Lapangan Olahraga Cepat, Mudah, dan Terjadwal')

@section('content')
<div class="bg-[#f8f9ff] text-[#0b1c30]">

    {{-- 1. Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-[#1a146b]/5 via-transparent to-transparent pt-8 pb-16 lg:pt-12 lg:pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                
                {{-- Hero Copy --}}
                <div class="lg:col-span-7 flex flex-col items-center lg:items-start text-center lg:text-left space-y-6">
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-headline font-black text-[#1a146b] tracking-tight leading-[1.15]">
                        Booking Lapangan Olahraga <span class="text-[#416900]">Cepat, Mudah,</span> dan Terjadwal
                    </h1>

                    <p class="text-base sm:text-lg text-gray-600 max-w-2xl leading-relaxed">
                        Cek jadwal real-time, pilih lapangan standar kompetisi, dan amankan slot bermain Anda cukup dengan DP 50%. Bebas antre, langsung main!
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2 w-full sm:w-auto">
                        <a
                            href="{{ route('katalog.index') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-xl bg-[#1a146b] hover:bg-[#312e81] text-white font-headline font-bold text-sm shadow-lg shadow-[#1a146b]/20 hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5 cursor-pointer"
                        >
                            <span class="material-symbols-outlined text-base">search</span>
                            <span>Cari Lapangan Sekarang</span>
                        </a>

                        <a
                            href="#cara-kerja"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl border border-gray-300 hover:border-[#1a146b] text-gray-700 hover:text-[#1a146b] font-headline font-semibold text-sm bg-white/80 hover:bg-white transition-all duration-200"
                        >
                            <span>Pelajari Alur Booking</span>
                            <span class="material-symbols-outlined text-base">arrow_downward</span>
                        </a>
                    </div>

                    {{-- Quick Social Proof Metrics --}}
                    <div class="grid grid-cols-3 gap-6 sm:gap-10 pt-6 border-t border-gray-200/80 w-full max-w-lg">
                        <div>
                            <p class="text-2xl sm:text-3xl font-headline font-black text-[#1a146b]">100%</p>
                            <p class="text-xs text-gray-500 font-medium mt-0.5">Jadwal Real-Time</p>
                        </div>
                        <div>
                            <p class="text-2xl sm:text-3xl font-headline font-black text-[#1a146b]">50%</p>
                            <p class="text-xs text-gray-500 font-medium mt-0.5">Bisa DP Dahulu</p>
                        </div>
                        <div>
                            <p class="text-2xl sm:text-3xl font-headline font-black text-[#1a146b]">QR Code</p>
                            <p class="text-xs text-gray-500 font-medium mt-0.5">Check-in Kilat</p>
                        </div>
                    </div>
                </div>

                                {{-- Hero Visual / Dynamic Banner Carousel (Ala Gelora / Ganesha Tanah Mas) --}}
                @php
                    $carouselSlides = [
                        [
                            'nama' => 'Lapangan 1 - Vinyl Pro (Futsal Indoor)',
                            'kategori' => 'Futsal',
                            'foto' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1200&q=80',
                            'url' => route('katalog.index'),
                        ],
                        [
                            'nama' => 'Lapangan 2 - Interlock Premium (Futsal & Basket)',
                            'kategori' => 'Futsal & Basket',
                            'foto' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=1200&q=80',
                            'url' => route('katalog.index'),
                        ],
                        [
                            'nama' => 'Badminton Court 1 & 2 (Karpet Yonex)',
                            'kategori' => 'Badminton',
                            'foto' => 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=1200&q=80',
                            'url' => route('katalog.index'),
                        ],
                    ];

                    if (isset($lapangans) && $lapangans->isNotEmpty()) {
                        $dbSlides = [];
                        foreach ($lapangans as $l) {
                            $dbSlides[] = [
                                'nama' => $l->nama,
                                'kategori' => $l->kategori ?? 'Sports Arena',
                                'foto' => $l->foto_url,
                                'url' => route('katalog.show', $l->id),
                            ];
                        }
                        if (!empty($dbSlides)) {
                            $carouselSlides = $dbSlides;
                        }
                    }
                @endphp

                <div class="lg:col-span-5 relative w-full"
                     x-data="{
                        currentSlide: 0,
                        totalSlides: {{ count($carouselSlides) }},
                        timer: null,
                        init() {
                            this.timer = setInterval(() => {
                                this.next();
                            }, 5000);
                        },
                        next() {
                            this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
                        },
                        prev() {
                            this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
                        }
                     }"
                     @mouseenter="clearInterval(timer)"
                     @mouseleave="timer = setInterval(() => next(), 5000)">

                    <div class="relative mx-auto max-w-md lg:max-w-none">
                        {{-- Background Accent Glow --}}
                        <div class="absolute -top-6 -right-6 w-60 h-60 bg-[#acf847]/30 rounded-full blur-3xl pointer-events-none"></div>
                        <div class="absolute -bottom-6 -left-6 w-60 h-60 bg-[#1a146b]/20 rounded-full blur-3xl pointer-events-none"></div>

                        {{-- Main Billboard Container --}}
                        <div class="relative rounded-3xl overflow-hidden border border-white/80 shadow-2xl bg-gray-950 group select-none">
                            <div class="relative w-full overflow-hidden" style="height: 380px; min-height: 380px;">

                                @foreach ($carouselSlides as $idx => $slide)
                                    <div x-show="currentSlide === {{ $idx }}"
                                         x-transition:enter="transition ease-out duration-500"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-300"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="absolute inset-0 w-full h-full"
                                         style="{{ $idx > 0 ? 'display: none;' : '' }}">

                                        {{-- Image Background --}}
                                        <img src="{{ $slide['foto'] }}"
                                             alt="{{ $slide['nama'] }}"
                                             class="w-full h-full object-cover object-center filter brightness-[0.78] contrast-[1.05]"
                                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1200&q=80';">

                                        {{-- Dark Vignette Gradient --}}
                                        <div class="absolute inset-0 bg-gradient-to-t from-[#0e0a40]/95 via-[#1a146b]/40 to-black/30"></div>

                                        {{-- Header Brand Badge Overlay (Kiri Atas) --}}
                                        <div class="absolute top-4 left-4 z-10 flex items-center gap-2">
                                            <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#1a146b]/80 backdrop-blur-md border border-white/20 text-white text-[11px] font-black tracking-wider uppercase shadow-sm">
                                                <span class="w-2 h-2 rounded-full bg-[#acf847] animate-pulse"></span>
                                                <span class="text-white">{{ $slide['kategori'] }}</span>
                                            </div>
                                        </div>

                                        {{-- Center Billboard Content (Posisi Tengah Elegan) --}}
                                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-6 z-10">
                                            <h3 class="text-2xl sm:text-3xl lg:text-4xl font-headline font-black text-white tracking-tight drop-shadow-[0_4px_14px_rgba(10,5,50,0.95)] leading-tight max-w-sm">
                                                {{ $slide['nama'] }}
                                            </h3>

                                            <a href="{{ $slide['url'] }}"
                                               class="mt-6 inline-flex items-center gap-2.5 px-6 py-3 rounded-full bg-[#acf847] hover:bg-[#9de837] text-[#102000] font-headline font-black text-xs shadow-lg shadow-[#acf847]/30 hover:shadow-[#acf847]/50 border border-[#bbf968] transition-all duration-200 transform hover:scale-105 active:scale-95 cursor-pointer">
                                                <span>Cek jadwal dan booking disini!</span>
                                                <span class="material-symbols-outlined text-sm font-black">arrow_forward</span>
                                            </a>
                                        </div>

                                        {{-- Watermark Pojok Kanan Bawah --}}
                                        <div class="absolute bottom-4 right-4 z-10 pointer-events-none opacity-80 hidden sm:block text-right">
                                            <span class="text-[10px] font-black tracking-widest text-white/90 uppercase block drop-shadow-md">
                                                MAIN MAKIN
                                            </span>
                                            <span class="text-xs font-black tracking-wider text-[#acf847] uppercase block drop-shadow-md -mt-1">
                                                MUDAH!
                                            </span>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Slider Navigation Arrow Buttons --}}
                                <button type="button"
                                        @click="prev()"
                                        class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-black/50 hover:bg-black/80 backdrop-blur-md border border-white/25 text-white flex items-center justify-center transition shadow-md cursor-pointer"
                                        title="Slide Sebelumnya">
                                    <span class="material-symbols-outlined text-base">chevron_left</span>
                                </button>

                                <button type="button"
                                        @click="next()"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-black/50 hover:bg-black/80 backdrop-blur-md border border-white/25 text-white flex items-center justify-center transition shadow-md cursor-pointer"
                                        title="Slide Selanjutnya">
                                    <span class="material-symbols-outlined text-base">chevron_right</span>
                                </button>
                            </div>

                            {{-- Slider Pagination Dots (Bawah) --}}
                            <div class="py-2.5 bg-black/80 backdrop-blur-md border-t border-white/10 flex items-center justify-center gap-2">
                                @foreach ($carouselSlides as $idx => $slide)
                                    <button type="button"
                                            @click="currentSlide = {{ $idx }}"
                                            class="h-2 rounded-full transition-all duration-300 cursor-pointer"
                                            :class="currentSlide === {{ $idx }} ? 'w-6 bg-[#acf847]' : 'w-2 bg-white/40 hover:bg-white/70'">
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- 2. USP / Fitur Unggulan (4 Card) --}}
    <section class="py-16 sm:py-20 bg-white border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <span class="inline-block text-xs sm:text-sm font-bold text-[#416900] uppercase tracking-wider mb-2">Kenapa LapangIn?</span>
                <h2 class="text-2xl sm:text-4xl font-headline font-black text-[#1a146b] tracking-tight">
                    Pengalaman Reservasi Olahraga Modern
                </h2>
                <p class="mt-3 text-sm sm:text-base text-gray-600">
                    Semua kenyamanan mulai dari pemilihan jadwal hingga bermain di lapangan kami rancang tanpa ribet.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- USP Card 1 --}}
                <div class="p-6 rounded-2xl bg-[#f8f9ff] border border-gray-100 hover:border-[#1a146b]/20 hover:shadow-lg transition-all duration-200">
                    <div class="w-12 h-12 rounded-xl bg-[#1a146b] text-white flex items-center justify-center mb-5 shadow-sm">
                        <span class="material-symbols-outlined text-[24px]">sports_soccer</span>
                    </div>
                    <h3 class="text-lg font-headline font-bold text-[#1a146b] mb-2">Standar Lantai AFC</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Material vinyl & interlock standar federasi internasional, ramah lutut, antiselip, dan nyaman untuk laga intens.
                    </p>
                </div>

                {{-- USP Card 2 --}}
                <div class="p-6 rounded-2xl bg-[#f8f9ff] border border-gray-100 hover:border-[#1a146b]/20 hover:shadow-lg transition-all duration-200">
                    <div class="w-12 h-12 rounded-xl bg-[#416900] text-white flex items-center justify-center mb-5 shadow-sm">
                        <span class="material-symbols-outlined text-[24px]">payments</span>
                    </div>
                    <h3 class="text-lg font-headline font-bold text-[#1a146b] mb-2">Sistem Pembayaran DP 50%</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Cukup bayar uang muka 50% secara instan lewat payment gateway resmi untuk mengamankan slot pertandingan tim Anda.
                    </p>
                </div>

                {{-- USP Card 3 --}}
                <div class="p-6 rounded-2xl bg-[#f8f9ff] border border-gray-100 hover:border-[#1a146b]/20 hover:shadow-lg transition-all duration-200">
                    <div class="w-12 h-12 rounded-xl bg-[#1a146b] text-white flex items-center justify-center mb-5 shadow-sm">
                        <span class="material-symbols-outlined text-[24px]">qr_code_scanner</span>
                    </div>
                    <h3 class="text-lg font-headline font-bold text-[#1a146b] mb-2">Check-in Cepat QR Code</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Tiba di lokasi tinggal tunjukkan tiket digital. Kasir scan QR dalam hitungan detik tanpa perlu bukti transfer manual.
                    </p>
                </div>

                {{-- USP Card 4 --}}
                <div class="p-6 rounded-2xl bg-[#f8f9ff] border border-gray-100 hover:border-[#1a146b]/20 hover:shadow-lg transition-all duration-200">
                    <div class="w-12 h-12 rounded-xl bg-[#416900] text-white flex items-center justify-center mb-5 shadow-sm">
                        <span class="material-symbols-outlined text-[24px]">schedule</span>
                    </div>
                    <h3 class="text-lg font-headline font-bold text-[#1a146b] mb-2">Ketersediaan Real-Time</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Jadwal terhubung langsung dengan sistem kasir lapangan. Tidak ada lagi risiko bentrok jadwal atau double-booking.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. Kategori Olahraga --}}
    <section class="py-16 sm:py-20 bg-[#f8f9ff]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
                <div>
                    <span class="inline-block text-xs sm:text-sm font-bold text-[#416900] uppercase tracking-wider mb-2">Kategori Lapangan</span>
                    <h2 class="text-2xl sm:text-4xl font-headline font-black text-[#1a146b] tracking-tight">
                        Pilih Olahraga Favoritmu
                    </h2>
                </div>
                <div class="mt-4 md:mt-0">
                    <a
                        href="{{ route('katalog.index') }}"
                        class="inline-flex items-center gap-1.5 text-sm font-headline font-bold text-[#1a146b] hover:text-[#416900] transition-colors"
                    >
                        <span>Lihat Semua Lapangan</span>
                        <span class="material-symbols-outlined text-base">arrow_forward</span>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Kategori: Futsal --}}
                <div class="group relative rounded-2xl overflow-hidden bg-white border border-gray-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div class="relative h-48 overflow-hidden bg-gray-900">
                        <img
                            src="https://images.unsplash.com/photo-1529900748604-07564a03e7a6?auto=format&fit=crop&w=700&q=80"
                            alt="Futsal"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-md bg-[#1a146b] text-white text-xs font-semibold">
                            Indoor & Vinyl
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-headline font-bold text-[#1a146b]">Futsal</h3>
                            <p class="mt-1 text-xs text-gray-500">Lantai interlock dan vinyl berstandar kompetisi.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-gray-500">Mulai sesi</span>
                            <a
                                href="{{ route('katalog.index', ['kategori' => 'Futsal']) }}"
                                class="inline-flex items-center gap-1 text-xs font-headline font-bold text-[#1a146b] hover:text-[#416900]"
                            >
                                Explore <span class="material-symbols-outlined text-xs">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Kategori: Badminton --}}
                <div class="group relative rounded-2xl overflow-hidden bg-white border border-gray-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div class="relative h-48 overflow-hidden bg-gray-900">
                        <img
                            src="https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=700&q=80"
                            alt="Badminton"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-md bg-[#416900] text-white text-xs font-semibold">
                            Karpet BWF
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-headline font-bold text-[#1a146b]">Badminton</h3>
                            <p class="mt-1 text-xs text-gray-500">Pencahayaan anti-silau dengan karpet standar PBSI/BWF.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-gray-500">Mulai sesi</span>
                            <a
                                href="{{ route('katalog.index', ['kategori' => 'Badminton']) }}"
                                class="inline-flex items-center gap-1 text-xs font-headline font-bold text-[#1a146b] hover:text-[#416900]"
                            >
                                Explore <span class="material-symbols-outlined text-xs">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Kategori: Basket --}}
                <div class="group relative rounded-2xl overflow-hidden bg-white border border-gray-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div class="relative h-48 overflow-hidden bg-gray-900">
                        <img
                            src="https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=700&q=80"
                            alt="Basket"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-md bg-[#1a146b] text-white text-xs font-semibold">
                            FIBA Ring & Board
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-headline font-bold text-[#1a146b]">Basket</h3>
                            <p class="mt-1 text-xs text-gray-500">Full court & 3x3 dengan ring hidrolik kaca tempered.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-gray-500">Mulai sesi</span>
                            <a
                                href="{{ route('katalog.index', ['kategori' => 'Basket']) }}"
                                class="inline-flex items-center gap-1 text-xs font-headline font-bold text-[#1a146b] hover:text-[#416900]"
                            >
                                Explore <span class="material-symbols-outlined text-xs">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Kategori: Mini Soccer --}}
                <div class="group relative rounded-2xl overflow-hidden bg-white border border-gray-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div class="relative h-48 overflow-hidden bg-gray-900">
                        <img
                            src="https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=700&q=80"
                            alt="Mini Soccer"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-md bg-[#416900] text-white text-xs font-semibold">
                            Rumput Sintetis Pro
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-headline font-bold text-[#1a146b]">Mini Soccer</h3>
                            <p class="mt-1 text-xs text-gray-500">Rumput sintetis monofilament dan drainase prima.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-gray-500">Mulai sesi</span>
                            <a
                                href="{{ route('katalog.index', ['kategori' => 'Mini Soccer']) }}"
                                class="inline-flex items-center gap-1 text-xs font-headline font-bold text-[#1a146b] hover:text-[#416900]"
                            >
                                Explore <span class="material-symbols-outlined text-xs">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 4. Cara Kerja (3 Langkah) --}}
    <section id="cara-kerja" class="py-16 sm:py-20 bg-white border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <span class="inline-block text-xs sm:text-sm font-bold text-[#416900] uppercase tracking-wider mb-2">Alur Reservasi</span>
                <h2 class="text-2xl sm:text-4xl font-headline font-black text-[#1a146b] tracking-tight">
                    3 Langkah Mudah Booking di LapangIn
                </h2>
                <p class="mt-3 text-sm sm:text-base text-gray-600">
                    Proses reservasi praktis tanpa konfirmasi via chat manual yang memakan waktu.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                {{-- Langkah 1 --}}
                <div class="relative bg-[#f8f9ff] p-8 rounded-2xl border border-gray-100 text-center">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-[#1a146b] text-white flex items-center justify-center font-headline font-black text-xl shadow-md mb-6">
                        1
                    </div>
                    <h3 class="text-lg font-headline font-bold text-[#1a146b] mb-2">Pilih Jadwal & Lapangan</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Pilih jenis olahraga, cek ketersediaan jam secara live, dan tentukan durasi bermain yang Anda inginkan.
                    </p>
                </div>

                {{-- Langkah 2 --}}
                <div class="relative bg-[#f8f9ff] p-8 rounded-2xl border border-gray-100 text-center">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-[#416900] text-white flex items-center justify-center font-headline font-black text-xl shadow-md mb-6">
                        2
                    </div>
                    <h3 class="text-lg font-headline font-bold text-[#1a146b] mb-2">Bayar DP Online Aman</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Cukup selesaikan down payment (DP 50%) melalui QRIS, Virtual Account, atau E-Wallet favorit Anda.
                    </p>
                </div>

                {{-- Langkah 3 --}}
                <div class="relative bg-[#f8f9ff] p-8 rounded-2xl border border-gray-100 text-center">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-[#1a146b] text-white flex items-center justify-center font-headline font-black text-xl shadow-md mb-6">
                        3
                    </div>
                    <h3 class="text-lg font-headline font-bold text-[#1a146b] mb-2">Scan QR di Lokasi</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Tiba di venue, tunjukkan tiket digital QR Code ke meja kasir untuk check-in instan dan langsung bermain!
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. Bottom CTA Banner --}}
    <section class="py-16 sm:py-20 bg-[#f8f9ff]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl bg-[#1a146b] px-6 py-12 sm:px-12 sm:py-16 shadow-2xl text-white">
                {{-- Decorative Glow elements --}}
                <div class="absolute -top-24 -right-24 w-96 h-96 bg-[#acf847]/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 max-w-3xl mx-auto text-center space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm text-[#acf847] text-xs font-semibold">
                        <span class="material-symbols-outlined text-sm">app_registration</span>
                        Gabung Komunitas LapangIn
                    </div>

                    <h2 class="text-2xl sm:text-4xl lg:text-5xl font-headline font-black tracking-tight leading-tight">
                        Daftar Akun Sekarang, Riwayat Booking Tersimpan Rapi
                    </h2>

                    <p class="text-sm sm:text-base text-gray-200 max-w-2xl mx-auto leading-relaxed">
                        Nikmati kemudahan pelacakan tiket digital, klaim invoice transaksi, dan kelola reschedule jadwal pertandingan tanpa khawatir data hilang.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                        @auth
                            <a
                                href="{{ route('katalog.index') }}"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-[#acf847] hover:bg-[#9de437] text-[#1a146b] font-headline font-bold text-sm shadow-md transition-all duration-200 transform hover:-translate-y-0.5 cursor-pointer"
                            >
                                <span class="material-symbols-outlined text-base">sports</span>
                                <span>Cari Lapangan Sekarang</span>
                            </a>
                        @else
                            <a
                                href="{{ route('register') }}"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-[#acf847] hover:bg-[#9de437] text-[#1a146b] font-headline font-bold text-sm shadow-md transition-all duration-200 transform hover:-translate-y-0.5 cursor-pointer"
                            >
                                <span class="material-symbols-outlined text-base">person_add</span>
                                <span>Daftar Akun Gratis</span>
                            </a>

                            <a
                                href="{{ route('katalog.index') }}"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl border border-white/30 hover:border-white text-white font-headline font-semibold text-sm bg-white/10 hover:bg-white/20 transition-all duration-200 cursor-pointer"
                            >
                                <span>Jelajahi Dulu</span>
                                <span class="material-symbols-outlined text-base">arrow_forward</span>
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
