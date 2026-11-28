@extends('layouts.app')

@section('title', 'Cek Jadwal Lapangan - LapangIn')

@section('content')
<div class="min-h-screen bg-[#f8f9ff] text-[#0b1c30] pb-16 relative"
     x-data="{
         selectedKategori: '{{ $kategori }}',
         selectedLapangan: '{{ $selectedLapanganId ?? '' }}',
         searchLapangan: '',
         todayStr: '{{ $today }}',
         activeDate: '{{ $tanggal }}',
         dateOptions: {{ Js::from($dateOptions) }},
         calOpen: false,
         calYear: {{ \Carbon\Carbon::parse($tanggal)->year }},
         calMonth: {{ \Carbon\Carbon::parse($tanggal)->month - 1 }},
         monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
         shortMonths: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
         dayHeaders: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
         jadwalBaseUrl: '{{ route('jadwal.index') }}',
         matrix: {{ Js::from($matrix) }},
         totalTersedia: {{ $totalSlotTersedia }},
         totalBooked: {{ $totalSlotDibooking }},
         clickedSlotKey: null,
         isLoadingDate: false,
         hasSwitchedDate: false,
         dateAbortController: null,
         currentPage: 1,
         perPage: 10,
         lapanganBaseUrl: '{{ url('/lapangan') }}',

         formatISO(y, m, d) {
             return y + '-' + String(m + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
         },

         addDays(iso, days) {
             const [y, m, d] = iso.split('-').map(Number);
             const dt = new Date(y, m - 1, d + days);
             return this.formatISO(dt.getFullYear(), dt.getMonth(), dt.getDate());
         },

         jadwalDateUrl(dateStr) {
             const url = new URL(this.jadwalBaseUrl, window.location.origin);
             const current = new URLSearchParams(window.location.search);
             current.forEach((v, k) => url.searchParams.set(k, v));
             url.searchParams.set('tanggal', dateStr);
             return url.toString();
         },

         get canPrevMonth() {
             const [ty, tm] = this.todayStr.split('-').map(Number);
             return this.calYear > ty || (this.calYear === ty && this.calMonth > (tm - 1));
         },

         prevMonth() {
             if (!this.canPrevMonth) return;
             if (this.calMonth === 0) { this.calMonth = 11; this.calYear--; }
             else { this.calMonth--; }
         },

         nextMonth() {
             if (this.calMonth === 11) { this.calMonth = 0; this.calYear++; }
             else { this.calMonth++; }
         },

         get calendarCells() {
             const firstDay = new Date(this.calYear, this.calMonth, 1).getDay();
             const daysInMonth = new Date(this.calYear, this.calMonth + 1, 0).getDate();
             const cells = [];
             for (let i = 0; i < firstDay; i++) cells.push(null);
             for (let d = 1; d <= daysInMonth; d++) {
                 const iso = this.formatISO(this.calYear, this.calMonth, d);
                 cells.push({
                     day: d,
                     iso,
                     disabled: iso < this.todayStr,
                     isToday: iso === this.todayStr,
                     isSelected: iso === this.activeDate,
                 });
             }
             return cells;
         },

         pickCalendarDate(iso) {
             if (iso < this.todayStr) return;
             const [y, m] = iso.split('-').map(Number);
             this.calYear = y;
             this.calMonth = m - 1;
             this.calOpen = false;
             this.switchDate(iso, this.jadwalDateUrl(iso));
         },

         init() {
             // Reset state ketika pengguna kembali via tombol Back browser (bfcache)
             window.addEventListener('pageshow', () => {
                 this.clickedSlotKey = null;
                 this.isLoadingDate = false;
             });
             this.$watch('selectedKategori', () => { this.currentPage = 1; });
             this.$watch('selectedLapangan', () => { this.currentPage = 1; });
             this.$watch('searchLapangan', () => { this.currentPage = 1; });
         },

         async switchDate(dateStr, targetUrl) {
             if (this.activeDate === dateStr && !this.isLoadingDate) return;

             if (this.dateAbortController) {
                 this.dateAbortController.abort();
             }
             const controller = new AbortController();
             this.dateAbortController = controller;

             const alreadyInStrip = this.dateOptions.some(d => d.date === dateStr);
             this.activeDate = dateStr;
             const [sy, sm] = dateStr.split('-').map(Number);
             this.calYear = sy;
             this.calMonth = sm - 1;
             this.clickedSlotKey = null;
             this.hasSwitchedDate = true;

             // Hanya tampilkan skeleton jika request > 220ms (kalau cepat langsung ganti tanpa flicker)
             const skeletonTimer = setTimeout(() => {
                 if (!controller.signal.aborted) {
                     this.isLoadingDate = true;
                 }
             }, 220);

             try {
                 const response = await fetch(targetUrl, {
                     headers: {
                         'Accept': 'application/json',
                         'X-Requested-With': 'XMLHttpRequest',
                     },
                     signal: controller.signal,
                 });

                 if (!response.ok) {
                     window.location.href = targetUrl;
                     return;
                 }

                 const data = await response.json();
                 if (controller.signal.aborted) return;

                 if (data && data.success && Array.isArray(data.matrix)) {
                     this.matrix = data.matrix;
                     if (!alreadyInStrip && Array.isArray(data.dateOptions)) {
                         this.dateOptions = data.dateOptions;
                     }
                     window.history.replaceState({}, '', targetUrl);
                 } else {
                     window.location.href = targetUrl;
                     return;
                 }
             } catch (err) {
                 if (err && err.name === 'AbortError') return;
                 window.location.href = targetUrl;
                 return;
             } finally {
                 clearTimeout(skeletonTimer);
                 if (!controller.signal.aborted) {
                     this.isLoadingDate = false;
                 }
             }
         },

         rowByLapangan(lapId) {
             return this.matrix.find(r => r.lapangan && r.lapangan.id === lapId) || null;
         },

         slotBookingUrl(lapId, jamMulai) {
             return `${this.lapanganBaseUrl}/${lapId}?tanggal=${encodeURIComponent(this.activeDate)}&jam=${encodeURIComponent(jamMulai)}`;
         },

         venueDetailUrl(lapId) {
             return `${this.lapanganBaseUrl}/${lapId}?tanggal=${encodeURIComponent(this.activeDate)}`;
         },

         selectSlot(url, slotKey) {
             if (this.clickedSlotKey || this.isLoadingDate) return;
             this.clickedSlotKey = slotKey;
             window.location.href = url;
         },

         filterMatrix() {
             return this.matrix.filter(row => {
                 const matchKategori = (this.selectedKategori === 'Semua' || !this.selectedKategori || row.lapangan.kategori === this.selectedKategori);
                 const matchLapangan = (!this.selectedLapangan || row.lapangan.id === this.selectedLapangan);
                 const matchSearch = (!this.searchLapangan.trim() || 
                     (row.lapangan.nama && row.lapangan.nama.toLowerCase().includes(this.searchLapangan.toLowerCase().trim())) ||
                     (row.lapangan.kategori && row.lapangan.kategori.toLowerCase().includes(this.searchLapangan.toLowerCase().trim()))
                 );
                 return matchKategori && matchLapangan && matchSearch;
             });
         },

         totalPages() {
             return Math.max(1, Math.ceil(this.filterMatrix().length / this.perPage));
         },

         isItemInCurrentPage(indexInFiltered) {
             const start = (this.currentPage - 1) * this.perPage;
             const end = start + this.perPage;
             return indexInFiltered >= start && indexInFiltered < end;
         },

         countFilteredTersedia() {
             return this.filterMatrix().reduce((acc, row) => acc + (row.tersedia_count || 0), 0);
         },

         countFilteredBooked() {
             return this.filterMatrix().reduce((acc, row) => acc + (row.booked_count || 0), 0);
         }
     }">

    {{-- Hero Section (Selaras dengan Katalog Lapangan) --}}
    <section class="px-4 sm:px-6 lg:px-8 pt-5 pb-4 sm:pt-8 sm:pb-8">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-end justify-between gap-4 sm:gap-6">
            <div>
                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-[#1a146b] max-w-3xl">
                    Cek Ketersediaan
                    <span class="text-[#416900]">
                        Lapangan
                    </span>
                </h1>
                <p class="mt-2 sm:mt-4 max-w-2xl text-xs sm:text-base leading-relaxed sm:leading-7 text-gray-600">
                    Pantau ketersediaan slot jam setiap venue secara transparan. Klik slot jam hijau untuk langsung memesan tiket.
                </p>
            </div>

            {{-- Summary Stats Pills (3 kolom sejajar rapi di HP, shimmer saat ganti tanggal) --}}
            <div class="grid grid-cols-3 gap-2 sm:flex sm:items-center sm:gap-3 w-full md:w-auto">
                <div class="bg-white rounded-xl sm:rounded-2xl px-3 py-2.5 sm:px-5 sm:py-3 border border-gray-200/80 shadow-xs text-center sm:text-left sm:min-w-[110px]">
                    <span class="text-[10px] sm:text-[11px] uppercase tracking-wider font-bold text-gray-500 block whitespace-nowrap">Slot Kosong</span>
                    <span x-show="!isLoadingDate" class="text-xl sm:text-2xl font-black text-[#416900]" x-text="countFilteredTersedia()">{{ $totalSlotTersedia }}</span>
                    <span x-show="isLoadingDate" style="display: none;" class="inline-block h-6 sm:h-7 w-10 sm:w-12 mt-0.5 rounded-lg skeleton-shimmer"></span>
                </div>
                <div class="bg-white rounded-xl sm:rounded-2xl px-3 py-2.5 sm:px-5 sm:py-3 border border-gray-200/80 shadow-xs text-center sm:text-left sm:min-w-[110px]">
                    <span class="text-[10px] sm:text-[11px] uppercase tracking-wider font-bold text-gray-500 block whitespace-nowrap">Terisi</span>
                    <span x-show="!isLoadingDate" class="text-xl sm:text-2xl font-black text-rose-600" x-text="countFilteredBooked()">{{ $totalSlotDibooking }}</span>
                    <span x-show="isLoadingDate" style="display: none;" class="inline-block h-6 sm:h-7 w-8 sm:w-10 mt-0.5 rounded-lg skeleton-shimmer"></span>
                </div>
                <div class="bg-white rounded-xl sm:rounded-2xl px-3 py-2.5 sm:px-5 sm:py-3 border border-gray-200/80 shadow-xs text-center sm:text-left sm:min-w-[110px]">
                    <span class="text-[10px] sm:text-[11px] uppercase tracking-wider font-bold text-gray-500 block whitespace-nowrap">Venue Tampil</span>
                    <span class="text-xl sm:text-2xl font-black text-[#1a146b]" x-text="filterMatrix().length">{{ count($matrix) }}</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Filter & Date Carousel (Swipe halus di HP, Full 7 kolom di Desktop) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-1 sm:mt-2">
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-3.5 sm:p-5">
            <div class="flex flex-col gap-3.5 sm:gap-5">

                {{-- Baris 1: Header Tanggal + Tombol Kalender Pill, diikuti Strip 7 Hari Full Width --}}
                <div class="pb-3.5 sm:pb-4 border-b border-gray-100 w-full space-y-2.5 sm:space-y-3"
                     @click.outside="calOpen = false"
                     @keydown.escape.window="calOpen = false">
                    <div class="relative flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 text-xs sm:text-sm font-extrabold text-[#0b1c30]">
                            <x-icons.calendar class="w-4 h-4 sm:w-5 sm:h-5 text-[#1a146b] shrink-0" />
                            <span>Pilih Tanggal</span>
                        </div>

                        <button
                            type="button"
                            @click="calOpen = !calOpen"
                            :aria-expanded="calOpen"
                            aria-label="Pilih tanggal dari kalender"
                            class="shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl border text-[11px] sm:text-xs font-extrabold transition cursor-pointer active:scale-95"
                            :class="calOpen ? 'bg-[#1a146b] text-white border-[#1a146b] shadow-xs' : 'bg-[#f4f7ff] text-[#1a146b] border-[#dce9ff] hover:bg-[#e8f0ff]'"
                        >
                            <span x-text="monthNames[calMonth] + ' ' + calYear"></span>
                            <span class="shrink-0 transition-transform duration-200" :class="calOpen ? 'rotate-180 text-[#acf847]' : 'text-[#1a146b]/70'">
                                <x-lucide-chevron-down class="w-3.5 h-3.5" />
                            </span>
                        </button>

                        {{-- Popover Kalender Custom --}}
                        <div
                            x-show="calOpen"
                            x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1 scale-[0.98]"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 -translate-y-1 scale-[0.98]"
                            class="absolute right-0 top-full mt-2 z-40 w-[292px] rounded-2xl bg-white border border-gray-200/90 shadow-xl shadow-[#0b1c30]/15 p-3"
                        >
                            <div class="grid grid-cols-2 gap-1.5 mb-3">
                                <button type="button"
                                        @click="pickCalendarDate(todayStr)"
                                        class="rounded-lg py-1.5 text-[11px] font-bold transition cursor-pointer"
                                        :class="activeDate === todayStr ? 'bg-[#1a146b] text-white' : 'bg-[#f4f7ff] text-[#1a146b] hover:bg-[#e6eeff]'">
                                    Hari Ini
                                </button>
                                <button type="button"
                                        @click="pickCalendarDate(addDays(todayStr, 1))"
                                        class="rounded-lg py-1.5 text-[11px] font-bold transition cursor-pointer"
                                        :class="activeDate === addDays(todayStr, 1) ? 'bg-[#1a146b] text-white' : 'bg-[#f4f7ff] text-[#1a146b] hover:bg-[#e6eeff]'">
                                    Besok
                                </button>
                            </div>

                            <div class="flex items-center justify-between mb-2 px-0.5">
                                <button type="button"
                                        @click="prevMonth()"
                                        :disabled="!canPrevMonth"
                                        aria-label="Bulan sebelumnya"
                                        class="h-7 w-7 inline-flex items-center justify-center rounded-lg border border-gray-200 text-[#1a146b] transition"
                                        :class="canPrevMonth ? 'hover:bg-[#f4f7ff] cursor-pointer' : 'opacity-30 cursor-not-allowed'">
                                    <x-lucide-chevron-left class="w-4 h-4" />
                                </button>
                                <span class="text-xs font-extrabold text-[#0b1c30]" x-text="monthNames[calMonth] + ' ' + calYear"></span>
                                <button type="button"
                                        @click="nextMonth()"
                                        aria-label="Bulan berikutnya"
                                        class="h-7 w-7 inline-flex items-center justify-center rounded-lg border border-gray-200 text-[#1a146b] hover:bg-[#f4f7ff] transition cursor-pointer">
                                    <x-lucide-chevron-right class="w-4 h-4" />
                                </button>
                            </div>

                            <div class="grid grid-cols-7 gap-0.5 text-center mb-1">
                                <template x-for="dh in dayHeaders" :key="dh">
                                    <span class="text-[10px] font-bold text-gray-400 py-1" x-text="dh"></span>
                                </template>
                            </div>

                            <div class="grid grid-cols-7 gap-0.5">
                                <template x-for="(cell, idx) in calendarCells" :key="idx">
                                    <div class="flex items-center justify-center">
                                        <template x-if="cell">
                                            <button type="button"
                                                    @click="pickCalendarDate(cell.iso)"
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

                    {{-- Strip 7 Hari Full Width --}}
                    <div class="flex sm:grid sm:grid-cols-7 overflow-x-auto gap-2 sm:gap-3 snap-x [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                        <template x-for="dOpt in dateOptions" :key="dOpt.date">
                            <a
                                :href="jadwalDateUrl(dOpt.date)"
                                data-no-progress
                                @click.prevent="switchDate(dOpt.date, jadwalDateUrl(dOpt.date))"
                                :class="activeDate === dOpt.date ? 'bg-[#1a146b] text-white border-[#1a146b] shadow-md' : 'bg-gray-50/90 hover:bg-white hover:border-[#1a146b]/40 text-gray-700 border-gray-200/90 shadow-2xs'"
                                class="flex flex-col items-center justify-center min-w-[68px] shrink-0 sm:min-w-0 sm:w-full snap-start py-2.5 sm:py-3.5 px-2 rounded-xl sm:rounded-2xl border transition-all text-center cursor-pointer"
                            >
                                <span
                                    :class="activeDate === dOpt.date ? 'text-[#acf847]' : 'text-gray-500'"
                                    class="text-[10px] sm:text-xs font-extrabold tracking-wider whitespace-nowrap"
                                    x-text="dOpt.label_top"
                                ></span>
                                <span class="text-lg sm:text-2xl font-black leading-tight mt-0.5 sm:mt-1" x-text="dOpt.day_num"></span>
                                <span class="text-[10px] sm:text-xs font-semibold opacity-80 mt-0.5" x-text="dOpt.month_name"></span>
                            </a>
                        </template>
                    </div>
                </div>

                {{-- Baris 2: Filter Kategori & Filter Spesifik Lapangan (Instan Client-side) --}}
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-2.5 sm:gap-3">
                    {{-- Kategori Badges (Instan 0 ms, horizontal scroll rapi) --}}
                    <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto py-0.5 scrollbar-none">
                        <span class="text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mr-1 shrink-0 whitespace-nowrap">Kategori:</span>

                        <button
                            type="button"
                            @click="selectedKategori = 'Semua'"
                            :class="selectedKategori === 'Semua' ? 'bg-[#1a146b] text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-3 sm:px-3.5 py-1.5 rounded-full text-xs font-bold transition flex items-center gap-1 cursor-pointer shrink-0 whitespace-nowrap"
                        >
                            Semua
                        </button>

                        @foreach ($kategoriList as $kat)
                            @if(filled($kat))
                                <button
                                    type="button"
                                    @click="selectedKategori = '{{ $kat }}'"
                                    :class="selectedKategori === '{{ $kat }}' ? 'bg-[#1a146b] text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 sm:px-3.5 py-1.5 rounded-full text-xs font-bold transition cursor-pointer shrink-0 whitespace-nowrap"
                                >
                                    {{ $kat }}
                                </button>
                            @endif
                        @endforeach
                    </div>

                    {{-- Search Bar Lapangan (Modern & Elegan) --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <div class="relative w-full sm:w-64 md:w-72">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                                <x-lucide-search class="w-4 h-4 shrink-0" />
                            </span>
                            <input
                                type="text"
                                x-model="searchLapangan"
                                placeholder="Cari nama lapangan..."
                                class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-9 pr-8 text-xs font-bold text-gray-800 placeholder-gray-400 shadow-2xs transition focus:border-[#1a146b] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#1a146b]"
                            >
                            <button
                                type="button"
                                x-show="searchLapangan.trim().length > 0"
                                @click="searchLapangan = ''"
                                class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 hover:text-gray-600 transition cursor-pointer"
                                title="Hapus pencarian"
                            >
                                <x-lucide-x class="w-4 h-4 shrink-0" />
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Legend Info Bar (Grid 2x2 ringkas di HP, 1 baris di Desktop) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-3.5 sm:mt-5">
        <div class="bg-white rounded-xl border border-gray-100 px-3.5 py-2.5 sm:px-4 sm:py-3 flex flex-col lg:flex-row lg:items-center justify-between gap-2.5 sm:gap-4 text-[11px] sm:text-xs">
            <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-x-3 gap-y-2 sm:gap-5">
                <span class="hidden sm:inline font-bold text-gray-500 uppercase tracking-wider">Keterangan Slot:</span>
                
                {{-- Slot Tersedia --}}
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <span class="w-5 h-5 rounded-md bg-[#eaf7d8] border border-[#acf847] text-[#416900] flex items-center justify-center shrink-0">
                        <x-lucide-check class="w-3 h-3" stroke-width="2.75" />
                    </span>
                    <span class="font-medium text-gray-700"><span class="sm:hidden">Tersedia</span><span class="hidden sm:inline">Tersedia (Bisa Dipesan)</span></span>
                </div>

                {{-- Slot Terisi --}}
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <span class="w-5 h-5 rounded-md bg-red-100 border border-red-200 text-red-600 flex items-center justify-center shrink-0">
                        <x-lucide-x class="w-3 h-3" stroke-width="2.75" />
                    </span>
                    <span class="font-medium text-gray-700"><span class="sm:hidden">Terisi</span><span class="hidden sm:inline">Terisi / Dibooking</span></span>
                </div>

                {{-- Slot Pending --}}
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <span class="w-5 h-5 rounded-md bg-amber-100 border border-amber-300 text-amber-700 flex items-center justify-center shrink-0">
                        <x-lucide-hourglass class="w-3 h-3" stroke-width="2.25" />
                    </span>
                    <span class="font-medium text-gray-700"><span class="sm:hidden">Menunggu Bayar</span><span class="hidden sm:inline">Menunggu Pembayaran</span></span>
                </div>

                {{-- Slot Lewat --}}
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <span class="w-5 h-5 rounded-md bg-gray-100 border border-gray-200 text-gray-400 flex items-center justify-center shrink-0">
                        <x-lucide-minus class="w-3 h-3" stroke-width="2.5" />
                    </span>
                    <span class="font-medium text-gray-400">Lewat Jam</span>
                </div>
            </div>

            <div class="text-gray-500 text-[10px] sm:text-[11px] italic border-t border-gray-100 pt-2 lg:border-t-0 lg:pt-0">
                *Klik kotak jam <strong>tersedia</strong> untuk langsung memesan lapangan.
            </div>
        </div>
    </section>

    {{-- Matriks Jadwal ala Bioskop --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        @if (empty($matrix))
            <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
                <x-lucide-calendar-x class="w-12 h-12 text-gray-300 shrink-0" />
                <h3 class="mt-3 text-lg font-extrabold text-gray-700">Tidak ada lapangan ditemukan</h3>
                <p class="mt-1 text-sm text-gray-500">Coba sesuaikan filter kategori atau tanggal Anda.</p>
                <a href="{{ route('jadwal.index') }}" class="mt-4 inline-block px-5 py-2 rounded-xl bg-[#1a146b] text-white text-xs font-bold hover:bg-opacity-90">
                    Reset Filter
                </a>
            </div>
        @else
            <div class="flex flex-col gap-6">
                @foreach ($matrix as $row)
                    @php
                        $lap = $row['lapangan'];
                        $slots = $row['slots'];
                    @endphp
                    <div
                        x-show="(function() {
                            const filtered = filterMatrix();
                            const idx = filtered.findIndex(r => r.lapangan.id === '{{ $lap->id }}');
                            if (idx === -1) return false;
                            return idx >= (currentPage - 1) * perPage && idx < currentPage * perPage;
                        })()"
                        x-transition
                        style="content-visibility: auto; contain-intrinsic-size: 1px 260px;"
                        class="bg-white rounded-2xl border border-gray-150 shadow-xs hover:shadow-md transition-shadow overflow-hidden"
                    >
                        {{-- Header Bar Lapangan --}}
                        <div class="px-3.5 py-3 sm:px-5 sm:py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3">
                            <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                                <img
                                    src="{{ $lap->foto_thumbnail_url }}"
                                    alt="{{ $lap->nama }}"
                                    width="48"
                                    height="48"
                                    loading="lazy"
                                    decoding="async"
                                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl object-cover border border-gray-200 shadow-xs shrink-0"
                                    onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($lap->nama) }}&background=1a146b&color=acf847&size=200';"
                                >
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                        <span class="px-2 py-0.5 rounded-md bg-[#1a146b]/10 text-[#1a146b] text-[10px] font-extrabold uppercase tracking-wider">
                                            {{ $lap->kategori }}
                                        </span>
                                        <span class="text-[11px] sm:text-xs text-gray-500">
                                            {{ $row['jam_operasional']['jam_buka'] }} – {{ $row['jam_operasional']['jam_tutup'] }} WIB
                                        </span>
                                    </div>
                                    <h3 class="text-sm sm:text-lg font-extrabold text-[#0b1c30] mt-0.5 truncate">
                                        {{ $lap->nama }}
                                    </h3>
                                </div>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end gap-2 sm:gap-3 w-full sm:w-auto pt-2 sm:pt-0 border-t border-gray-100 sm:border-t-0">
                                <div>
                                    {{-- Skeleton Badge ala GitHub saat ganti tanggal --}}
                                    <span x-show="isLoadingDate" style="display: none;" class="inline-block h-6 w-24 sm:w-28 rounded-full skeleton-shimmer align-middle"></span>
                                    <span
                                        x-show="!isLoadingDate"
                                        :class="(rowByLapangan('{{ $lap->id }}')?.tersedia_count ?? {{ $row['tersedia_count'] }}) > 0 ? 'bg-[#eaf7d8] text-[#416900]' : 'bg-red-50 text-red-600'"
                                        class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 rounded-full text-[11px] sm:text-xs font-bold"
                                    >
                                        <span
                                            :class="(rowByLapangan('{{ $lap->id }}')?.tersedia_count ?? {{ $row['tersedia_count'] }}) > 0 ? 'bg-[#416900]' : 'bg-red-500'"
                                            class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full"
                                        ></span>
                                        <span x-text="(rowByLapangan('{{ $lap->id }}')?.tersedia_count ?? {{ $row['tersedia_count'] }}) + ' Slot Tersedia'">{{ $row['tersedia_count'] }} Slot Tersedia</span>
                                    </span>
                                </div>
                                <a
                                    href="{{ route('katalog.show', ['id' => $lap->id, 'tanggal' => $tanggal]) }}"
                                    :href="venueDetailUrl('{{ $lap->id }}')"
                                    class="px-3 sm:px-3.5 py-1.5 rounded-xl border border-gray-200 hover:border-[#1a146b] text-[#1a146b] text-[11px] sm:text-xs font-bold transition flex items-center gap-1 shrink-0"
                                >
                                    Detail
                                    <x-lucide-arrow-right class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" />
                                </a>
                            </div>
                        </div>

                        {{-- Cinema Seat Matrix of Hours (4 kolom rapat di HP, 6-8 kolom di Desktop) --}}
                        <div class="p-3 sm:p-5">
                            {{-- 1. GitHub-style In-Place Skeleton Grid saat ganti tanggal --}}
                            <template x-if="isLoadingDate">
                                <div class="grid grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-1.5 sm:gap-2.5">
                                    <template x-for="n in {{ max(8, count($slots)) }}" :key="n">
                                        <div class="flex flex-col items-center justify-center py-2.5 px-1 sm:py-3.5 sm:px-2 rounded-xl border border-gray-100 bg-white gap-1.5">
                                            <div class="w-9 sm:w-10 h-3 rounded-md skeleton-shimmer"></div>
                                            <div class="w-11 sm:w-12 h-2 rounded-md skeleton-shimmer"></div>
                                            <div class="w-8 sm:w-9 h-2.5 rounded-md mt-0.5 skeleton-shimmer"></div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- 2. Initial Server-Rendered Slots (Fast SSR / FCP) --}}
                            <div x-show="!isLoadingDate && !hasSwitchedDate" class="grid grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-1.5 sm:gap-2.5">
                                @forelse ($slots as $slot)
                                    @php
                                        $status = $slot['status'];
                                        $isTersedia = $slot['is_tersedia'];
                                        $isBooked = ($status === 'dibooking');
                                        $isPending = ($status === 'pending');
                                        $isLewat = ($status === 'lewat_jam');
                                    @endphp

                                    @if ($isTersedia)
                                        @php
                                            $slotKey = $lap->id . '_' . $slot['jam_mulai'];
                                        @endphp
                                        <a
                                            href="{{ route('katalog.show', ['id' => $lap->id, 'tanggal' => $tanggal, 'jam' => $slot['jam_mulai']]) }}"
                                            @click.prevent="selectSlot('{{ route('katalog.show', ['id' => $lap->id, 'tanggal' => $tanggal, 'jam' => $slot['jam_mulai']]) }}', '{{ $slotKey }}')"
                                            :class="{
                                                'pointer-events-none': clickedSlotKey !== null,
                                                'bg-[#1a146b] border-[#1a146b] text-white shadow-md scale-[1.02]': clickedSlotKey === '{{ $slotKey }}',
                                                'bg-white border-gray-200 hover:border-[#1a146b]/40 hover:bg-[#f8faff] text-[#0b1c30] hover:shadow-xs': clickedSlotKey !== '{{ $slotKey }}'
                                            }"
                                            class="group relative flex flex-col items-center justify-center py-2.5 px-1 sm:py-3 sm:px-2 rounded-xl border transition-all duration-150 active:scale-95 cursor-pointer text-center gap-0.5"
                                            title="Slot Tersedia: {{ $slot['jam_label'] }}"
                                        >
                                            <div class="flex items-center gap-1">
                                                <span class="text-xs font-black tracking-tight">{{ $slot['jam_mulai'] }}</span>
                                                <span class="w-1.5 h-1.5 rounded-full" :class="clickedSlotKey === '{{ $slotKey }}' ? 'bg-[#acf847]' : 'bg-[#416900]'"></span>
                                            </div>
                                            <span class="text-[9px] sm:text-[10px]" :class="clickedSlotKey === '{{ $slotKey }}' ? 'text-white/80' : 'text-gray-400'">
                                                {{ $slot['jam_mulai'] }} - {{ $slot['jam_selesai'] }}
                                            </span>
                                            <span class="mt-0.5 text-[9px] sm:text-[10px] font-bold"
                                                  :class="clickedSlotKey === '{{ $slotKey }}' ? 'text-[#acf847]' : 'text-emerald-600'"
                                                  x-text="clickedSlotKey === '{{ $slotKey }}' ? 'DIPILIH' : 'Tersedia'">
                                                Tersedia
                                            </span>
                                        </a>
                                    @elseif ($isPending)
                                        <div
                                            class="relative flex flex-col items-center justify-center py-2 px-1 sm:py-3 sm:px-2 rounded-xl border border-amber-300 bg-amber-50 text-amber-900 cursor-not-allowed opacity-90 text-center"
                                            title="Menunggu Pembayaran: {{ $slot['jam_label'] }}"
                                        >
                                            <span class="text-xs font-bold tracking-tight text-amber-800">
                                                {{ $slot['jam_mulai'] }}
                                            </span>
                                            <span class="text-[9px] sm:text-[10px] font-medium text-amber-600">
                                                s/d {{ $slot['jam_selesai'] }}
                                            </span>
                                            <span class="mt-1 px-1.5 py-0.2 rounded text-[8px] sm:text-[9px] font-bold uppercase bg-amber-200/70 text-amber-800">
                                                PROSES
                                            </span>
                                        </div>
                                    @elseif ($isBooked)
                                        <div
                                            class="relative flex flex-col items-center justify-center py-2 px-1 sm:py-3 sm:px-2 rounded-xl border border-red-200 bg-red-50 text-red-900 cursor-not-allowed opacity-85 text-center"
                                            title="Sudah Dibooking: {{ $slot['jam_label'] }}"
                                        >
                                            <span class="text-xs font-bold tracking-tight text-red-800 line-through">
                                                {{ $slot['jam_mulai'] }}
                                            </span>
                                            <span class="text-[9px] sm:text-[10px] font-medium text-red-600">
                                                s/d {{ $slot['jam_selesai'] }}
                                            </span>
                                            <span class="mt-1 px-1.5 py-0.2 rounded text-[8px] sm:text-[9px] font-bold uppercase bg-red-200/70 text-red-800">
                                                TERISI
                                            </span>
                                        </div>
                                    @else
                                        {{-- Lewat Jam --}}
                                        <div
                                            class="relative flex flex-col items-center justify-center py-2 px-1 sm:py-3 sm:px-2 rounded-xl border border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed text-center"
                                            title="Jam telah berlalu: {{ $slot['jam_label'] }}"
                                        >
                                            <span class="text-xs font-semibold tracking-tight text-gray-400">
                                                {{ $slot['jam_mulai'] }}
                                            </span>
                                            <span class="text-[9px] sm:text-[10px] font-medium text-gray-400">
                                                s/d {{ $slot['jam_selesai'] }}
                                            </span>
                                            <span class="mt-1 px-1.5 py-0.2 rounded text-[8px] sm:text-[9px] font-semibold uppercase bg-gray-200 text-gray-500">
                                                LEWAT
                                            </span>
                                        </div>
                                    @endif
                                @empty
                                    <div class="col-span-full py-4 text-center text-xs text-gray-400">
                                        Tidak ada jam operasional untuk hari ini.
                                    </div>
                                @endforelse
                            </div>

                            {{-- 3. Reactive Alpine Slots setelah user ganti tanggal (Seamless tanpa reload halaman) --}}
                            <template x-if="hasSwitchedDate && !isLoadingDate">
                                <div class="grid grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-1.5 sm:gap-2.5">
                                    <template x-for="slot in (rowByLapangan('{{ $lap->id }}')?.slots || [])" :key="activeDate + '_' + slot.jam_mulai">
                                        <div>
                                            <template x-if="slot.is_tersedia">
                                                <a
                                                    :href="slotBookingUrl('{{ $lap->id }}', slot.jam_mulai)"
                                                    @click.prevent="selectSlot(slotBookingUrl('{{ $lap->id }}', slot.jam_mulai), '{{ $lap->id }}_' + slot.jam_mulai)"
                                                    :class="{
                                                        'pointer-events-none': clickedSlotKey !== null,
                                                        'bg-[#1a146b] border-[#1a146b] text-white shadow-md scale-[1.02]': clickedSlotKey === ('{{ $lap->id }}_' + slot.jam_mulai),
                                                        'bg-white border-gray-200 hover:border-[#1a146b]/40 hover:bg-[#f8faff] text-[#0b1c30] hover:shadow-xs': clickedSlotKey !== ('{{ $lap->id }}_' + slot.jam_mulai)
                                                    }"
                                                    class="group relative flex flex-col items-center justify-center py-2.5 px-1 sm:py-3 sm:px-2 rounded-xl border transition-all duration-150 active:scale-95 cursor-pointer text-center gap-0.5"
                                                    :title="'Slot Tersedia: ' + slot.jam_label"
                                                >
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs font-black tracking-tight" x-text="slot.jam_mulai"></span>
                                                        <span class="w-1.5 h-1.5 rounded-full" :class="clickedSlotKey === ('{{ $lap->id }}_' + slot.jam_mulai) ? 'bg-[#acf847]' : 'bg-[#416900]'"></span>
                                                    </div>
                                                    <span class="text-[9px] sm:text-[10px]" :class="clickedSlotKey === ('{{ $lap->id }}_' + slot.jam_mulai) ? 'text-white/80' : 'text-gray-400'" x-text="slot.jam_mulai + ' - ' + slot.jam_selesai"></span>
                                                    <span class="mt-0.5 text-[9px] sm:text-[10px] font-bold"
                                                          :class="clickedSlotKey === ('{{ $lap->id }}_' + slot.jam_mulai) ? 'text-[#acf847]' : 'text-emerald-600'"
                                                          x-text="clickedSlotKey === ('{{ $lap->id }}_' + slot.jam_mulai) ? 'DIPILIH' : 'Tersedia'"></span>
                                                </a>
                                            </template>

                                            <template x-if="!slot.is_tersedia && slot.status === 'pending'">
                                                <div
                                                    class="relative flex flex-col items-center justify-center py-2 px-1 sm:py-3 sm:px-2 rounded-xl border border-amber-300 bg-amber-50 text-amber-900 cursor-not-allowed opacity-90 text-center"
                                                    :title="'Menunggu Pembayaran: ' + slot.jam_label"
                                                >
                                                    <span class="text-xs font-bold tracking-tight text-amber-800" x-text="slot.jam_mulai"></span>
                                                    <span class="text-[9px] sm:text-[10px] font-medium text-amber-600" x-text="'s/d ' + slot.jam_selesai"></span>
                                                    <span class="mt-1 px-1.5 py-0.2 rounded text-[8px] sm:text-[9px] font-bold uppercase bg-amber-200/70 text-amber-800">
                                                        PROSES
                                                    </span>
                                                </div>
                                            </template>

                                            <template x-if="!slot.is_tersedia && slot.status === 'dibooking'">
                                                <div
                                                    class="relative flex flex-col items-center justify-center py-2 px-1 sm:py-3 sm:px-2 rounded-xl border border-red-200 bg-red-50 text-red-900 cursor-not-allowed opacity-85 text-center"
                                                    :title="'Sudah Dibooking: ' + slot.jam_label"
                                                >
                                                    <span class="text-xs font-bold tracking-tight text-red-800 line-through" x-text="slot.jam_mulai"></span>
                                                    <span class="text-[9px] sm:text-[10px] font-medium text-red-600" x-text="'s/d ' + slot.jam_selesai"></span>
                                                    <span class="mt-1 px-1.5 py-0.2 rounded text-[8px] sm:text-[9px] font-bold uppercase bg-red-200/70 text-red-800">
                                                        TERISI
                                                    </span>
                                                </div>
                                            </template>

                                            <template x-if="!slot.is_tersedia && slot.status !== 'pending' && slot.status !== 'dibooking'">
                                                <div
                                                    class="relative flex flex-col items-center justify-center py-2 px-1 sm:py-3 sm:px-2 rounded-xl border border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed text-center"
                                                    :title="'Jam telah berlalu: ' + slot.jam_label"
                                                >
                                                    <span class="text-xs font-semibold tracking-tight text-gray-400" x-text="slot.jam_mulai"></span>
                                                    <span class="text-[9px] sm:text-[10px] font-medium text-gray-400" x-text="'s/d ' + slot.jam_selesai"></span>
                                                    <span class="mt-1 px-1.5 py-0.2 rounded text-[8px] sm:text-[9px] font-semibold uppercase bg-gray-200 text-gray-500">
                                                        LEWAT
                                                    </span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <template x-if="(rowByLapangan('{{ $lap->id }}')?.slots || []).length === 0">
                                        <div class="col-span-full py-4 text-center text-xs text-gray-400">
                                            Tidak ada jam operasional untuk tanggal ini.
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                @endforeach

                {{-- Empty state saat hasil pencarian / filter kosong --}}
                <div
                    x-show="filterMatrix().length === 0"
                    class="bg-white rounded-2xl border border-gray-100 p-12 text-center"
                    style="display: none;"
                >
                    <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-gray-50 text-gray-400 flex items-center justify-center">
                        <x-lucide-search-x class="w-6 h-6 shrink-0" />
                    </div>
                    <h3 class="text-base font-bold text-gray-800">Tidak ada lapangan yang cocok</h3>
                    <p class="mt-1 text-xs sm:text-sm text-gray-500">
                        Tidak ditemukan venue yang sesuai dengan filter atau kata kunci pencarian Anda.
                    </p>
                    <button
                        type="button"
                        @click="selectedKategori = 'Semua'; selectedLapangan = ''; searchLapangan = ''"
                        class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#1a146b] text-white text-xs font-bold hover:bg-indigo-900 transition cursor-pointer shadow-xs"
                    >
                        Reset Filter &amp; Pencarian
                    </button>
                </div>

                {{-- Pagination Controls (10 Baris per Halaman) --}}
                <div
                    x-show="filterMatrix().length > perPage"
                    class="bg-white rounded-2xl border border-gray-150 p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4 mt-2"
                >
                    <div class="text-xs text-gray-500 font-medium">
                        Menampilkan <span class="font-bold text-[#1a146b]" x-text="((currentPage - 1) * perPage) + 1"></span>
                        – <span class="font-bold text-[#1a146b]" x-text="Math.min(currentPage * perPage, filterMatrix().length)"></span>
                        dari <span class="font-bold text-[#1a146b]" x-text="filterMatrix().length"></span> venue lapangan
                    </div>

                    <div class="flex items-center gap-1.5">
                        {{-- Tombol Sebelumnya --}}
                        <button
                            type="button"
                            @click="currentPage > 1 ? currentPage-- : null"
                            :disabled="currentPage <= 1"
                            :class="currentPage <= 1 ? 'opacity-40 cursor-not-allowed bg-gray-50 text-gray-400' : 'bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 cursor-pointer shadow-2xs'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1 transition"
                        >
                            <x-lucide-chevron-left class="w-4 h-4 shrink-0" />
                            <span>Sebelumnya</span>
                        </button>

                        {{-- Page Number Badges --}}
                        <div class="flex items-center gap-1 px-1">
                            <template x-for="p in totalPages()" :key="p">
                                <button
                                    type="button"
                                    @click="currentPage = p"
                                    :class="currentPage === p ? 'bg-[#1a146b] text-white shadow-xs' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 border border-gray-200'"
                                    class="w-7 h-7 rounded-lg text-xs font-black transition flex items-center justify-center cursor-pointer"
                                    x-text="p"
                                ></button>
                            </template>
                        </div>

                        {{-- Tombol Selanjutnya --}}
                        <button
                            type="button"
                            @click="currentPage < totalPages() ? currentPage++ : null"
                            :disabled="currentPage >= totalPages()"
                            :class="currentPage >= totalPages() ? 'opacity-40 cursor-not-allowed bg-gray-50 text-gray-400' : 'bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 cursor-pointer shadow-2xs'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1 transition"
                        >
                            <span>Selanjutnya</span>
                            <x-lucide-chevron-right class="w-4 h-4 shrink-0" />
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </section>

</div>
@endsection

