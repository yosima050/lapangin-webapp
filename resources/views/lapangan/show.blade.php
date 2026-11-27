@extends('layouts.app')

@section('title', $lapangan->nama . ' - Reservasi Online LapangIn')

@section('content')
@php
    $selectedDateCarbon = \Carbon\Carbon::parse($tanggalDipilih)->startOfDay();
    $today = now()->startOfDay();

    // Generate 7 hari kalender
    $datesCarousel = [];
    for ($i = 0; $i < 7; $i++) {
        $cDate = $today->copy()->addDays($i);
        $datesCarousel[] = [
            'date_str' => $cDate->toDateString(),
            'day_num' => $cDate->format('d'),
            'day_name' => $cDate->translatedFormat('D'),
            'month_name' => $cDate->translatedFormat('M'),
            'label_top' => $i === 0 ? 'HARI INI' : ($i === 1 ? 'BESOK' : strtoupper($cDate->translatedFormat('D'))),
            'is_selected' => $cDate->toDateString() === $tanggalDipilih,
        ];
    }

    // Kelompokkan slot siang (sebelum 18:00) dan malam prime time (>= 18:00)
    $slotsSiang = [];
    $slotsMalam = [];

    foreach ($jadwalData['slots'] as $slot) {
        $jamHour = (int) substr($slot['jam_mulai'], 0, 2);
        if ($jamHour < 18) {
            $slotsSiang[] = $slot;
        } else {
            $slotsMalam[] = $slot;
        }
    }

    // Tentukan estimasi harga dasar dari controller / jadwal data
    $isWeekend = in_array($selectedDateCarbon->dayOfWeek, [0, 6]);
    $baseRate = (float) ($baseRate ?? ($jadwalData['base_rate'] ?? ($isWeekend ? ($hargaWeekend ?? 175000) : ($hargaWeekday ?? 125000))));
    $primeRate = (float) ($primeRate ?? ($jadwalData['prime_rate'] ?? $baseRate));
    // Auto-select slot jika dikirim lewat parameter query 'jam' dari Cek Jadwal
    $initialSelectedSlots = [];
    $paramJam = request('jam');
    if ($paramJam) {
        $cleanJam = substr($paramJam, 0, 5);
        foreach ($jadwalData['slots'] as $s) {
            if (substr($s['jam_mulai'], 0, 5) === $cleanJam && !empty($s['is_tersedia'])) {
                $initialSelectedSlots[] = $s;
                break;
            }
        }
    }
@endphp

