@extends('layouts.app')

@section('title', 'LapangIn - Booking Lapangan Olahraga Cepat, Mudah, dan Terjadwal')

@php
    $carouselSlides = [
        [
            'nama' => 'Lapangan 1 - Vinyl Pro (Futsal Indoor)',
            'kategori' => 'Futsal',
            'foto' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=800&h=480&fm=webp&q=75',
            'url' => route('katalog.index'),
        ],
        [
            'nama' => 'Lapangan 2 - Interlock Premium (Futsal & Basket)',
            'kategori' => 'Futsal & Basket',
            'foto' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=800&h=480&fm=webp&q=75',
            'url' => route('katalog.index'),
        ],
        [
            'nama' => 'Badminton Court 1 & 2 (Karpet Yonex)',
            'kategori' => 'Badminton',
            'foto' => 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=800&h=480&fm=webp&q=75',
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

    $heroLcpImage = $carouselSlides[0]['foto'] ?? null;
    $heroCategories = isset($lapangans) && $lapangans->isNotEmpty()
        ? $lapangans->pluck('kategori')->filter()->unique()->sort()->values()
        : collect(['Futsal', 'Badminton', 'Basket', 'Mini Soccer']);
    $todayDate = \Carbon\Carbon::now('Asia/Jakarta')->toDateString();
    $totalLapanganAktif = isset($lapangans) ? $lapangans->count() : count($carouselSlides);
@endphp

@push('head')
    @if($heroLcpImage)
        <link rel="preload" as="image" href="{{ $heroLcpImage }}" fetchpriority="high" />
    @endif
@endpush

@section('content')
<div class="bg-[#f8f9ff] text-[#0b1c30]">

    {{-- 1. Photo-First Editorial Sports Arena Hero + Quick-Book Dock --}}
    <section class="relative pt-4 pb-10 sm:pt-6 sm:pb-16 lg:pt-8 lg:pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Full-Bleed Arena Photo Showcase --}}
            <div class="relative rounded-3xl overflow-hidden bg-[#0e0a3c] shadow-xl border border-[#1a146b]/15 select-none"
                 x-data="{
                    currentSlide: 0,
                    totalSlides: {{ count($carouselSlides) }},
                    timer: null,
                    autoStarted: false,
                    startAuto() {
                        if (this.autoStarted || this.totalSlides <= 1) return;
                        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                        this.autoStarted = true;
                        this.timer = setInterval(() => this.next(), 6000);
                    },
                    init() {
                        if (this.totalSlides > 1) {
                            const trigger = () => this.startAuto();
                            ['pointermove', 'touchstart', 'scroll', 'keydown'].forEach((evt) => {
                                window.addEventListener(evt, trigger, { once: true, passive: true });
                            });
                        }
                    },
                    next() {
                        this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
                    },
                    prev() {
                        this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
                    }
                 }"
                 @mouseenter="if (timer) { clearInterval(timer); timer = null; }"
                 @mouseleave="if (autoStarted && totalSlides > 1 && !timer) timer = setInterval(() => next(), 6000)">

                <div class="relative w-full h-[350px] sm:h-[450px] lg:h-[520px] xl:h-[550px] overflow-hidden">
                    @foreach ($carouselSlides as $idx => $slide)
                        <div x-show="currentSlide === {{ $idx }}"
                             x-transition:enter="transition ease-out duration-500"
                             x-transition:enter-start="opacity-0 scale-[1.02]"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-300"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0"
                             class="absolute inset-0 w-full h-full"
                             style="{{ $idx > 0 ? 'display: none;' : '' }}">

                            <img src="{{ $slide['foto'] }}"
                                 alt="{{ $slide['nama'] }}"
                                 width="1200"
                                 height="600"
                                 fetchpriority="{{ $idx === 0 ? 'high' : 'low' }}"
                                 @if($idx === 0)
                                    decoding="sync"
                                 @else
                                    loading="lazy"
                                    decoding="async"
                                 @endif
                                 class="w-full h-full object-cover object-center"
                                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1200&h=600&fm=webp&q=75';">

                            {{-- Directional Editorial Scrim (Jaga keterbacaan teks tanpa menutupi atmosfer lapangan) --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-[#0b082e]/95 via-[#0b082e]/45 to-black/20"></div>
                            <div class="hidden sm:block absolute inset-0 bg-gradient-to-r from-[#0b082e]/85 via-[#0b082e]/30 to-transparent"></div>

                            {{-- Top Bar Overlay: Kategori Lapangan Aktif & Link Langsung --}}
                            <div class="absolute top-4 left-4 right-4 sm:top-6 sm:left-8 sm:right-8 z-10 flex items-center justify-between gap-2">
                                <a href="{{ $slide['url'] }}"
                                   class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-black/45 hover:bg-black/65 backdrop-blur-md border border-white/15 text-white text-[11px] font-bold transition max-w-[75%]">
                                    <span class="w-2 h-2 rounded-full bg-[#acf847] shrink-0"></span>
                                    <span class="truncate">{{ $slide['nama'] }}</span>
                                    <x-lucide-arrow-up-right class="w-3.5 h-3.5 text-[#acf847] shrink-0" />
                                </a>

                                <div class="hidden sm:flex items-center gap-1.5">
                                    <button type="button"
                                            @click="prev()"
                                            aria-label="Slide Sebelumnya"
                                            class="w-8 h-8 rounded-full bg-black/40 hover:bg-black/65 backdrop-blur-md border border-white/20 text-white flex items-center justify-center transition cursor-pointer">
                                        <x-lucide-chevron-left class="w-4 h-4" />
                                    </button>
                                    <button type="button"
                                            @click="next()"
                                            aria-label="Slide Selanjutnya"
                                            class="w-8 h-8 rounded-full bg-black/40 hover:bg-black/65 backdrop-blur-md border border-white/20 text-white flex items-center justify-center transition cursor-pointer">
                                        <x-lucide-chevron-right class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Editorial Left-Aligned Copy Overlay (di atas foto arena) --}}
                    <div class="absolute inset-x-0 bottom-0 z-10 p-5 pb-12 sm:p-10 sm:pb-20 lg:p-12 lg:pb-20 flex flex-col justify-end pointer-events-none">
                        <div class="max-w-2xl space-y-2.5 sm:space-y-3.5">
                            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-headline font-black text-white tracking-tight leading-[1.08]">
                                Pesan Lapangan.<br>
                                <span class="text-[#acf847]">Langsung Main.</span>
                            </h1>
                            <p class="text-xs sm:text-base lg:text-lg text-indigo-100/90 leading-relaxed max-w-lg">
                                Cek slot kosong real-time, kunci jadwal mulai DP 50%, dan check-in di gerbang cukup pakai QR Code.
                            </p>
                        </div>

                        {{-- Slide Dots (Pojok Kanan Bawah di Desktop, Bawah Kiri di HP) --}}
                        <div class="mt-3.5 sm:mt-0 sm:absolute sm:bottom-16 sm:right-10 lg:right-12 flex items-center gap-0.5 pointer-events-auto">
                            @foreach ($carouselSlides as $idx => $slide)
                                <button type="button"
                                        @click="currentSlide = {{ $idx }}"
                                        aria-label="Pindah ke Slide {{ $idx + 1 }}"
                                        class="min-w-[24px] min-h-[24px] inline-flex items-center justify-center px-1 cursor-pointer">
                                    <span class="block h-1.5 rounded-full transition-all duration-300"
                                          :class="currentSlide === {{ $idx }} ? 'w-6 bg-[#acf847]' : 'w-1.5 bg-white/45 hover:bg-white/75'"></span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Floating Interactive Quick-Book Search Dock (Custom Dropdown & Calendar, Tanpa Popup Bawaan Browser) --}}
            <div class="relative z-20 -mt-8 sm:-mt-11 mx-2 sm:mx-6 lg:mx-8"
                 x-data="{
                    katOpen: false,
                    dateOpen: false,
                    selectedKat: '',
                    todayStr: '{{ $todayDate }}',
                    selectedDate: '{{ $todayDate }}',
                    viewYear: {{ \Carbon\Carbon::parse($todayDate)->year }},
                    viewMonth: {{ \Carbon\Carbon::parse($todayDate)->month - 1 }},
                    monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                    shortMonths: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    dayHeaders: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    formatISO(y, m, d) {
                        return y + '-' + String(m + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
                    },
                    addDays(iso, days) {
                        const [y, m, d] = iso.split('-').map(Number);
                        const dt = new Date(y, m - 1, d + days);
                        return this.formatISO(dt.getFullYear(), dt.getMonth(), dt.getDate());
                    },
                    get dateLabel() {
                        const [y, m, d] = this.selectedDate.split('-').map(Number);
                        const dt = new Date(y, m - 1, d);
                        if (this.selectedDate === this.todayStr || this.selectedDate === this.addDays(this.todayStr, 1)) {
                            return d + ' ' + this.shortMonths[m - 1] + ' ' + y;
                        }
                        return this.dayHeaders[dt.getDay()] + ', ' + d + ' ' + this.shortMonths[m - 1] + ' ' + y;
                    },
                    get dateBadge() {
                        if (this.selectedDate === this.todayStr) return 'Hari Ini';
                        if (this.selectedDate === this.addDays(this.todayStr, 1)) return 'Besok';
                        return '';
                    },
                    get canPrevMonth() {
                        const [ty, tm] = this.todayStr.split('-').map(Number);
                        return this.viewYear > ty || (this.viewYear === ty && this.viewMonth > (tm - 1));
                    },
                    prevMonth() {
                        if (!this.canPrevMonth) return;
                        if (this.viewMonth === 0) { this.viewMonth = 11; this.viewYear--; }
                        else { this.viewMonth--; }
                    },
                    nextMonth() {
                        if (this.viewMonth === 11) { this.viewMonth = 0; this.viewYear++; }
                        else { this.viewMonth++; }
                    },
                    get calendarCells() {
                        const firstDay = new Date(this.viewYear, this.viewMonth, 1).getDay();
                        const daysInMonth = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
                        const cells = [];
                        for (let i = 0; i < firstDay; i++) cells.push(null);
                        for (let d = 1; d <= daysInMonth; d++) {
                            const iso = this.formatISO(this.viewYear, this.viewMonth, d);
                            cells.push({
                                day: d,
                                iso,
                                disabled: iso < this.todayStr,
                                isToday: iso === this.todayStr,
                                isSelected: iso === this.selectedDate,
                            });
                        }
                        return cells;
                    },
                    pickDate(iso) {
                        if (iso < this.todayStr) return;
                        this.selectedDate = iso;
                        const [y, m] = iso.split('-').map(Number);
                        this.viewYear = y;
                        this.viewMonth = m - 1;
                        this.dateOpen = false;
                    }
                 }"
                 @keydown.escape.window="katOpen = false; dateOpen = false">
                <form method="GET"
                      action="{{ route('jadwal.index') }}"
                      class="rounded-2xl sm:rounded-3xl bg-white border border-gray-200/90 shadow-xl shadow-[#0b1c30]/12 p-4 sm:p-5 lg:p-6 grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 items-end">

                    {{-- Pilih Cabang Olahraga (Custom Dropdown) --}}
                    <div class="sm:col-span-5 relative" @click.outside="katOpen = false">
                        <span id="hero_kat_label" class="block text-[11px] font-extrabold uppercase tracking-wider text-gray-600 mb-1.5 px-1">
                            Cabang Olahraga
                        </span>
                        <input type="hidden" name="kategori" :value="selectedKat">
                        <button type="button"
                                aria-labelledby="hero_kat_label"
                                @click="katOpen = !katOpen; dateOpen = false"
                                :aria-expanded="katOpen"
                                class="w-full flex items-center justify-between gap-2.5 rounded-xl border py-3 sm:py-3.5 pl-3.5 pr-3.5 text-left text-sm sm:text-[15px] font-bold text-[#0b1c30] transition cursor-pointer"
                                :class="katOpen ? 'border-[#1a146b] bg-white ring-1 ring-[#1a146b]' : 'border-gray-200 bg-[#f8faff] hover:border-gray-300'">
                            <span class="flex items-center gap-3 min-w-0">
                                <span class="w-5 h-5 sm:w-6 sm:h-6 shrink-0 text-[#1a146b] inline-flex items-center justify-center">
                                    <x-icons.sport class="w-full h-full" />
                                </span>
                                <span class="truncate" x-text="selectedKat || 'Semua Olahraga'"></span>
                            </span>
                            <span class="shrink-0 transition-transform duration-200"
                                  :class="katOpen ? 'rotate-180 text-[#1a146b]' : 'text-gray-500'">
                                <x-lucide-chevron-down class="w-4 h-4 sm:w-5 sm:h-5" />
                            </span>
                        </button>

                        {{-- Panel Pilihan Cabang Olahraga --}}
                        <div x-show="katOpen"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1 scale-[0.98]"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 -translate-y-1 scale-[0.98]"
                             class="absolute left-0 right-0 mt-1.5 z-40 rounded-2xl bg-white border border-gray-200/90 shadow-xl shadow-[#0b1c30]/12 p-1.5 space-y-0.5 max-h-60 overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                            <button type="button"
                                    @click="selectedKat = ''; katOpen = false"
                                    class="w-full flex items-center justify-between gap-2 rounded-xl px-3 py-2.5 text-left text-xs sm:text-sm transition cursor-pointer"
                                    :class="selectedKat === '' ? 'bg-[#1a146b] text-white font-bold shadow-xs' : 'text-[#0b1c30] font-semibold hover:bg-[#f4f7ff] hover:text-[#1a146b]'">
                                <span>Semua Olahraga</span>
                                <span x-show="selectedKat === ''" class="shrink-0">
                                    <x-lucide-check class="w-4 h-4 text-[#acf847]" />
                                </span>
                            </button>
                            @foreach ($heroCategories as $kat)
                                <button type="button"
                                        @click="selectedKat = @js($kat); katOpen = false"
                                        class="w-full flex items-center justify-between gap-2 rounded-xl px-3 py-2.5 text-left text-xs sm:text-sm transition cursor-pointer"
                                        :class="selectedKat === @js($kat) ? 'bg-[#1a146b] text-white font-bold shadow-xs' : 'text-[#0b1c30] font-semibold hover:bg-[#f4f7ff] hover:text-[#1a146b]'">
                                    <span class="truncate">{{ $kat }}</span>
                                    <span x-show="selectedKat === @js($kat)" class="shrink-0">
                                        <x-lucide-check class="w-4 h-4 text-[#acf847]" />
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Pilih Tanggal Main (Custom Calendar Popover) --}}
                    <div class="sm:col-span-4 relative" @click.outside="dateOpen = false">
                        <span id="hero_date_label" class="block text-[11px] font-extrabold uppercase tracking-wider text-gray-600 mb-1.5 px-1">
                            Tanggal Main
                        </span>
                        <input type="hidden" name="tanggal" :value="selectedDate">
                        <button type="button"
                                aria-labelledby="hero_date_label"
                                @click="dateOpen = !dateOpen; katOpen = false"
                                :aria-expanded="dateOpen"
                                class="w-full flex items-center justify-between gap-2.5 rounded-xl border py-3 sm:py-3.5 pl-3.5 pr-3.5 text-left text-sm sm:text-[15px] font-bold text-[#0b1c30] transition cursor-pointer"
                                :class="dateOpen ? 'border-[#1a146b] bg-white ring-1 ring-[#1a146b]' : 'border-gray-200 bg-[#f8faff] hover:border-gray-300'">
                            <span class="flex items-center gap-3 min-w-0">
                                <span class="w-5 h-5 sm:w-6 sm:h-6 shrink-0 text-[#1a146b] inline-flex items-center justify-center">
                                    <x-icons.calendar class="w-full h-full" />
                                </span>
                                <span class="flex items-center gap-2 min-w-0">
                                    <span class="truncate" x-text="dateLabel"></span>
                                    <span x-show="dateBadge"
                                          x-text="dateBadge"
                                          class="shrink-0 px-2 py-0.5 rounded-md bg-[#eaf7d8] text-[#416900] text-[10px] sm:text-[11px] font-extrabold leading-none"></span>
                                </span>
                            </span>
                            <span class="shrink-0 transition-transform duration-200"
                                  :class="dateOpen ? 'rotate-180 text-[#1a146b]' : 'text-gray-500'">
                                <x-lucide-chevron-down class="w-4 h-4 sm:w-5 sm:h-5" />
                            </span>
                        </button>

                        {{-- Panel Kalender Custom --}}
                        <div x-show="dateOpen"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1 scale-[0.98]"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 -translate-y-1 scale-[0.98]"
                             class="absolute left-0 right-0 sm:right-auto sm:w-[292px] mt-1.5 z-40 rounded-2xl bg-white border border-gray-200/90 shadow-xl shadow-[#0b1c30]/12 p-3">
                            {{-- Pintasan Cepat --}}
                            <div class="grid grid-cols-2 gap-1.5 mb-3">
                                <button type="button"
                                        @click="pickDate(todayStr)"
                                        class="rounded-lg py-1.5 text-[11px] font-bold transition cursor-pointer"
                                        :class="selectedDate === todayStr ? 'bg-[#1a146b] text-white' : 'bg-[#f4f7ff] text-[#1a146b] hover:bg-[#e6eeff]'">
                                    Hari Ini
                                </button>
                                <button type="button"
                                        @click="pickDate(addDays(todayStr, 1))"
                                        class="rounded-lg py-1.5 text-[11px] font-bold transition cursor-pointer"
                                        :class="selectedDate === addDays(todayStr, 1) ? 'bg-[#1a146b] text-white' : 'bg-[#f4f7ff] text-[#1a146b] hover:bg-[#e6eeff]'">
                                    Besok
                                </button>
                            </div>

                            {{-- Header Bulan --}}
                            <div class="flex items-center justify-between mb-2 px-0.5">
                                <button type="button"
                                        @click="prevMonth()"
                                        :disabled="!canPrevMonth"
                                        aria-label="Bulan sebelumnya"
                                        class="h-7 w-7 inline-flex items-center justify-center rounded-lg border border-gray-200 text-[#1a146b] transition"
                                        :class="canPrevMonth ? 'hover:bg-[#f4f7ff] cursor-pointer' : 'opacity-30 cursor-not-allowed'">
                                    <x-lucide-chevron-left class="w-4 h-4" />
                                </button>
                                <span class="text-xs font-extrabold text-[#0b1c30]" x-text="monthNames[viewMonth] + ' ' + viewYear"></span>
                                <button type="button"
                                        @click="nextMonth()"
                                        aria-label="Bulan berikutnya"
                                        class="h-7 w-7 inline-flex items-center justify-center rounded-lg border border-gray-200 text-[#1a146b] hover:bg-[#f4f7ff] transition cursor-pointer">
                                    <x-lucide-chevron-right class="w-4 h-4" />
                                </button>
                            </div>

                            {{-- Nama Hari --}}
                            <div class="grid grid-cols-7 gap-0.5 text-center mb-1">
                                <template x-for="dh in dayHeaders" :key="dh">
                                    <span class="text-[10px] font-bold text-gray-600 py-1" x-text="dh"></span>
                                </template>
                            </div>

                            {{-- Grid Tanggal --}}
                            <div class="grid grid-cols-7 gap-0.5">
                                <template x-for="(cell, idx) in calendarCells" :key="idx">
                                    <div class="flex items-center justify-center">
                                        <template x-if="cell">
                                            <button type="button"
                                                    @click="pickDate(cell.iso)"
                                                    :disabled="cell.disabled"
                                                    class="h-8 w-8 rounded-lg text-xs font-bold flex flex-col items-center justify-center transition relative"
                                                    :class="cell.disabled
                                                        ? 'text-gray-300 cursor-not-allowed'
                                                        : (cell.isSelected
                                                            ? 'bg-[#1a146b] text-white shadow-xs ring-2 ring-[#acf847]/70 cursor-pointer'
                                                            : (cell.isToday
                                                                ? 'bg-[#eff4ff] text-[#1a146b] hover:bg-[#dfe9ff] cursor-pointer'
                                                                : 'text-[#0b1c30] hover:bg-[#f4f7ff] hover:text-[#1a146b] cursor-pointer'))">
                                                <span x-text="cell.day"></span>
                                                <span x-show="cell.isToday && !cell.isSelected" class="absolute bottom-1 h-1 w-1 rounded-full bg-[#416900]"></span>
                                            </button>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Submit CTA --}}
                    <div class="sm:col-span-3">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#1a146b] hover:bg-[#130f52] active:scale-[0.98] px-6 py-3 sm:py-3.5 text-sm sm:text-[15px] font-extrabold text-white shadow-md shadow-[#1a146b]/20 transition cursor-pointer">
                            <x-lucide-search class="w-4 h-4 sm:w-5 sm:h-5 text-[#acf847] shrink-0" />
                            <span>Cari Jadwal</span>
                        </button>
                    </div>
                </form>

                {{-- Quick Action Strip di Bawah Dock --}}
                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 px-1 text-xs sm:text-sm">
                    <a href="{{ route('jadwal.index') }}"
                       class="inline-flex items-center gap-1.5 font-bold text-[#1a146b] hover:underline">
                        <span class="w-4 h-4 sm:w-5 sm:h-5 text-[#416900] shrink-0 inline-flex items-center justify-center">
                            <x-icons.time class="w-full h-full" />
                        </span>
                        <span>Lihat Matriks Jadwal Hari Ini</span>
                        <x-lucide-arrow-right class="w-3.5 h-3.5 shrink-0" />
                    </a>
                    <a href="{{ route('katalog.index') }}"
                       class="text-[11px] sm:text-xs font-semibold text-gray-600 hover:text-[#1a146b] transition">
                        {{ $totalLapanganAktif }} Lapangan Aktif &bull; Lihat Semua
                    </a>
                </div>
            </div>

        </div>
    </section>

    {{-- 2. Keunggulan LapangIn (Asymmetric Product Bento — Tanpa 4 Kartu Icon Generik) --}}
    <section class="py-12 sm:py-20 bg-white border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8 sm:mb-12">
                <div class="max-w-2xl space-y-2">
                    <span class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.14em] text-[#416900]">
                        <span class="h-1.5 w-6 rounded-full bg-[#acf847]"></span>
                        Kenapa LapangIn
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-headline font-black text-[#0b1c30] tracking-tight leading-[1.15]">
                        Dirancang Buat Yang Serius Main.
                    </h2>
                </div>
                <p class="text-xs sm:text-sm text-gray-600 max-w-sm leading-relaxed">
                    Dari cek slot kosong, pilih cara bayar, sampai check-in, semuanya terhubung langsung ke kasir lapangan.
                </p>
            </div>

            <div class="flex md:grid md:grid-cols-12 gap-3.5 sm:gap-5 overflow-x-auto md:overflow-visible snap-x snap-mandatory -mx-4 px-4 md:mx-0 md:px-0 pb-2 md:pb-0 scrollbar-none">

                {{-- Bento 1 (7 Cols, Featured Dark Navy): Jadwal Real-Time + Mini Live Slot Preview --}}
                <div class="w-[84vw] max-w-[330px] md:w-auto md:max-w-none shrink-0 snap-center md:col-span-7 rounded-3xl bg-[#1a146b] text-white p-5 sm:p-8 flex flex-col justify-between gap-5 sm:gap-6 border border-[#1a146b] shadow-sm">
                    <div class="space-y-2 max-w-lg">
                        <h3 class="text-xl sm:text-2xl font-headline font-black tracking-tight leading-snug">
                            Jadwal Real-Time, Bebas Double-Booking
                        </h3>
                        <p class="text-xs sm:text-sm text-indigo-100/85 leading-relaxed">
                            Begitu slot dikunci lewat web atau kasir offline, jam tersebut langsung tertutup otomatis. Tidak ada lagi jadwal bentrok di hari main.
                        </p>
                    </div>

                    {{-- Mini Product Preview: 3 Slot Jam Live (Kontras Terang & Jelas di Atas Navy) --}}
                    <div class="grid grid-cols-3 gap-2 sm:gap-2.5 pt-2">
                        <div class="rounded-xl bg-red-50 border border-red-200 px-3 py-2.5 text-center shadow-xs">
                            <span class="block text-xs sm:text-sm font-black text-red-700 line-through">18:00</span>
                            <span class="block text-[10px] font-bold text-red-600 mt-0.5">Terisi</span>
                        </div>
                        <div class="rounded-xl bg-[#acf847] text-[#102000] px-3 py-2.5 text-center shadow-sm">
                            <span class="block text-xs sm:text-sm font-black">19:00</span>
                            <span class="block text-[10px] font-extrabold mt-0.5">Dipilih</span>
                        </div>
                        <div class="rounded-xl bg-white border border-white/90 px-3 py-2.5 text-center shadow-xs">
                            <div class="inline-flex items-center justify-center gap-1">
                                <span class="text-xs sm:text-sm font-black text-[#0b1c30]">20:00</span>
                                <span class="w-1.5 h-1.5 rounded-full bg-[#416900]"></span>
                            </div>
                            <span class="block text-[10px] font-bold text-emerald-700 mt-0.5">Tersedia</span>
                        </div>
                    </div>
                </div>

                {{-- Bento 2 (5 Cols, Crisp Light): Pembayaran Fleksibel Lunas / DP 50% --}}
                <div class="w-[84vw] max-w-[330px] md:w-auto md:max-w-none shrink-0 snap-center md:col-span-5 rounded-3xl bg-[#f8f9ff] p-5 sm:p-8 flex flex-col justify-between gap-5 sm:gap-6 border border-[#e5eeff]">
                    <div class="space-y-2">
                        <h3 class="text-xl sm:text-2xl font-headline font-black text-[#0b1c30] tracking-tight leading-snug">
                            Bayar Lunas 100% atau DP 50%
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                            Bebas pilih bayar penuh langsung beres, atau kunci jadwal dulu dengan DP 50% lewat QRIS, Virtual Account, dan E-Wallet.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="rounded-xl bg-white border border-[#1a146b]/20 px-3 py-2.5">
                            <span class="block text-xs font-extrabold text-[#1a146b]">Bayar Lunas</span>
                            <span class="block text-[10px] font-semibold text-gray-600 mt-0.5">Langsung main di venue</span>
                        </div>
                        <div class="rounded-xl bg-white border border-gray-200 px-3 py-2.5">
                            <span class="block text-xs font-extrabold text-[#0b1c30]">DP 50%</span>
                            <span class="block text-[10px] font-semibold text-gray-600 mt-0.5">Sisa bayar di kasir</span>
                        </div>
                    </div>
                </div>

                {{-- Bento 3 (5 Cols, Crisp Light): Standar Lapangan Kompetisi --}}
                <div class="w-[84vw] max-w-[330px] md:w-auto md:max-w-none shrink-0 snap-center md:col-span-5 rounded-3xl bg-[#f8f9ff] p-5 sm:p-8 flex flex-col justify-center border border-[#e5eeff]">
                    <div class="space-y-2">
                        <h3 class="text-xl sm:text-2xl font-headline font-black text-[#0b1c30] tracking-tight leading-snug">
                            Permukaan Standar Kompetisi
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                            Material lapangan ramah lutut dan antiselip, lengkap dengan pencahayaan LED merata untuk sesi siang hingga malam.
                        </p>
                    </div>
                </div>

                {{-- Bento 4 (7 Cols, Soft Court-Lime Surface): Check-in QR & Token --}}
                <div class="w-[84vw] max-w-[330px] md:w-auto md:max-w-none shrink-0 snap-center md:col-span-7 rounded-3xl bg-[#f4fbe6] p-5 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-5 sm:gap-6 border border-[#d7f0a6]">
                    <div class="space-y-2 max-w-md">
                        <h3 class="text-xl sm:text-2xl font-headline font-black text-[#0b1c30] tracking-tight leading-snug">
                            E-Tiket QR &amp; Token Instan
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-700 leading-relaxed">
                            Tak perlu screenshot bukti transfer ke chat admin. Cukup tunjukkan QR Code atau sebutkan 6 karakter token di meja resepsionis.
                        </p>
                    </div>

                    {{-- Mini Boarding Pass Chip --}}
                    <div class="shrink-0 rounded-2xl bg-white border border-[#c7e68b] px-4 py-3.5 shadow-xs flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-[#1a146b] text-[#acf847] flex items-center justify-center shrink-0">
                            <x-lucide-qr-code class="w-6 h-6" />
                        </div>
                        <div>
                            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-600">Token Gerbang</span>
                            <span class="block font-mono text-base font-black tracking-[0.18em] text-[#1a146b]">LPN720</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- 3. Kategori Olahraga --}}
    <section class="py-10 sm:py-20 bg-[#f8f9ff]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-6 sm:mb-12">
                <div>
                    <span class="inline-block text-[11px] sm:text-sm font-bold text-[#416900] uppercase tracking-wider mb-1.5 sm:mb-2">Kategori Lapangan</span>
                    <h2 class="text-xl sm:text-4xl font-headline font-black text-[#1a146b] tracking-tight">
                        Pilih Olahraga Favoritmu
                    </h2>
                </div>
                <div class="mt-2.5 md:mt-0">
                    <a
                        href="{{ route('katalog.index') }}"
                        class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-headline font-bold text-[#1a146b] hover:text-[#416900] transition-colors"
                    >
                        <span>Lihat Semua Lapangan</span>
                        <x-lucide-arrow-right class="w-4 h-4 shrink-0" />
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
                {{-- Kategori: Futsal --}}
                <div class="group relative rounded-2xl overflow-hidden bg-white border border-gray-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div class="relative h-28 sm:h-48 overflow-hidden bg-gray-900">
                        <img
                            src="https://images.unsplash.com/photo-1529900748604-07564a03e7a6?auto=format&fit=crop&w=500&q=75"
                            alt="Futsal"
                            width="350"
                            height="192"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy"
                            decoding="async"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-2 left-2 sm:top-3 sm:left-3 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md bg-[#1a146b] text-white text-[10px] sm:text-xs font-semibold">
                            Indoor &amp; Vinyl
                        </span>
                    </div>
                    <div class="p-3 sm:p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm sm:text-lg font-headline font-bold text-[#1a146b]">Futsal</h3>
                            <p class="mt-1 text-[11px] sm:text-xs text-gray-500 line-clamp-2">Lantai interlock dan vinyl berstandar kompetisi.</p>
                        </div>
                        <div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-[11px] sm:text-xs text-gray-500 hidden sm:inline">Mulai sesi</span>
                            <a
                                href="{{ route('katalog.index', ['kategori' => 'Futsal']) }}"
                                class="inline-flex items-center gap-1 text-xs font-headline font-bold text-[#1a146b] hover:text-[#416900]"
                            >
                                Explore <x-lucide-arrow-right class="w-3.5 h-3.5 shrink-0" />
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Kategori: Badminton --}}
                <div class="group relative rounded-2xl overflow-hidden bg-white border border-gray-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div class="relative h-28 sm:h-48 overflow-hidden bg-gray-900">
                        <img
                            src="https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=500&q=75"
                            alt="Badminton"
                            width="350"
                            height="192"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy"
                            decoding="async"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-2 left-2 sm:top-3 sm:left-3 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md bg-[#416900] text-white text-[10px] sm:text-xs font-semibold">
                            Karpet BWF
                        </span>
                    </div>
                    <div class="p-3 sm:p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm sm:text-lg font-headline font-bold text-[#1a146b]">Badminton</h3>
                            <p class="mt-1 text-[11px] sm:text-xs text-gray-500 line-clamp-2">Pencahayaan anti-silau dengan karpet standar PBSI/BWF.</p>
                        </div>
                        <div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-[11px] sm:text-xs text-gray-500 hidden sm:inline">Mulai sesi</span>
                            <a
                                href="{{ route('katalog.index', ['kategori' => 'Badminton']) }}"
                                class="inline-flex items-center gap-1 text-xs font-headline font-bold text-[#1a146b] hover:text-[#416900]"
                            >
                                Explore <x-lucide-arrow-right class="w-3.5 h-3.5 shrink-0" />
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Kategori: Basket --}}
                <div class="group relative rounded-2xl overflow-hidden bg-white border border-gray-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div class="relative h-28 sm:h-48 overflow-hidden bg-gray-900">
                        <img
                            src="https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=500&q=75"
                            alt="Basket"
                            width="350"
                            height="192"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy"
                            decoding="async"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-2 left-2 sm:top-3 sm:left-3 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md bg-[#1a146b] text-white text-[10px] sm:text-xs font-semibold">
                            FIBA Ring
                        </span>
                    </div>
                    <div class="p-3 sm:p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm sm:text-lg font-headline font-bold text-[#1a146b]">Basket</h3>
                            <p class="mt-1 text-[11px] sm:text-xs text-gray-500 line-clamp-2">Full court &amp; 3x3 dengan ring hidrolik kaca tempered.</p>
                        </div>
                        <div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-[11px] sm:text-xs text-gray-500 hidden sm:inline">Mulai sesi</span>
                            <a
                                href="{{ route('katalog.index', ['kategori' => 'Basket']) }}"
                                class="inline-flex items-center gap-1 text-xs font-headline font-bold text-[#1a146b] hover:text-[#416900]"
                            >
                                Explore <x-lucide-arrow-right class="w-3.5 h-3.5 shrink-0" />
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Kategori: Mini Soccer --}}
                <div class="group relative rounded-2xl overflow-hidden bg-white border border-gray-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div class="relative h-28 sm:h-48 overflow-hidden bg-gray-900">
                        <img
                            src="https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=500&q=75"
                            alt="Mini Soccer"
                            width="350"
                            height="192"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy"
                            decoding="async"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-2 left-2 sm:top-3 sm:left-3 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md bg-[#416900] text-white text-[10px] sm:text-xs font-semibold">
                            Sintetis Pro
                        </span>
                    </div>
                    <div class="p-3 sm:p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm sm:text-lg font-headline font-bold text-[#1a146b]">Mini Soccer</h3>
                            <p class="mt-1 text-[11px] sm:text-xs text-gray-500 line-clamp-2">Rumput sintetis monofilament dan drainase prima.</p>
                        </div>
                        <div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-[11px] sm:text-xs text-gray-500 hidden sm:inline">Mulai sesi</span>
                            <a
                                href="{{ route('katalog.index', ['kategori' => 'Mini Soccer']) }}"
                                class="inline-flex items-center gap-1 text-xs font-headline font-bold text-[#1a146b] hover:text-[#416900]"
                            >
                                Explore <x-lucide-arrow-right class="w-3.5 h-3.5 shrink-0" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 4. Alur Reservasi (Odoo-Style Illustrated Winding Roadmap) --}}
    <section id="cara-kerja" class="relative py-14 sm:py-24 bg-white border-t border-gray-100 overflow-hidden">
        {{-- Pola Latar Halus --}}
        <div class="absolute inset-0 opacity-[0.35] pointer-events-none"
             style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 26px 26px;"></div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-16">
                <span class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.14em] text-[#416900] mb-2">
                    <span class="h-1.5 w-5 rounded-full bg-[#acf847]"></span>
                    Alur Reservasi
                </span>
                <h2 class="text-2xl sm:text-4xl lg:text-[42px] font-headline font-black text-[#0b1c30] tracking-tight leading-tight">
                    Mulai Main dalam 3 Langkah
                </h2>
                <p class="mt-2.5 text-xs sm:text-base text-gray-600">
                    Tanpa chat admin manual. Ikuti jalurnya dari pilih jam sampai masuk lapangan.
                </p>
            </div>

            {{-- Illustrated Winding Roadmap Container --}}
            <div class="relative">
                {{-- Garis Putus-Putus Melengkung Ala Odoo (Desktop) --}}
                <svg class="hidden lg:block absolute inset-0 w-full h-full pointer-events-none"
                     viewBox="0 0 1120 380"
                     fill="none"
                     preserveAspectRatio="none"
                     aria-hidden="true">
                    <path d="M 10 265 L 330 265 Q 385 265 385 210 L 385 160 Q 385 105 440 105 L 1110 105"
                          stroke="#84cc16"
                          stroke-width="3.5"
                          stroke-linecap="round"
                          stroke-dasharray="10 11" />
                </svg>

                {{-- Garis Vertikal Putus-Putus (Mobile) --}}
                <div class="lg:hidden absolute left-1/2 top-16 bottom-16 -translate-x-1/2 w-0.5 border-l-2 border-dashed border-[#84cc16]/70 pointer-events-none" aria-hidden="true"></div>

                <div class="relative grid grid-cols-1 lg:grid-cols-3 gap-10 lg:gap-8 items-start">

                    {{-- LANGKAH 1 (Posisi Bawah Kiri di Desktop + Panah "Mulai di sini") --}}
                    <div class="relative flex flex-col items-center text-center lg:mt-32 bg-white/85 lg:bg-transparent backdrop-blur-[1px] rounded-2xl p-4 lg:p-0">
                        {{-- Label "Mulai di sini" + Panah Melengkung --}}
                        <div class="mb-2 flex flex-col items-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#eaf7d8] text-[#284200] text-[11px] font-extrabold tracking-wide -rotate-3">
                                Mulai di sini
                            </span>
                            <svg class="w-8 h-9 text-gray-400 mt-0.5" viewBox="0 0 36 40" fill="none" aria-hidden="true">
                                <path d="M12 4 C22 12, 24 22, 19 34" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                <path d="M14 28 L19 35 L25 30" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>

                        {{-- Ilustrasi Pedestal 1: Jadwal & Lapangan --}}
                        <div class="w-40 h-32 flex items-center justify-center">
                            <svg viewBox="0 0 180 140" fill="none" class="w-full h-full" aria-hidden="true">
                                {{-- Pedestal 3D --}}
                                <ellipse cx="90" cy="112" rx="64" ry="18" fill="#FFFFFF" stroke="#0B1C30" stroke-width="2.5" />
                                <path d="M26 104 V112 C26 122 55 130 90 130 C125 130 154 122 154 112 V104" fill="#FFFFFF" stroke="#0B1C30" stroke-width="2.5" />
                                <ellipse cx="90" cy="104" rx="64" ry="18" fill="#ACF847" stroke="#0B1C30" stroke-width="2.5" />
                                <ellipse cx="90" cy="104" rx="50" ry="12" fill="#D9FCA3" />

                                {{-- Kartu Jadwal di Atas Pedestal --}}
                                <rect x="46" y="24" width="88" height="72" rx="10" fill="#FFFFFF" stroke="#0B1C30" stroke-width="2.5" />
                                <path d="M46 42 H134" stroke="#0B1C30" stroke-width="2.5" />
                                <rect x="47.5" y="25.5" width="85" height="15.5" rx="8" fill="#1A146B" />
                                <circle cx="58" cy="33" r="2.2" fill="#ACF847" />
                                <circle cx="66" cy="33" r="2.2" fill="#FFFFFF" fill-opacity="0.6" />
                                <circle cx="74" cy="33" r="2.2" fill="#FFFFFF" fill-opacity="0.6" />

                                {{-- Grid Slot Mini --}}
                                <rect x="56" y="50" width="20" height="14" rx="4" fill="#EFF4FF" stroke="#0B1C30" stroke-width="1.8" />
                                <rect x="80" y="50" width="20" height="14" rx="4" fill="#ACF847" stroke="#0B1C30" stroke-width="1.8" />
                                <rect x="104" y="50" width="20" height="14" rx="4" fill="#EFF4FF" stroke="#0B1C30" stroke-width="1.8" />
                                <rect x="56" y="70" width="20" height="14" rx="4" fill="#EFF4FF" stroke="#0B1C30" stroke-width="1.8" />
                                <rect x="80" y="70" width="44" height="14" rx="4" fill="#1A146B" />

                                {{-- Kursor Klik --}}
                                <path d="M93 60 L108 69 L100 71 L98 79 L93 60 Z" fill="#FFFFFF" stroke="#0B1C30" stroke-width="2.2" stroke-linejoin="round" />
                            </svg>
                        </div>

                        <h3 class="mt-2 text-lg sm:text-xl font-headline font-black text-[#0b1c30]">
                            Pilih Jadwal &amp; Lapangan
                        </h3>
                        <p class="mt-1.5 text-xs sm:text-sm text-gray-600 max-w-[260px] leading-relaxed">
                            Cek slot jam yang kosong secara live, pilih tanggal main, dan tambah perlengkapan kalau perlu.
                        </p>
                        <a href="{{ route('jadwal.index') }}"
                           class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-[#1a146b] hover:bg-[#130f52] px-4 py-2 text-xs font-extrabold text-white shadow-xs transition active:scale-95">
                            <span>Cek Jadwal</span>
                            <x-lucide-arrow-right class="w-3.5 h-3.5 text-[#acf847]" />
                        </a>
                    </div>

                    {{-- LANGKAH 2 (Posisi Atas Tengah di Desktop) --}}
                    <div class="relative flex flex-col items-center text-center bg-white/85 lg:bg-transparent backdrop-blur-[1px] rounded-2xl p-4 lg:p-0">
                        {{-- Ilustrasi Pedestal 2: Bayar Lunas atau DP 50% --}}
                        <div class="w-40 h-32 flex items-center justify-center">
                            <svg viewBox="0 0 180 140" fill="none" class="w-full h-full" aria-hidden="true">
                                {{-- Pedestal 3D --}}
                                <ellipse cx="90" cy="112" rx="64" ry="18" fill="#FFFFFF" stroke="#0B1C30" stroke-width="2.5" />
                                <path d="M26 104 V112 C26 122 55 130 90 130 C125 130 154 122 154 112 V104" fill="#FFFFFF" stroke="#0B1C30" stroke-width="2.5" />
                                <ellipse cx="90" cy="104" rx="64" ry="18" fill="#ACF847" stroke="#0B1C30" stroke-width="2.5" />
                                <ellipse cx="90" cy="104" rx="50" ry="12" fill="#D9FCA3" />

                                {{-- Kartu Pembayaran --}}
                                <rect x="44" y="34" width="92" height="60" rx="10" fill="#1A146B" stroke="#0B1C30" stroke-width="2.5" />
                                <rect x="44" y="48" width="92" height="12" fill="#ACF847" stroke="#0B1C30" stroke-width="2" />
                                <rect x="55" y="70" width="26" height="10" rx="3" fill="#FFFFFF" />
                                <rect x="88" y="72" width="36" height="6" rx="3" fill="#FFFFFF" fill-opacity="0.45" />

                                {{-- Badge Centang Melayang --}}
                                <circle cx="132" cy="34" r="15" fill="#ACF847" stroke="#0B1C30" stroke-width="2.5" />
                                <path d="M126 34 L130.5 38.5 L139 30" stroke="#0B1C30" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>

                        <h3 class="mt-2 text-lg sm:text-xl font-headline font-black text-[#0b1c30]">
                            Bayar Lunas atau DP 50%
                        </h3>
                        <p class="mt-1.5 text-xs sm:text-sm text-gray-600 max-w-[270px] leading-relaxed">
                            Bebas pilih bayar langsung lunas (100%) atau DP 50% dulu untuk mengunci slot via QRIS, VA, dan E-Wallet.
                        </p>
                        <div class="mt-4 flex flex-wrap items-center justify-center gap-1.5">
                            <span class="px-2.5 py-1 rounded-lg bg-[#eaf7d8] text-[#284200] text-[11px] font-extrabold">Lunas 100%</span>
                            <span class="px-2.5 py-1 rounded-lg bg-[#f4f7ff] text-[#1a146b] text-[11px] font-extrabold">Atau DP 50%</span>
                        </div>
                    </div>

                    {{-- LANGKAH 3 (Posisi Atas Kanan di Desktop) --}}
                    <div class="relative flex flex-col items-center text-center bg-white/85 lg:bg-transparent backdrop-blur-[1px] rounded-2xl p-4 lg:p-0">
                        {{-- Ilustrasi Pedestal 3: E-Tiket QR & Langsung Main --}}
                        <div class="w-40 h-32 flex items-center justify-center">
                            <svg viewBox="0 0 180 140" fill="none" class="w-full h-full" aria-hidden="true">
                                {{-- Pedestal 3D --}}
                                <ellipse cx="90" cy="112" rx="64" ry="18" fill="#FFFFFF" stroke="#0B1C30" stroke-width="2.5" />
                                <path d="M26 104 V112 C26 122 55 130 90 130 C125 130 154 122 154 112 V104" fill="#FFFFFF" stroke="#0B1C30" stroke-width="2.5" />
                                <ellipse cx="90" cy="104" rx="64" ry="18" fill="#ACF847" stroke="#0B1C30" stroke-width="2.5" />
                                <ellipse cx="90" cy="104" rx="50" ry="12" fill="#D9FCA3" />

                                {{-- E-Tiket HP + QR Code --}}
                                <rect x="60" y="18" width="60" height="82" rx="10" fill="#FFFFFF" stroke="#0B1C30" stroke-width="2.5" />
                                <rect x="78" y="24" width="24" height="3.5" rx="1.75" fill="#CBD5E1" />

                                {{-- Kotak QR --}}
                                <rect x="70" y="34" width="40" height="40" rx="6" fill="#EFF4FF" stroke="#0B1C30" stroke-width="2" />
                                <rect x="75" y="39" width="10" height="10" rx="1.5" fill="#1A146B" />
                                <rect x="95" y="39" width="10" height="10" rx="1.5" fill="#1A146B" />
                                <rect x="75" y="59" width="10" height="10" rx="1.5" fill="#1A146B" />
                                <rect x="90" y="54" width="6" height="6" rx="1" fill="#1A146B" />
                                <rect x="99" y="61" width="6" height="8" rx="1" fill="#416900" />

                                {{-- Strip Token Bawah --}}
                                <rect x="72" y="81" width="36" height="10" rx="4" fill="#1A146B" />
                                <path d="M80 86 H100" stroke="#ACF847" stroke-width="2.2" stroke-linecap="round" />
                            </svg>
                        </div>

                        <h3 class="mt-2 text-lg sm:text-xl font-headline font-black text-[#0b1c30]">
                            Scan QR &amp; Langsung Main
                        </h3>
                        <p class="mt-1.5 text-xs sm:text-sm text-gray-600 max-w-[260px] leading-relaxed">
                            Tunjukkan E-Tiket QR Code atau Token 6 karakter di meja resepsionis untuk check-in instan.
                        </p>
                        <a href="{{ route('katalog.index') }}"
                           class="mt-4 inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white hover:border-[#1a146b] px-4 py-2 text-xs font-extrabold text-[#1a146b] shadow-2xs transition active:scale-95">
                            <span>Jelajahi Lapangan</span>
                            <x-lucide-arrow-right class="w-3.5 h-3.5" />
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </section>

    {{-- 5. Bottom CTA Banner (Hanya tampil untuk pengunjung yang belum login) --}}
    @guest
    <section class="py-10 sm:py-20 bg-[#f8f9ff]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl bg-[#1a146b] px-5 py-8 sm:px-12 sm:py-16 shadow-2xl text-white">
                {{-- Decorative Glow elements --}}
                <div class="absolute -top-24 -right-24 w-96 h-96 bg-[#acf847]/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 max-w-3xl mx-auto text-center space-y-5">
                    <h2 class="text-2xl sm:text-4xl lg:text-5xl font-headline font-black tracking-tight leading-tight">
                        Daftar Akun Sekarang, Riwayat Booking Tersimpan Rapi
                    </h2>

                    <p class="text-sm sm:text-base text-gray-200 max-w-2xl mx-auto leading-relaxed">
                        Nikmati kemudahan pelacakan tiket digital, klaim invoice transaksi, dan kelola reschedule jadwal pertandingan tanpa khawatir data hilang.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                        <a
                            href="{{ route('register') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-[#acf847] hover:bg-[#9de437] text-[#1a146b] font-headline font-bold text-sm shadow-md transition-all duration-200 transform hover:-translate-y-0.5 cursor-pointer"
                        >
                            <x-lucide-user-plus class="w-4 h-4 shrink-0" />
                            <span>Daftar Akun Gratis</span>
                        </a>

                        <a
                            href="{{ route('katalog.index') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl border border-white/30 hover:border-white text-white font-headline font-semibold text-sm bg-white/10 hover:bg-white/20 transition-all duration-200 cursor-pointer"
                        >
                            <span>Jelajahi Dulu</span>
                            <x-lucide-arrow-right class="w-4 h-4 shrink-0" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endguest

</div>
@endsection