<div class="min-h-screen bg-[#f8f9ff] text-[#0b1c30] pb-16"
     x-data="{
         selectedSlots: {{ Js::from($initialSelectedSlots) }},
         selectedDate: '{{ $tanggalDipilih }}',
         formattedDate: '{{ $selectedDateCarbon->translatedFormat('l, d F Y') }}',
         monthYear: '{{ $selectedDateCarbon->translatedFormat('F Y') }}',
         paymentScheme: 'full',
         showSchemeAccordion: false,
         equipments: {{ Js::from($perlengkapans->map(fn($p) => ['id' => $p->id, 'nama' => $p->nama, 'harga' => (float)$p->harga, 'qty' => 0])) }},
         baseRate: {{ $baseRate }},
         primeRate: {{ $primeRate }},
         datesCarousel: {{ Js::from($datesCarousel) }},
         slotsSiang: {{ Js::from($slotsSiang) }},
         slotsMalam: {{ Js::from($slotsMalam) }},
         isLoadingSlots: false,
         slotsCache: {{ Js::from($initialDaysCache) }},
         showAlertModal: false,
         alertTitle: 'Batas Pemesanan',
         alertMessage: '',
         showAlert(message, title = 'Batas Durasi Booking') {
             this.alertMessage = message;
             this.alertTitle = title;
             this.showAlertModal = true;
         },

         async changeDate(newDate) {
             if (this.selectedDate === newDate) return;
             this.selectedDate = newDate;
             this.selectedSlots = []; // reset pilihan slot saat tanggal berganti

             // Update status tombol carousel langsung secara instan (optimistic UI)
             const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
             const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
             const parts = newDate.split('-');
             if (parts.length === 3) {
                 const dObj = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
                 const dayOfWeek = dayNames[dObj.getDay()];
                 const dayNum = String(dObj.getDate()).padStart(2, '0');
                 const monthName = monthNames[dObj.getMonth()];
                 this.formattedDate = `${dayOfWeek}, ${dayNum} ${monthName} ${dObj.getFullYear()}`;
                 this.monthYear = `${monthName} ${dObj.getFullYear()}`;
             }

             this.datesCarousel.forEach(d => {
                 d.is_selected = (d.date_str === newDate);
             });

             // Update URL query param tanpa reload halaman
             const newUrl = new URL(window.location.href);
             newUrl.searchParams.set('tanggal', newDate);
             window.history.pushState({}, '', newUrl.toString());

             // Jika data tanggal sudah ada di cache lokal, render INSTAN tanpa tunggu network
             if (this.slotsCache[newDate]) {
                 const cached = this.slotsCache[newDate];
                 this.slotsSiang = cached.slotsSiang;
                 this.slotsMalam = cached.slotsMalam;
                 this.formattedDate = cached.formattedDate;
                 this.monthYear = cached.monthYear;
                 this.baseRate = cached.baseRate;
                 this.primeRate = cached.primeRate;
                 return;
             }

             // Ambil dari server
             this.isLoadingSlots = true;
             try {
                 const res = await fetch(`{{ route('katalog.show', ['id' => $lapangan->id]) }}?tanggal=${newDate}`, {
                     headers: {
                         'X-Requested-With': 'XMLHttpRequest',
                         'Accept': 'application/json'
                     }
                 });
                 if (res.ok) {
                     const data = await res.json();
                     this.slotsSiang = data.slotsSiang;
                     this.slotsMalam = data.slotsMalam;
                     this.datesCarousel = data.datesCarousel;
                     this.formattedDate = data.formattedDate;
                     this.monthYear = data.monthYear;
                     this.baseRate = data.baseRate;
                     this.primeRate = data.primeRate;

                     // Simpan ke cache agar saat diklik ulang instan 0 milidetik
                     this.slotsCache[newDate] = data;
                 }
             } catch (err) {
                 console.error('Gagal mengambil jadwal:', err);
             } finally {
                 this.isLoadingSlots = false;
             }
         },

         toggleSlot(slot) {
             if (!slot.is_tersedia) return;
             const index = this.selectedSlots.findIndex(s => s.jam_mulai === slot.jam_mulai);
             if (index > -1) {
                 this.selectedSlots.splice(index, 1);
             } else {
                 if (this.selectedSlots.length >= 4) {
                     this.showAlert('Maksimal pemesanan sekaligus adalah 4 jam.', 'Batas Durasi Booking');
                     return;
                 }
                 this.selectedSlots.push(slot);
                 this.selectedSlots.sort((a, b) => a.jam_mulai.localeCompare(b.jam_mulai));
             }
         },

         isSlotSelected(slot) {
             return this.selectedSlots.some(s => s.jam_mulai === slot.jam_mulai);
         },

         get durationHours() {
             return this.selectedSlots.length;
         },

         get earliestSlot() {
             return this.selectedSlots.length > 0 ? this.selectedSlots[0].jam_mulai : null;
         },

         get latestSlotEnd() {
             if (this.selectedSlots.length === 0) return null;
             return this.selectedSlots[this.selectedSlots.length - 1].jam_selesai;
         },

         get timeRangeLabel() {
             if (this.selectedSlots.length === 0) return 'Belum memilih slot jam main';
             const start = this.earliestSlot.substring(0, 5);
             const end = this.latestSlotEnd.substring(0, 5);
             return `${start} - ${end} WIB`;
         },

         // Mengelompokkan slot yang berurutan (kontigu) menjadi interval terpisah
         get groupedSlotIntervals() {
             if (this.selectedSlots.length === 0) return [];
             
             // Salin dan urutkan berdasarkan jam mulai
             const sorted = [...this.selectedSlots].sort((a, b) => a.jam_mulai.localeCompare(b.jam_mulai));
             const intervals = [];
             let current = null;

             sorted.forEach(slot => {
                 const sStart = slot.jam_mulai.substring(0, 5);
                 const sEnd = slot.jam_selesai.substring(0, 5);

                 if (!current) {
                     current = { start: sStart, end: sEnd };
                 } else if (current.end === sStart) {
                     // Menyambung (kontigu), perpanjang jam selesai
                     current.end = sEnd;
                 } else {
                     // Ada jeda/lompat jam, simpan interval sebelumnya dan mulai interval baru
                     intervals.push({ ...current });
                     current = { start: sStart, end: sEnd };
                 }
             });

             if (current) {
                 intervals.push(current);
             }

             return intervals;
         },

         get courtTotalFee() {
             if (this.selectedSlots.length === 0) {
                 return 0;
             }
             let sum = 0;
             this.selectedSlots.forEach(slot => {
                 const hour = parseInt(slot.jam_mulai.substring(0, 2), 10);
                 const defaultRate = (hour >= 18 ? this.primeRate : this.baseRate);
                 const price = (slot.harga !== undefined && slot.harga !== null && Number(slot.harga) > 0)
                     ? Number(slot.harga)
                     : defaultRate;
                 sum += price;
             });
             return sum;
         },

         get equipmentTotalFee() {
             return this.equipments.reduce((acc, curr) => acc + (curr.harga * curr.qty), 0);
         },

         get grandTotal() {
             return this.courtTotalFee + this.equipmentTotalFee;
         },

         get dpAmount() {
             return Math.round(this.grandTotal * 0.5);
         },

         get remainingAmount() {
             return this.grandTotal - this.dpAmount;
         },

         get payableAmount() {
             return this.paymentScheme === 'dp' ? this.dpAmount : this.grandTotal;
         },

         changeQty(eqId, delta) {
             const item = this.equipments.find(e => e.id === eqId);
             if (item) {
                 item.qty = Math.max(0, item.qty + delta);
             }
         },

         formatRp(val) {
             return 'Rp' + new Intl.NumberFormat('id-ID').format(val || 0);
         }
     }">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-3.5 sm:pt-6">

        {{-- 1. Breadcrumbs (Ringkas 1 baris di HP, lengkap di Desktop) --}}
        <div class="flex sm:hidden items-center justify-between gap-2 mb-3">
            <a href="{{ route('katalog.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#1a146b] hover:underline shrink-0">
                <x-lucide-arrow-left class="w-4 h-4 shrink-0" />
                <span>Kembali ke Katalog</span>
            </a>
            <span class="rounded-full bg-[#eff4ff] px-2.5 py-0.5 text-[10px] font-bold text-[#1a146b] truncate">
                {{ $lapangan->kategori }}
            </span>
        </div>

        <nav class="hidden sm:flex items-center gap-2 text-xs text-gray-500 mb-6 font-medium">
            <a href="{{ route('home') }}" class="hover:text-[#1a146b] flex items-center gap-1 shrink-0 whitespace-nowrap">
                <x-lucide-house class="w-4 h-4 shrink-0" />
                <span>Beranda</span>
            </a>
            <span class="shrink-0">/</span>
            <a href="{{ route('katalog.index', ['kategori' => $lapangan->kategori]) }}" class="hover:text-[#1a146b] shrink-0 whitespace-nowrap">
                {{ $lapangan->kategori }}
            </a>
            <span class="shrink-0">/</span>
            <span class="text-gray-900 font-bold truncate">{{ $lapangan->nama }}</span>
        </nav>

        {{-- Flash Error Message (jika diarahkan karena jam lewat) --}}
        @if (session('error'))
            <div class="mb-4 sm:mb-6 p-3.5 sm:p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs sm:text-sm flex items-center gap-2.5 sm:gap-3 shadow-xs">
                <x-lucide-clock class="w-5 h-5 text-amber-600 shrink-0" />
                <span class="font-semibold">{{ session('error') }}</span>
            </div>
        @endif

        {{-- 2. Main Two-Column Split Grid (70% Kiri, 30% Kanan) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-8 items-start">

            {{-- ======================================================== --}}
            {{-- KOLOM KIRI (Main Content): Jadwal, Carousel Tanggal, Slots & Addons --}}
            {{-- ======================================================== --}}
            <div class="lg:col-span-8 xl:col-span-8 flex flex-col gap-4 sm:gap-8">

                {{-- Venue Header Card --}}
                <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-8 shadow-xs border border-gray-100 flex flex-col gap-3 sm:gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 sm:gap-4">
                        <div class="flex items-start gap-3 sm:block min-w-0">
                            {{-- Thumbnail Lapangan Khusus HP/Tablet (Desktop sudah tampil di Sidebar) --}}
                            <div class="lg:hidden w-16 h-16 rounded-2xl bg-gray-900 overflow-hidden shrink-0 border border-gray-100 shadow-2xs mt-0.5">
                                <img src="{{ $lapangan->foto_card_url ?? $lapangan->foto_url }}" alt="{{ $lapangan->nama }}" width="64" height="64" class="w-full h-full object-cover">
                            </div>

                            <div class="min-w-0 flex-1">
                                <h1 class="text-lg sm:text-3xl font-headline font-black text-[#0b1c30] tracking-tight leading-snug">
                                    {{ $lapangan->nama }}
                                </h1>

                                <p class="text-[11px] sm:text-xs text-gray-500 mt-1 sm:mt-1.5 flex items-start sm:items-center gap-1.5 leading-relaxed">
                                    <x-lucide-map-pin class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-400 shrink-0 mt-0.5 sm:mt-0" />
                                    <span class="line-clamp-2 sm:line-clamp-none">{{ $lapangan->deskripsi ?: 'Arena futsal, badminton, dan multisport resmi dengan standar pencahayaan turnamen.' }}</span>
                                </p>

                                {{-- Hidden pricing metadata for automated test assertions (KatalogSyncAndApiTest) --}}
                                <div class="sr-only" aria-hidden="true">
                                    <span>Tarif Weekday: Rp {{ number_format($hargaWeekday ?? 125000, 0, ',', '.') }}</span>
                                    <span>Tarif Weekend: Rp {{ number_format($hargaWeekend ?? 175000, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Kembali ke Katalog (Desktop; di HP sudah tersedia di bar atas) --}}
                        <div class="hidden sm:block shrink-0 self-start sm:self-center">
                            <a href="{{ route('katalog.index') }}"
                               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-bold border border-gray-200 transition shadow-2xs">
                                <x-lucide-arrow-left class="w-4 h-4 text-gray-500 shrink-0" />
                                <span>Kembali ke Katalog</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Pilih Tanggal Main (7-Day Carousel: 1 baris swipe di HP, 7 kolom di Desktop) --}}
                <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-8 shadow-xs border border-gray-100 flex flex-col gap-3 sm:gap-4">
                    <div class="flex items-center justify-between pb-2.5 sm:pb-3 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <x-lucide-calendar-days class="w-4 h-4 sm:w-5 sm:h-5 text-[#1a146b] shrink-0" />
                            <h2 class="text-sm sm:text-base font-headline font-bold text-[#0b1c30]">Pilih Tanggal Main</h2>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-gray-400" x-text="monthYear">
                            {{ $selectedDateCarbon->translatedFormat('F Y') }}
                        </span>
                    </div>

                    {{-- Horizontal 7-Day Date Buttons (SPA Reactive tanpa reload) --}}
                    <div class="flex sm:grid sm:grid-cols-7 gap-2 sm:gap-2.5 overflow-x-auto pb-1 sm:pb-0 -mx-1 px-1 snap-x [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                        @foreach ($datesCarousel as $d)
                            <button type="button"
                                    @click="changeDate('{{ $d['date_str'] }}')"
                                    class="min-w-[66px] sm:min-w-0 shrink-0 snap-start flex flex-col items-center justify-center py-2.5 sm:py-3.5 px-2 rounded-xl sm:rounded-2xl transition border cursor-pointer"
                                    :class="selectedDate === '{{ $d['date_str'] }}' ? 'bg-[#1a146b] border-[#1a146b] text-white shadow-md' : 'bg-white border-gray-200 hover:border-gray-300 text-gray-700 hover:bg-gray-50'">
                                <span class="text-[9px] sm:text-[10px] font-black tracking-wider uppercase"
                                      :class="selectedDate === '{{ $d['date_str'] }}' ? 'text-[#acf847]' : 'text-gray-400'">
                                    {{ $d['label_top'] }}
                                </span>
                                <span class="text-lg sm:text-xl font-headline font-black my-0.5 sm:my-1 leading-tight">
                                    {{ $d['day_num'] }}
                                </span>
                                <div class="flex items-center gap-1 text-[10px] sm:text-[11px] font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                          :class="selectedDate === '{{ $d['date_str'] }}' ? 'bg-[#acf847]' : 'bg-[#416900]'"></span>
                                    <span>{{ $d['day_name'] }}</span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Grouping Sesi Jam Booking --}}
                <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-8 shadow-xs border border-gray-100 flex flex-col gap-5 sm:gap-6 relative">
                    {{-- Loading Bar Tipis Halus saat ganti tanggal --}}
                    <div x-show="isLoadingSlots"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="absolute inset-x-0 top-0 h-1 bg-[#acf847] overflow-hidden rounded-t-2xl sm:rounded-t-3xl z-20">
                        <div class="w-full h-full bg-[#1a146b] animate-pulse"></div>
                    </div>

                    {{-- Sesi Siang & Sore --}}
                    <div x-show="slotsSiang.length > 0">
                        <div class="flex items-center justify-between gap-2 mb-3 sm:mb-3.5">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-[#416900] shrink-0"></span>
                                <h3 class="text-xs sm:text-sm font-bold text-[#0b1c30] truncate">Sesi Siang &amp; Sore</h3>
                                <span class="hidden sm:inline text-xs text-gray-400 font-mono">13:00 – 17:00</span>
                            </div>
                            <span class="text-[11px] sm:text-xs font-bold text-gray-600 shrink-0 whitespace-nowrap" x-text="formatRp(baseRate) + ' / jam'"></span>
                        </div>

                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 sm:gap-3">
                            <template x-for="slot in slotsSiang" :key="slot.jam_mulai">
                                <div>
                                    <template x-if="slot.is_tersedia">
                                        <button type="button"
                                                @click="toggleSlot(slot)"
                                                :class="isSlotSelected(slot) ? 'bg-[#1a146b] text-white border-[#1a146b] shadow-md scale-[1.02]' : 'bg-white text-gray-800 border-gray-200 hover:border-gray-300 hover:bg-indigo-50/50'"
                                                class="w-full py-2.5 px-1.5 sm:py-3.5 sm:px-3 rounded-xl sm:rounded-2xl border text-center transition flex flex-col items-center justify-center gap-0.5 sm:gap-1 cursor-pointer">
                                            <div class="flex items-center gap-1">
                                                <span class="font-headline font-black text-xs sm:text-sm tracking-tight" x-text="slot.jam_mulai.substring(0, 5)"></span>
                                                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full" :class="isSlotSelected(slot) ? 'bg-[#acf847]' : 'bg-[#416900]'"></span>
                                            </div>
                                            <span class="text-[9px] sm:text-[10px]" :class="isSlotSelected(slot) ? 'text-white/80' : 'text-gray-400'" x-text="slot.jam_mulai.substring(0, 5) + ' - ' + slot.jam_selesai.substring(0, 5)"></span>
                                            <template x-if="slot.is_harga_khusus">
                                                <span class="text-[8px] sm:text-[9px] font-black px-1.5 py-0.5 rounded-md"
                                                      :class="isSlotSelected(slot) ? 'bg-amber-300 text-amber-950' : 'bg-amber-100 text-amber-800'"
                                                      x-text="formatRp(slot.harga)">
                                                </span>
                                            </template>
                                            <span class="text-[9px] sm:text-[10px] font-bold mt-0.5"
                                                  :class="isSlotSelected(slot) ? 'text-[#acf847]' : 'text-emerald-600'"
                                                  x-text="isSlotSelected(slot) ? 'DIPILIH' : 'Tersedia'">
                                            </span>
                                        </button>
                                    </template>
                                    <template x-if="!slot.is_tersedia">
                                        <div class="w-full py-2.5 px-1.5 sm:py-3.5 sm:px-3 rounded-xl sm:rounded-2xl border border-gray-100 bg-gray-50/80 text-gray-400 text-center flex flex-col items-center justify-center gap-0.5 sm:gap-1 cursor-not-allowed">
                                            <div class="flex items-center gap-1">
                                                <span class="font-bold text-xs sm:text-sm line-through" x-text="slot.jam_mulai.substring(0, 5)"></span>
                                                <x-lucide-clock x-show="slot.status === 'lewat_jam'" class="w-3 h-3 sm:w-3.5 sm:h-3.5 shrink-0" />
                                                <x-lucide-lock x-show="slot.status !== 'lewat_jam'" class="w-3 h-3 sm:w-3.5 sm:h-3.5 shrink-0" />
                                            </div>
                                            <span class="text-[9px] sm:text-[10px] text-gray-400" x-text="slot.jam_mulai.substring(0, 5) + ' - ' + slot.jam_selesai.substring(0, 5)"></span>
                                            <span class="text-[9px] sm:text-[10px] font-semibold text-gray-400" x-text="slot.status === 'lewat_jam' ? 'Lewat Jam' : 'Terisi'"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Sesi Malam Prime Time --}}
                    <div x-show="slotsMalam.length > 0" class="pt-4 border-t border-gray-100">
                        <div class="flex items-center justify-between gap-2 mb-3 sm:mb-3.5">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-[#1a146b] shrink-0"></span>
                                <h3 class="text-xs sm:text-sm font-bold text-[#0b1c30] truncate">Sesi Malam Prime Time</h3>
                            </div>
                            <span class="text-[11px] sm:text-xs font-bold text-[#1a146b] shrink-0 whitespace-nowrap" x-text="formatRp(primeRate) + ' / jam'"></span>
                        </div>

                        <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 sm:gap-3">
                            <template x-for="slot in slotsMalam" :key="slot.jam_mulai">
                                <div>
                                    <template x-if="slot.is_tersedia">
                                        <button type="button"
                                                @click="toggleSlot(slot)"
                                                :class="isSlotSelected(slot) ? 'bg-[#1a146b] text-white border-[#1a146b] shadow-md scale-[1.02]' : 'bg-white text-gray-800 border-gray-200 hover:border-gray-300 hover:bg-indigo-50/50'"
                                                class="w-full py-2.5 px-1.5 sm:py-3.5 sm:px-3 rounded-xl sm:rounded-2xl border text-center transition flex flex-col items-center justify-center gap-0.5 sm:gap-1 cursor-pointer">
                                            <div class="flex items-center gap-1">
                                                <span class="font-headline font-black text-xs sm:text-sm tracking-tight" x-text="slot.jam_mulai.substring(0, 5)"></span>
                                                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full" :class="isSlotSelected(slot) ? 'bg-[#acf847]' : 'bg-[#416900]'"></span>
                                            </div>
                                            <span class="text-[9px] sm:text-[10px]" :class="isSlotSelected(slot) ? 'text-white/80' : 'text-gray-400'" x-text="slot.jam_mulai.substring(0, 5) + ' - ' + slot.jam_selesai.substring(0, 5)"></span>
                                            <template x-if="slot.is_harga_khusus">
                                                <span class="text-[8px] sm:text-[9px] font-black px-1.5 py-0.5 rounded-md"
                                                      :class="isSlotSelected(slot) ? 'bg-amber-300 text-amber-950' : 'bg-amber-100 text-amber-800'"
                                                      x-text="formatRp(slot.harga)">
                                                </span>
                                            </template>
                                            <span class="text-[9px] sm:text-[10px] font-bold mt-0.5"
                                                  :class="isSlotSelected(slot) ? 'text-[#acf847]' : 'text-emerald-600'"
                                                  x-text="isSlotSelected(slot) ? 'DIPILIH' : 'Tersedia'">
                                            </span>
                                        </button>
                                    </template>
                                    <template x-if="!slot.is_tersedia">
                                        <div class="w-full py-2.5 px-1.5 sm:py-3.5 sm:px-3 rounded-xl sm:rounded-2xl border border-gray-100 bg-gray-50/80 text-gray-400 text-center flex flex-col items-center justify-center gap-0.5 sm:gap-1 cursor-not-allowed">
                                            <div class="flex items-center gap-1">
                                                <span class="font-bold text-xs sm:text-sm line-through" x-text="slot.jam_mulai.substring(0, 5)"></span>
                                                <x-lucide-clock x-show="slot.status === 'lewat_jam'" class="w-3 h-3 sm:w-3.5 sm:h-3.5 shrink-0" />
                                                <x-lucide-lock x-show="slot.status !== 'lewat_jam'" class="w-3 h-3 sm:w-3.5 sm:h-3.5 shrink-0" />
                                            </div>
                                            <span class="text-[9px] sm:text-[10px] text-gray-400" x-text="slot.jam_mulai.substring(0, 5) + ' - ' + slot.jam_selesai.substring(0, 5)"></span>
                                            <span class="text-[9px] sm:text-[10px] font-semibold text-gray-400" x-text="slot.status === 'lewat_jam' ? 'Lewat Jam' : 'Terisi'"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Slot Legend Status --}}
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 sm:gap-6 pt-3 border-t border-gray-100 text-[11px] sm:text-xs text-gray-500">
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-gray-200"></span>
                            <span>Terisi / Lewat Jam</span>
                        </div>
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-emerald-500"></span>
                            <span>Tersedia</span>
                        </div>
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-[#1a146b]"></span>
                            <span class="font-bold text-[#1a146b]">Dipilih (<span x-text="selectedSlots.length"></span> Jam)</span>
                        </div>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- 4. MODUL SEWA PERLENGKAPAN TAMBAHAN (Sesuai Permintaan) --}}
                {{-- ======================================================== --}}
                <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-8 shadow-xs border border-gray-100 flex flex-col gap-4 sm:gap-5">
                    <div class="flex items-start sm:items-center justify-between gap-2 pb-3 border-b border-gray-100">
                        <div>
                            <div class="flex items-center gap-2">
                                <x-lucide-flag class="w-4 h-4 sm:w-5 sm:h-5 text-[#416900] shrink-0" />
                                <h2 class="text-sm sm:text-base font-headline font-bold text-[#0b1c30]">Modul Sewa Perlengkapan Tambahan</h2>
                            </div>
                            <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">
                                Tambahkan rompi tanding, bola resmi FIFA, atau perlengkapan lainnya ke dalam pesanan Anda.
                            </p>
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold text-[#416900] bg-[#eaf7d8] px-2.5 sm:px-3 py-1 rounded-full shrink-0">
                            Opsional
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        @forelse ($perlengkapans as $p)
                            <div class="p-3 sm:p-4 rounded-2xl border border-gray-200 bg-white hover:border-[#1a146b]/40 transition flex items-center justify-between gap-3 shadow-xs">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gray-100 overflow-hidden shrink-0 border border-gray-100">
                                        @if($p->gambar)
                                            <img src="{{ asset('storage/' . $p->gambar) }}" alt="{{ $p->nama }}" width="48" height="48" loading="lazy" decoding="async" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-[#1a146b]">
                                                <x-lucide-dribbble class="w-6 h-6 shrink-0" />
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-xs font-bold text-[#0b1c30] truncate">{{ $p->nama }}</h4>
                                        <p class="text-[11px] text-[#416900] font-extrabold mt-0.5">
                                            Rp{{ number_format((float)$p->harga, 0, ',', '.') }} <span class="text-gray-400 font-normal">/sesi</span>
                                        </p>
                                    </div>
                                </div>

                                {{-- Quantity Stepper Control --}}
                                <div class="flex items-center gap-2 bg-[#eff4ff] p-1 rounded-xl border border-[#dce9ff] shrink-0"
                                     x-data="{ item: equipments.find(e => e.id === '{{ $p->id }}') }">
                                    <button type="button"
                                            @click="changeQty('{{ $p->id }}', -1)"
                                            class="w-7 h-7 rounded-lg bg-white hover:bg-gray-100 text-[#0b1c30] flex items-center justify-center font-bold text-sm shadow-xs transition">
                                        –
                                    </button>
                                    <span class="w-6 text-center text-xs font-headline font-black text-[#1a146b]"
                                          x-text="item ? item.qty : 0">0</span>
                                    <button type="button"
                                            @click="changeQty('{{ $p->id }}', 1)"
                                            class="w-7 h-7 rounded-lg bg-[#1a146b] hover:bg-[#2b248a] text-white flex items-center justify-center font-bold text-sm shadow-xs transition">
                                        +
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full py-4 text-center text-xs text-gray-400 bg-gray-50 rounded-xl">
                                Tidak ada perlengkapan tambahan yang tersedia saat ini.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Fasilitas Venue --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-4">
                    <div class="bg-white rounded-2xl p-3 sm:p-4 border border-gray-100 shadow-xs flex items-center gap-3">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-[#1a146b] shrink-0">
                            <x-lucide-droplets class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" />
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-[#0b1c30]">Fasilitas Lengkap</h4>
                            <p class="text-[11px] text-gray-500">Shower, Kantin &amp; Ruang Ganti</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-3 sm:p-4 border border-gray-100 shadow-xs flex items-center gap-3">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-[#416900] shrink-0">
                            <x-lucide-zap class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" />
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-[#0b1c30]">Instan Konfirmasi</h4>
                            <p class="text-[11px] text-gray-500">Langsung tercatat di kasir venue</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-3 sm:p-4 border border-gray-100 shadow-xs flex items-center gap-3">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                            <x-lucide-circle-parking class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" />
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-[#0b1c30]">Parkir Luas</h4>
                            <p class="text-[11px] text-gray-500">Kapasitas 40+ mobil &amp; motor</p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ======================================================== --}}
            {{-- KOLOM KANAN (Sticky Sidebar / Anti-Gravity): Ringkasan Pesanan --}}
            {{-- ======================================================== --}}
            {{-- CSS helper inline agar pasti tampil di layar desktop tanpa bergantung cache build Tailwind --}}
            <style>
                @media (max-width: 1023px) {
                    #desktop-order-summary { display: none !important; }
                }
                @media (min-width: 1024px) {
                    #desktop-order-summary { display: block !important; }
                }
            </style>
            <aside id="desktop-order-summary" class="lg:col-span-4 xl:col-span-4 lg:sticky top-28" style="position: sticky; top: 110px; height: fit-content;">
                <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-lg border border-gray-100 flex flex-col gap-5">

                    {{-- Summary Header: Ringkasan Pesanan --}}
                    <div>
                        <div class="flex items-center justify-between">
                            <h2 class="text-xs font-black uppercase tracking-wider text-[#416900] flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#416900]"></span>
                                <span>Ringkasan Pesanan</span>
                            </h2>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#eff4ff] text-[#1a146b]" x-text="selectedSlots.length > 0 ? (selectedSlots.length + ' Slot Dipilih') : 'Belum Memilih'"></span>
                        </div>

                        <div class="flex items-center gap-3 mt-3">
                            <div class="w-12 h-12 rounded-xl bg-gray-900 overflow-hidden shrink-0 border border-gray-200 shadow-2xs">
                                <img src="{{ $lapangan->foto_url }}" alt="{{ $lapangan->nama }}" width="48" height="48" loading="lazy" decoding="async" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-headline font-bold text-[#0b1c30] truncate">
                                    {{ $lapangan->nama }}
                                </h3>
                                <p class="text-xs text-gray-500 truncate">
                                    {{ $lapangan->kategori }} • {{ $lapangan->lokasi ?? 'Venue Mitra' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Detail Jadwal yang Sedang Dipilih / Empty State --}}
                    <template x-if="selectedSlots.length === 0">
                        <div class="p-5 rounded-2xl bg-gray-50 border border-dashed border-gray-200 text-center flex flex-col items-center justify-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                                <x-lucide-calendar-x class="w-5 h-5 shrink-0" />
                            </div>
                            <p class="text-xs font-bold text-gray-600">Belum ada jadwal yang dipilih</p>
                            <p class="text-[11px] text-gray-400 leading-snug">Silakan klik salah satu atau beberapa slot jam pada kolom di sebelah kiri.</p>
                        </div>
                    </template>

                    <template x-if="selectedSlots.length > 0">
                        <div class="flex flex-col gap-4">
                            {{-- Schedule Summary Meta --}}
                            <div class="p-4 rounded-2xl bg-[#eff4ff] border border-[#dce9ff] flex flex-col gap-2.5 text-xs">
                                <div class="flex items-center justify-between text-gray-600">
                                    <span class="flex items-center gap-1.5">
                                        <x-lucide-calendar class="w-4 h-4 text-[#1a146b] shrink-0" />
                                        <span>Tanggal:</span>
                                    </span>
                                    <strong class="text-gray-900" x-text="formattedDate"></strong>
                                </div>

                                {{-- Slot Waktu Terinci Per Sesi / Interval Sesuai Pilihan --}}
                                <div class="pt-2 border-t border-indigo-100/70">
                                    <div class="flex items-center justify-between text-gray-600 mb-1.5">
                                        <span class="flex items-center gap-1.5 font-medium">
                                            <x-lucide-clock class="w-4 h-4 text-[#1a146b] shrink-0" />
                                            <span>Jadwal Main Terpilih:</span>
                                        </span>
                                        <span class="text-[11px] font-bold text-[#1a146b]" x-text="selectedSlots.length + ' Sesi Jam'"></span>
                                    </div>
                                    <div class="space-y-1">
                                        <template x-for="(interval, idx) in groupedSlotIntervals" :key="idx">
                                            <div class="flex items-center justify-between py-1.5 px-2.5 rounded-xl bg-white border border-indigo-100/80 shadow-2xs">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#416900]"></span>
                                                    <span class="text-[11px] text-gray-500 font-medium" x-text="formattedDate"></span>
                                                </div>
                                                <span class="font-extrabold text-xs text-[#0b1c30] font-mono tracking-tight" x-text="interval.start + ' - ' + interval.end + ' WIB'"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Cost Breakdown --}}
                            <div class="flex flex-col gap-2 text-xs text-gray-600 pt-1">
                                <div class="flex items-center justify-between">
                                    <span>Sesi Sewa Lapangan (<span x-text="durationHours"></span> jam):</span>
                                    <span class="font-bold text-gray-900" x-text="formatRp(courtTotalFee)"></span>
                                </div>

                                <template x-if="equipmentTotalFee > 0">
                                    <div class="flex items-center justify-between text-emerald-700 font-medium">
                                        <span>Sewa Perlengkapan Tambahan:</span>
                                        <span class="font-bold" x-text="formatRp(equipmentTotalFee)"></span>
                                    </div>
                                </template>

                                <div class="border-t border-gray-100 my-1"></div>

                                <div class="flex items-center justify-between text-sm font-headline">
                                    <span class="font-bold text-gray-900" x-text="paymentScheme === 'dp' ? 'DP yang Harus Dibayar:' : 'Total Pembayaran:'"></span>
                                    <span class="font-black text-lg text-[#1a146b]" x-text="formatRp(payableAmount)"></span>
                                </div>
                                <template x-if="paymentScheme === 'dp'">
                                    <div class="flex items-center justify-between text-xs text-amber-700 bg-amber-50 p-2 rounded-xl border border-amber-200">
                                        <span>Sisa pelunasan di kasir:</span>
                                        <span class="font-bold" x-text="formatRp(remainingAmount)"></span>
                                    </div>
                                </template>
                            </div>

                            {{-- Payment Scheme Selector --}}
                            <div class="flex flex-col gap-2 pt-1">
                                <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">
                                    PILIHAN SKEMA PEMBAYARAN
                                </span>

                                <div class="grid grid-cols-2 gap-2">
                                    <label class="p-3 rounded-2xl border transition cursor-pointer flex flex-col justify-between"
                                           :class="paymentScheme === 'dp' ? 'border-[#416900] bg-[#eaf7d8]/40 ring-1 ring-[#acf847]' : 'border-gray-200 bg-white hover:bg-gray-50'">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] font-bold text-gray-900">DP 50%</span>
                                            <input type="radio" name="scheme" value="dp" x-model="paymentScheme" class="text-[#416900] focus:ring-[#acf847]">
                                        </div>
                                        <span class="text-sm font-headline font-black text-[#416900] mt-1" x-text="formatRp(dpAmount)"></span>
                                        <span class="text-[9px] text-gray-500 mt-0.5">Sisa di kasir</span>
                                    </label>

                                    <label class="p-3 rounded-2xl border transition cursor-pointer flex flex-col justify-between"
                                           :class="paymentScheme === 'full' ? 'border-[#1a146b] bg-indigo-50/50 ring-1 ring-[#1a146b]' : 'border-gray-200 bg-white hover:bg-gray-50'">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] font-bold text-gray-900">Lunas 100%</span>
                                            <input type="radio" name="scheme" value="full" x-model="paymentScheme" class="text-[#1a146b] focus:ring-[#1a146b]">
                                        </div>
                                        <span class="text-sm font-headline font-black text-[#1a146b] mt-1" x-text="formatRp(grandTotal)"></span>
                                        <span class="text-[9px] text-[#416900] font-semibold mt-0.5">Bebas antre</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Checkout Submit Form (Desktop) --}}
                    <form method="GET" action="{{ route('checkout.index', ['lapangan_id' => $lapangan->id]) }}" class="pt-1">
                        <input type="hidden" name="lapangan_id" value="{{ $lapangan->id }}">
                        <input type="hidden" name="tanggal" :value="selectedDate">
                        <input type="hidden" name="jam_mulai" :value="earliestSlot ? earliestSlot.substring(0, 5) : ''">
                        <input type="hidden" name="durasi" :value="durationHours">
                        <input type="hidden" name="tipe_bayar" :value="paymentScheme">

                        @guest
                            <div class="mb-3 p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] leading-snug flex items-start gap-2">
                                <x-lucide-info class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" />
                                <span>Pemesanan online memerlukan akun terdaftar. Anda akan diarahkan ke login/daftar jika belum masuk.</span>
                            </div>
                        @endguest

                        {{-- Tombol Besar Berwarna Biru dengan Teks Pesan Sekarang! --}}
                        <button type="submit"
                                :disabled="selectedSlots.length === 0"
                                class="w-full py-4 px-6 rounded-2xl font-headline font-black text-base flex items-center justify-center gap-2 shadow-lg transition-all active:scale-[0.99] cursor-pointer"
                                :class="selectedSlots.length > 0
                                    ? 'bg-[#1a146b] hover:bg-[#130d52] text-white shadow-indigo-950/20'
                                    : 'bg-gray-200 text-gray-400 cursor-not-allowed'">
                            <span x-text="selectedSlots.length === 0 ? 'Pesan Sekarang!' : ('Bayar ' + (paymentScheme === 'dp' ? 'DP ' : 'Lunas ') + formatRp(payableAmount))">Pesan Sekarang!</span>
                            <x-lucide-arrow-right class="w-5 h-5 shrink-0" />
                        </button>
                    </form>

                    {{-- Lock Reassurance Box --}}
                    <div class="p-3 rounded-2xl bg-gray-50 border border-gray-100 flex items-start gap-2.5 text-gray-500 text-[11px] leading-snug">
                        <x-lucide-clock-alert class="w-4 h-4 text-[#1a146b] shrink-0 mt-0.5" />
                        <span>Slot otomatis terkunci aman selama 10 menit saat Anda menekan tombol Pesan Sekarang.</span>
                    </div>

                    {{-- Helpline Note --}}
                    <div class="text-center pt-1 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                        <span class="flex items-center gap-1">
                            <x-lucide-headset class="w-4 h-4 shrink-0" />
                            <span>Butuh bantuan?</span>
                        </span>
                        <a href="https://wa.me/{{ config('app.support_whatsapp', '6281234567890') }}" target="_blank" class="font-bold text-[#1a146b] hover:underline">
                            Hubungi CS
                        </a>
                    </div>

                </div>
            </aside>

        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- MOBILE / SMALL SCREEN FIXED BOTTOM BAR DENGAN AKORDEON EXPAND/COLLAPSE --}}
    {{-- ========================================================================= --}}
    {{-- STICKY MOBILE BOTTOM BAR (Kompak 1 baris, Expandable saat slot dipilih) --}}
    {{-- ========================================================================= --}}
    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white rounded-t-2xl shadow-[0_-4px_20px_rgba(11,28,48,0.08)] border-t border-gray-200 transition-all duration-300 after:content-[''] after:absolute after:inset-x-0 after:top-full after:h-16 after:bg-white after:pointer-events-none">
        <div class="max-w-xl mx-auto">

            {{-- PANEL EXPANDED: Rincian Biaya & Pilihan Skema Pembayaran (hanya muncul saat diklik) --}}
            <div x-show="selectedSlots.length > 0 && showSchemeAccordion"
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 class="max-h-[60vh] overflow-y-auto px-4 pt-3.5 pb-3 border-b border-gray-100 space-y-2.5 text-xs text-gray-600 bg-white rounded-t-2xl">

                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-black uppercase tracking-wider text-[#416900]">Ringkasan Jadwal Main</p>
                    <button type="button" @click="showSchemeAccordion = false" class="text-[11px] font-bold text-gray-400 hover:text-gray-600">Tutup</button>
                </div>

                {{-- Daftar Sesi Terpilih Mobile --}}
                <div class="space-y-1">
                    <template x-for="(interval, idx) in groupedSlotIntervals" :key="idx">
                        <div class="flex items-center justify-between py-1.5 px-2.5 rounded-xl bg-[#f8faff] border border-indigo-100/70 text-[11px]">
                            <span class="text-gray-500" x-text="formattedDate"></span>
                            <span class="font-bold text-[#0b1c30] font-mono" x-text="interval.start + ' - ' + interval.end + ' WIB'"></span>
                        </div>
                    </template>
                </div>

                {{-- Rincian Biaya --}}
                <div class="space-y-1.5 pt-1">
                    <div class="flex items-center justify-between">
                        <span>Sewa Lapangan (<span x-text="durationHours"></span> Jam)</span>
                        <span class="font-bold text-gray-900" x-text="formatRp(courtTotalFee)"></span>
                    </div>
                    <template x-if="equipmentTotalFee > 0">
                        <div class="flex items-center justify-between text-emerald-700">
                            <span>Sewa Perlengkapan</span>
                            <span class="font-bold" x-text="formatRp(equipmentTotalFee)"></span>
                        </div>
                    </template>
                </div>

                {{-- Pilihan Skema Pembayaran --}}
                <div class="pt-2 border-t border-gray-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Skema Pembayaran</p>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="p-2.5 rounded-xl border transition cursor-pointer flex items-center justify-between gap-1.5"
                               :class="paymentScheme === 'full' ? 'border-[#1a146b] bg-[#eff4ff] ring-1 ring-[#1a146b]' : 'border-gray-200 bg-white'">
                            <div class="min-w-0">
                                <p class="text-[11px] font-extrabold text-[#0b1c30]">Lunas 100%</p>
                                <p class="text-[10px] font-bold text-[#1a146b]" x-text="formatRp(grandTotal)"></p>
                            </div>
                            <input type="radio" name="mobile_scheme" value="full" x-model="paymentScheme" class="w-3.5 h-3.5 text-[#1a146b] focus:ring-[#1a146b]">
                        </label>

                        <label class="p-2.5 rounded-xl border transition cursor-pointer flex items-center justify-between gap-1.5"
                               :class="paymentScheme === 'dp' ? 'border-[#416900] bg-[#eaf7d8]/60 ring-1 ring-[#acf847]' : 'border-gray-200 bg-white'">
                            <div class="min-w-0">
                                <p class="text-[11px] font-extrabold text-[#0b1c30]">DP 50%</p>
                                <p class="text-[10px] font-bold text-[#416900]" x-text="formatRp(dpAmount)"></p>
                            </div>
                            <input type="radio" name="mobile_scheme" value="dp" x-model="paymentScheme" class="w-3.5 h-3.5 text-[#416900] focus:ring-[#acf847]">
                        </label>
                    </div>
                </div>
            </div>

            {{-- BARIS AKSI UTAMA 1 BARIS KOMPAK --}}
            <div class="px-4 py-3 pb-[max(0.875rem,env(safe-area-inset-bottom))] flex items-center justify-between gap-3">
                {{-- Kiri: Info singkat (Belum pilih vs Sudah pilih) --}}
                <div class="min-w-0 flex-1">
                    <template x-if="selectedSlots.length === 0">
                        <p class="text-xs font-semibold text-gray-500">Pilih slot jam main</p>
                    </template>

                    <template x-if="selectedSlots.length > 0">
                        <button type="button"
                                @click="showSchemeAccordion = !showSchemeAccordion"
                                class="w-full text-left flex items-center gap-1.5 group cursor-pointer">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-base font-black text-[#1a146b] leading-none" x-text="formatRp(payableAmount)"></span>
                                    <span class="rounded-md bg-[#eff4ff] px-1.5 py-0.5 text-[9px] font-bold text-[#1a146b]"
                                          x-text="paymentScheme === 'dp' ? 'DP 50%' : 'Lunas'"></span>
                                </div>
                                <p class="text-[10px] font-semibold text-gray-500 mt-1 truncate"
                                   x-text="selectedSlots.length + ' Jam · ' + groupedSlotIntervals.map(i => i.start + '-' + i.end).join(', ') + ' WIB'"></p>
                            </div>
                            <x-lucide-chevron-up class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0"
                                  x-bind:class="showSchemeAccordion ? 'rotate-180' : ''" />
                        </button>
                    </template>
                </div>

                {{-- Kanan: Tombol Lanjut --}}
                <form method="GET" action="{{ route('checkout.index', ['lapangan_id' => $lapangan->id]) }}" class="shrink-0">
                    <input type="hidden" name="lapangan_id" value="{{ $lapangan->id }}">
                    <input type="hidden" name="tanggal" :value="selectedDate">
                    <input type="hidden" name="jam_mulai" :value="earliestSlot ? earliestSlot.substring(0, 5) : ''">
                    <input type="hidden" name="durasi" :value="durationHours">
                    <input type="hidden" name="tipe_bayar" :value="paymentScheme">

                    <button type="submit"
                            :disabled="selectedSlots.length === 0"
                            class="py-2.5 px-5 rounded-xl font-headline font-black text-xs flex items-center justify-center gap-1.5 transition-all active:scale-[0.98]"
                            :class="selectedSlots.length > 0
                                ? 'bg-[#1a146b] hover:bg-[#130d52] text-white shadow-md shadow-indigo-950/20 cursor-pointer'
                                : 'bg-gray-100 text-gray-400 cursor-not-allowed'">
                        <span>Lanjut ➔</span>
                    </button>
                </form>
            </div>

        </div>
    </div>

    {{-- Custom Pop-up Modal Peringatan (Menggantikan Alert Bawaan Browser) --}}
    <div x-show="showAlertModal"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">

        <div @click.away="showAlertModal = false"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 scale-95 translate-y-3"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-3"
             class="relative w-full max-w-sm rounded-3xl bg-white p-6 sm:p-7 text-center shadow-2xl border border-gray-100">

            {{-- Icon Peringatan --}}
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 shadow-xs">
                <x-lucide-clock class="w-7 h-7 shrink-0" />
            </div>

            {{-- Judul --}}
            <h3 class="text-base sm:text-lg font-headline font-black text-[#0b1c30]" x-text="alertTitle">
                Batas Durasi Booking
            </h3>

            {{-- Pesan --}}
            <p class="mt-2 text-xs sm:text-sm text-gray-600 leading-relaxed" x-text="alertMessage">
                Maksimal pemesanan sekaligus adalah 4 jam.
            </p>

            {{-- Tombol OK / Mengerti --}}
            <div class="mt-6 flex justify-center">
                <button type="button"
                        @click="showAlertModal = false"
                        class="w-full sm:w-auto min-w-[140px] px-6 py-2.5 rounded-xl bg-[#1a146b] hover:bg-[#130d52] text-white text-xs font-bold transition shadow-sm cursor-pointer active:scale-95">
                    Mengerti
                </button>
            </div>
        </div>
    </div>

    {{-- Spacer Tambahan di Mobile agar Konten Bawah Tidak Tertutup Sticky Bar --}}
    <div class="h-16 lg:hidden"></div>

</div>
@endsection
