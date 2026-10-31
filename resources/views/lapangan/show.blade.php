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

    // Tentukan estimasi harga dasar
    $isWeekend = in_array($selectedDateCarbon->dayOfWeek, [0, 6]);
    $baseRate = (float) ($isWeekend ? ($hargaWeekend ?? 175000) : ($hargaWeekday ?? 125000));
    $primeRate = (float) ($baseRate + 40000);
@endphp

<div class="min-h-screen bg-[#f8f9ff] text-[#0b1c30] pb-16"
     x-data="{
         selectedSlots: [],
         selectedDate: '{{ $tanggalDipilih }}',
         formattedDate: '{{ $selectedDateCarbon->translatedFormat('l, d F Y') }}',
         monthYear: '{{ $selectedDateCarbon->translatedFormat('F Y') }}',
         paymentScheme: 'dp',
         equipments: {{ Js::from($perlengkapans->map(fn($p) => ['id' => $p->id, 'nama' => $p->nama, 'harga' => (float)$p->harga, 'qty' => 0])) }},
         baseRate: {{ $baseRate }},
         primeRate: {{ $primeRate }},
         datesCarousel: {{ Js::from($datesCarousel) }},
         slotsSiang: {{ Js::from($slotsSiang) }},
         slotsMalam: {{ Js::from($slotsMalam) }},
         isLoadingSlots: false,
         slotsCache: {
             '{{ $tanggalDipilih }}': {
                 slotsSiang: {{ Js::from($slotsSiang) }},
                 slotsMalam: {{ Js::from($slotsMalam) }},
                 datesCarousel: {{ Js::from($datesCarousel) }},
                 formattedDate: '{{ $selectedDateCarbon->translatedFormat('l, d F Y') }}',
                 monthYear: '{{ $selectedDateCarbon->translatedFormat('F Y') }}',
                 baseRate: {{ $baseRate }},
                 primeRate: {{ $primeRate }}
             }
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
                 this.datesCarousel = cached.datesCarousel;
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
                     alert('Maksimal pemesanan sekaligus adalah 4 jam.');
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

         get courtTotalFee() {
             if (this.selectedSlots.length === 0) {
                 return 0;
             }
             let sum = 0;
             this.selectedSlots.forEach(slot => {
                 const hour = parseInt(slot.jam_mulai.substring(0, 2), 10);
                 sum += (hour >= 18 ? this.primeRate : this.baseRate);
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">

        {{-- 1. Breadcrumbs --}}
        <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6 font-medium">
            <a href="{{ route('home') }}" class="hover:text-[#1a146b] flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">home</span>
                <span>Beranda</span>
            </a>
            <span>/</span>
            <a href="{{ route('katalog.index', ['kategori' => $lapangan->kategori]) }}" class="hover:text-[#1a146b]">
                {{ $lapangan->kategori }}
            </a>
            <span>/</span>
            <span class="text-gray-900 font-bold truncate">{{ $lapangan->nama }}</span>
        </nav>

        {{-- 2. Main Two-Column Split Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- ======================================================== --}}
            {{-- LEFT COLUMN: Arena Details, Date Carousel, Slots & Addons --}}
            {{-- ======================================================== --}}
            <div class="lg:col-span-8 flex flex-col gap-8">

                {{-- Venue Header Card --}}
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-gray-100 flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#eaf7d8] text-[#416900] text-[10px] font-black uppercase tracking-wider">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#416900]"></span>
                                    TERVERIFIKASI
                                </span>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-gray-500">
                                    <span class="material-symbols-outlined text-sm text-[#416900]">verified</span>
                                    <span>Standard AFC Flooring</span>
                                </span>
                            </div>

                            <h1 class="text-2xl sm:text-3xl font-headline font-black text-[#0b1c30] tracking-tight">
                                {{ $lapangan->nama }}
                            </h1>

                            <p class="text-xs text-gray-500 mt-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm text-gray-400">location_on</span>
                                <span>{{ $lapangan->deskripsi ?: 'Arena futsal, badminton, dan multisport resmi dengan standar pencahayaan turnamen.' }}</span>
                            </p>

                            {{-- Tarif Resmi Terverifikasi --}}
                            <div class="mt-3.5 flex flex-wrap items-center gap-3 text-xs">
                                <div class="px-3 py-1.5 rounded-xl bg-gray-50 border border-gray-200">
                                    <span class="text-gray-500">Tarif Weekday:</span>
                                    <strong class="text-[#1a146b] ml-1">Rp {{ number_format($hargaWeekday ?? 125000, 0, ',', '.') }}</strong>
                                </div>
                                <div class="px-3 py-1.5 rounded-xl bg-[#eaf7d8] border border-[#acf847]/60">
                                    <span class="text-[#416900]">Tarif Weekend:</span>
                                    <strong class="text-[#102000] ml-1">Rp {{ number_format($hargaWeekend ?? 175000, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Kembali ke Katalog (Area Atas Bersih) --}}
                        <div class="shrink-0 self-start sm:self-center">
                            <a href="{{ route('katalog.index') }}"
                               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-bold border border-gray-200 transition shadow-2xs">
                                <span class="material-symbols-outlined text-base text-gray-500">arrow_back</span>
                                <span>Kembali ke Katalog</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Pilih Tanggal Main (7-Day Carousel) --}}
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-gray-100 flex flex-col gap-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-xl text-[#1a146b]">calendar_month</span>
                            <h2 class="text-base font-headline font-bold text-[#0b1c30]">Pilih Tanggal Main</h2>
                        </div>
                        <span class="text-xs font-semibold text-gray-400" x-text="monthYear">
                            {{ $selectedDateCarbon->translatedFormat('F Y') }}
                        </span>
                    </div>

                    {{-- Horizontal 7-Day Date Buttons (SPA Reactive tanpa reload) --}}
                    <div class="grid grid-cols-4 sm:grid-cols-7 gap-2.5">
                        <template x-for="d in datesCarousel" :key="d.date_str">
                            <button type="button"
                                    @click="changeDate(d.date_str)"
                                    class="flex flex-col items-center justify-center py-3.5 px-2 rounded-2xl transition border cursor-pointer"
                                    :class="selectedDate === d.date_str ? 'bg-[#1a146b] border-[#1a146b] text-white shadow-md' : 'bg-white border-gray-200 hover:border-gray-300 text-gray-700 hover:bg-gray-50'">
                                <span class="text-[10px] font-black tracking-wider uppercase"
                                      :class="selectedDate === d.date_str ? 'text-[#acf847]' : 'text-gray-400'"
                                      x-text="d.label_top">
                                </span>
                                <span class="text-xl font-headline font-black my-1" x-text="d.day_num"></span>
                                <div class="flex items-center gap-1 text-[11px] font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                          :class="selectedDate === d.date_str ? 'bg-[#acf847]' : 'bg-[#416900]'"></span>
                                    <span x-text="d.day_name"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Grouping Sesi Jam Booking --}}
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-gray-100 flex flex-col gap-6 relative">
                    {{-- Loading Bar Tipis Halus saat ganti tanggal --}}
                    <div x-show="isLoadingSlots"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="absolute inset-x-0 top-0 h-1 bg-[#acf847] overflow-hidden rounded-t-3xl z-20">
                        <div class="w-full h-full bg-[#1a146b] animate-pulse"></div>
                    </div>

                    {{-- Sesi Siang & Sore --}}
                    <div x-show="slotsSiang.length > 0">
                        <div class="flex items-center justify-between mb-3.5">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#416900]"></span>
                                <h3 class="text-sm font-bold text-[#0b1c30]">Sesi Siang &amp; Sore</h3>
                                <span class="text-xs text-gray-400 font-mono">13:00 – 17:00</span>
                            </div>
                            <span class="text-xs font-bold text-gray-600" x-text="formatRp(baseRate) + ' / jam'"></span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <template x-for="slot in slotsSiang" :key="slot.jam_mulai">
                                <div>
                                    <template x-if="slot.is_tersedia">
                                        <button type="button"
                                                @click="toggleSlot(slot)"
                                                :class="isSlotSelected(slot) ? 'bg-[#1a146b] text-white border-[#1a146b] shadow-md scale-[1.02]' : 'bg-white text-gray-800 border-gray-200 hover:border-gray-300 hover:bg-indigo-50/50'"
                                                class="w-full py-3.5 px-3 rounded-2xl border text-center transition flex flex-col items-center justify-center gap-1 cursor-pointer">
                                            <div class="flex items-center gap-1">
                                                <span class="font-headline font-black text-sm tracking-tight" x-text="slot.jam_mulai.substring(0, 5)"></span>
                                                <span class="w-2 h-2 rounded-full" :class="isSlotSelected(slot) ? 'bg-[#acf847]' : 'bg-[#416900]'"></span>
                                            </div>
                                            <span class="text-[10px]" :class="isSlotSelected(slot) ? 'text-white/80' : 'text-gray-400'" x-text="slot.jam_mulai.substring(0, 5) + ' - ' + slot.jam_selesai.substring(0, 5)"></span>
                                            <span class="text-[10px] font-bold mt-0.5"
                                                  :class="isSlotSelected(slot) ? 'text-[#acf847]' : 'text-emerald-600'"
                                                  x-text="isSlotSelected(slot) ? 'DIPILIH' : 'Tersedia'">
                                            </span>
                                        </button>
                                    </template>
                                    <template x-if="!slot.is_tersedia">
                                        <div class="w-full py-3.5 px-3 rounded-2xl border border-gray-100 bg-gray-50/80 text-gray-400 text-center flex flex-col items-center justify-center gap-1 cursor-not-allowed">
                                            <div class="flex items-center gap-1">
                                                <span class="font-bold text-sm line-through" x-text="slot.jam_mulai.substring(0, 5)"></span>
                                                <span class="material-symbols-outlined text-xs" x-text="slot.status === 'lewat_jam' ? 'schedule' : 'lock'"></span>
                                            </div>
                                            <span class="text-[10px] text-gray-400" x-text="slot.jam_mulai.substring(0, 5) + ' - ' + slot.jam_selesai.substring(0, 5)"></span>
                                            <span class="text-[10px] font-semibold text-gray-400" x-text="slot.status === 'lewat_jam' ? 'Lewat Jam' : 'Terisi'"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Sesi Malam Prime Time --}}
                    <div x-show="slotsMalam.length > 0" class="pt-4 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-3.5">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#1a146b]"></span>
                                <h3 class="text-sm font-bold text-[#0b1c30]">Sesi Malam Prime Time</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#eaf7d8] text-[#416900] text-[10px] font-black uppercase tracking-wider">
                                    ⚡ PALING FAVORIT
                                </span>
                            </div>
                            <span class="text-xs font-bold text-[#1a146b]" x-text="formatRp(primeRate) + ' / jam'"></span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                            <template x-for="slot in slotsMalam" :key="slot.jam_mulai">
                                <div>
                                    <template x-if="slot.is_tersedia">
                                        <button type="button"
                                                @click="toggleSlot(slot)"
                                                :class="isSlotSelected(slot) ? 'bg-[#1a146b] text-white border-[#1a146b] shadow-md scale-[1.02]' : 'bg-white text-gray-800 border-gray-200 hover:border-gray-300 hover:bg-indigo-50/50'"
                                                class="w-full py-3.5 px-3 rounded-2xl border text-center transition flex flex-col items-center justify-center gap-1 cursor-pointer">
                                            <div class="flex items-center gap-1">
                                                <span class="font-headline font-black text-sm tracking-tight" x-text="slot.jam_mulai.substring(0, 5)"></span>
                                                <span class="w-2 h-2 rounded-full" :class="isSlotSelected(slot) ? 'bg-[#acf847]' : 'bg-[#416900]'"></span>
                                            </div>
                                            <span class="text-[10px]" :class="isSlotSelected(slot) ? 'text-white/80' : 'text-gray-400'" x-text="slot.jam_mulai.substring(0, 5) + ' - ' + slot.jam_selesai.substring(0, 5)"></span>
                                            <span class="text-[10px] font-bold mt-0.5"
                                                  :class="isSlotSelected(slot) ? 'text-[#acf847]' : 'text-emerald-600'"
                                                  x-text="isSlotSelected(slot) ? 'DIPILIH' : 'Tersedia'">
                                            </span>
                                        </button>
                                    </template>
                                    <template x-if="!slot.is_tersedia">
                                        <div class="w-full py-3.5 px-3 rounded-2xl border border-gray-100 bg-gray-50/80 text-gray-400 text-center flex flex-col items-center justify-center gap-1 cursor-not-allowed">
                                            <div class="flex items-center gap-1">
                                                <span class="font-bold text-sm line-through" x-text="slot.jam_mulai.substring(0, 5)"></span>
                                                <span class="material-symbols-outlined text-xs" x-text="slot.status === 'lewat_jam' ? 'schedule' : 'lock'"></span>
                                            </div>
                                            <span class="text-[10px] text-gray-400" x-text="slot.jam_mulai.substring(0, 5) + ' - ' + slot.jam_selesai.substring(0, 5)"></span>
                                            <span class="text-[10px] font-semibold text-gray-400" x-text="slot.status === 'lewat_jam' ? 'Lewat Jam' : 'Terisi'"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Slot Legend Status --}}
                    <div class="flex flex-wrap items-center gap-6 pt-3 border-t border-gray-100 text-xs text-gray-500">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-gray-200"></span>
                            <span>Terisi / Lewat Jam</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span>Tersedia</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#1a146b]"></span>
                            <span class="font-bold text-[#1a146b]">Dipilih Anda (<span x-text="selectedSlots.length"></span> Jam)</span>
                        </div>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- 4. MODUL SEWA PERLENGKAPAN TAMBAHAN (Sesuai Permintaan) --}}
                {{-- ======================================================== --}}
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-gray-100 flex flex-col gap-5">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-xl text-[#416900]">sports_score</span>
                                <h2 class="text-base font-headline font-bold text-[#0b1c30]">Modul Sewa Perlengkapan Tambahan</h2>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Tambahkan rompi tanding, bola resmi FIFA, atau perlengkapan lainnya ke dalam pesanan Anda.
                            </p>
                        </div>
                        <span class="text-xs font-bold text-[#416900] bg-[#eaf7d8] px-3 py-1 rounded-full">
                            Opsional
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @forelse ($perlengkapans as $p)
                            <div class="p-4 rounded-2xl border border-gray-200 bg-white hover:border-[#1a146b]/40 transition flex items-center justify-between gap-3 shadow-xs">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-gray-100 overflow-hidden shrink-0 border border-gray-100">
                                        @if($p->gambar)
                                            <img src="{{ asset('storage/' . $p->gambar) }}" alt="{{ $p->nama }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-[#1a146b]">
                                                <span class="material-symbols-outlined text-2xl">sports_soccer</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-bold text-[#0b1c30]">{{ $p->nama }}</h4>
                                        <p class="text-[11px] text-[#416900] font-extrabold mt-0.5">
                                            Rp{{ number_format((float)$p->harga, 0, ',', '.') }} <span class="text-gray-400 font-normal">/sesi</span>
                                        </p>
                                    </div>
                                </div>

                                {{-- Quantity Stepper Control --}}
                                <div class="flex items-center gap-2 bg-[#eff4ff] p-1 rounded-xl border border-[#dce9ff]"
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
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-[#1a146b] shrink-0">
                            <span class="material-symbols-outlined text-xl">shower</span>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-[#0b1c30]">Fasilitas Lengkap</h4>
                            <p class="text-[11px] text-gray-500">Shower, Kantin &amp; Ruang Ganti</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-[#416900] shrink-0">
                            <span class="material-symbols-outlined text-xl">bolt</span>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-[#0b1c30]">Instan Konfirmasi</h4>
                            <p class="text-[11px] text-gray-500">Langsung tercatat di kasir venue</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                            <span class="material-symbols-outlined text-xl">local_parking</span>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-[#0b1c30]">Parkir Luas</h4>
                            <p class="text-[11px] text-gray-500">Kapasitas 40+ mobil &amp; motor</p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ======================================================== --}}
            {{-- RIGHT COLUMN: Sticky Order Summary & Booking Checkout --}}
            {{-- ======================================================== --}}
            <div class="lg:col-span-4 sticky top-24">
                <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-lg border border-gray-100 flex flex-col gap-5">

                    {{-- Summary Header --}}
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">
                            RINGKASAN PEMESANAN
                        </span>
                        <div class="flex items-center gap-3 mt-2">
                            <div class="w-12 h-12 rounded-xl bg-gray-900 overflow-hidden shrink-0 border border-gray-200">
                                <img src="{{ $lapangan->foto_url }}" alt="{{ $lapangan->nama }}" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-headline font-bold text-[#0b1c30] truncate">
                                    {{ $lapangan->nama }}
                                </h3>
                                <p class="text-xs text-gray-500 truncate">
                                    {{ $lapangan->kategori }} • Standard AFC
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Schedule Summary Meta --}}
                    <div class="p-4 rounded-2xl bg-[#eff4ff] border border-[#dce9ff] flex flex-col gap-2.5 text-xs">
                        <div class="flex items-center justify-between text-gray-600">
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm text-[#1a146b]">calendar_today</span>
                                <span>Tanggal:</span>
                            </span>
                            <strong class="text-gray-900" x-text="formattedDate"></strong>
                        </div>

                        <div class="flex items-center justify-between text-gray-600">
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm text-[#1a146b]">schedule</span>
                                <span>Slot Waktu:</span>
                            </span>
                            <strong class="text-gray-900" x-text="timeRangeLabel"></strong>
                        </div>

                        <div class="flex items-center justify-between text-gray-600">
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm text-[#1a146b]">timelapse</span>
                                <span>Durasi Total:</span>
                            </span>
                            <strong class="text-[#1a146b]" x-text="durationHours > 0 ? (durationHours + ' Jam Sesi') : '-'"></strong>
                        </div>
                    </div>

                    {{-- Cost Breakdown --}}
                    <div class="flex flex-col gap-2 text-xs text-gray-600 pt-1">
                        <div class="flex items-center justify-between">
                            <span>Sesi Sewa Lapangan (<span x-text="durationHours"></span> jam):</span>
                            <span class="font-bold text-gray-900" x-text="selectedSlots.length > 0 ? formatRp(courtTotalFee) : 'Rp0'"></span>
                        </div>

                        <template x-if="equipmentTotalFee > 0">
                            <div class="flex items-center justify-between text-emerald-700 font-medium">
                                <span>Sewa Perlengkapan Tambahan:</span>
                                <span class="font-bold" x-text="formatRp(equipmentTotalFee)"></span>
                            </div>
                        </template>

                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-1">
                                <span>Biaya Layanan &amp; Pemeliharaan</span>
                                <span class="material-symbols-outlined text-xs text-gray-400">info</span>
                            </span>
                            <span class="font-bold text-[#416900] uppercase text-[10px]">GRATIS</span>
                        </div>

                        <div class="border-t border-gray-100 my-1"></div>

                        <div class="flex items-center justify-between text-sm font-headline">
                            <span class="font-bold text-gray-900">Total Biaya Sewa:</span>
                            <span class="font-black text-lg text-[#1a146b]" x-text="formatRp(grandTotal)"></span>
                        </div>
                    </div>

                    {{-- Payment Scheme Selector --}}
                    <div class="flex flex-col gap-2.5 pt-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">
                            PILIHAN SKEMA PEMBAYARAN
                        </span>

                        <label class="p-3.5 rounded-2xl border transition cursor-pointer flex flex-col gap-1"
                               :class="paymentScheme === 'dp' ? 'border-[#416900] bg-[#eaf7d8]/40 ring-1 ring-[#acf847]' : 'border-gray-200 bg-white hover:bg-gray-50'">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <input type="radio" name="scheme" value="dp" x-model="paymentScheme" class="text-[#416900] focus:ring-[#acf847]">
                                    <span class="text-xs font-bold text-gray-900">Bayar DP 50%</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-md bg-[#acf847] text-[#102000] text-[10px] font-black uppercase tracking-wider">
                                    Rekomendasi
                                </span>
                            </div>
                            <div class="flex items-baseline justify-between pl-6 mt-1">
                                <span class="text-base font-headline font-black text-[#416900]" x-text="formatRp(dpAmount)"></span>
                                <span class="text-[10px] text-gray-500">Sisa dibayar di kasir venue</span>
                            </div>
                        </label>

                        <label class="p-3.5 rounded-2xl border transition cursor-pointer flex flex-col gap-1"
                               :class="paymentScheme === 'full' ? 'border-[#1a146b] bg-indigo-50/50 ring-1 ring-[#1a146b]' : 'border-gray-200 bg-white hover:bg-gray-50'">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <input type="radio" name="scheme" value="full" x-model="paymentScheme" class="text-[#1a146b] focus:ring-[#1a146b]">
                                    <span class="text-xs font-bold text-gray-900">Bayar Lunas (100%)</span>
                                </div>
                            </div>
                            <div class="flex items-baseline justify-between pl-6 mt-1">
                                <span class="text-base font-headline font-black text-[#1a146b]" x-text="formatRp(grandTotal)"></span>
                                <span class="text-[10px] text-gray-500">Main langsung tanpa kasir</span>
                            </div>
                        </label>
                    </div>

                    {{-- Checkout Submit Form --}}
                    <form method="GET" action="{{ route('checkout.index', ['lapangan_id' => $lapangan->id]) }}" class="pt-1">
                        <input type="hidden" name="lapangan_id" value="{{ $lapangan->id }}">
                        <input type="hidden" name="tanggal" :value="selectedDate">
                        <input type="hidden" name="jam_mulai" :value="earliestSlot ? earliestSlot.substring(0, 5) : ''">
                        <input type="hidden" name="durasi" :value="durationHours">
                        <input type="hidden" name="tipe_bayar" :value="paymentScheme">

                        @guest
                            <div class="mb-3 p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] leading-snug flex items-start gap-2">
                                <span class="material-symbols-outlined text-base text-amber-600 shrink-0 mt-0.5">info</span>
                                <span>Pemesanan online memerlukan akun terdaftar. Anda akan diarahkan ke pendaftaran akun sebelum menyelesaikan pesanan.</span>
                            </div>
                        @endguest

                        <button type="submit"
                                :disabled="selectedSlots.length === 0"
                                class="w-full py-4 px-6 rounded-2xl font-headline font-black text-sm flex items-center justify-center gap-2 shadow-md transition-all active:scale-[0.99] cursor-pointer"
                                :class="selectedSlots.length > 0 ? 'bg-[#416900] hover:bg-[#315000] text-white shadow-emerald-700/20' : 'bg-gray-200 text-gray-400 cursor-not-allowed'">
                            <span class="material-symbols-outlined text-lg">check_circle</span>
                            <span x-text="selectedSlots.length === 0 ? 'Pilih Jam Terlebih Dahulu' : (paymentScheme === 'dp' ? 'Lanjut ke Pembayaran DP (' + formatRp(dpAmount) + ')' : 'Lanjut ke Pembayaran Lunas (' + formatRp(grandTotal) + ')')"></span>
                        </button>
                    </form>

                    {{-- Lock Reassurance Box --}}
                    <div class="p-3 rounded-2xl bg-gray-50 border border-gray-100 flex items-start gap-2.5 text-gray-500 text-[11px] leading-snug">
                        <span class="material-symbols-outlined text-base text-[#1a146b] shrink-0 mt-0.5">lock_clock</span>
                        <span>Slot otomatis terkunci aman selama 10 menit saat Anda menekan tombol lanjut ke checkout.</span>
                    </div>

                    {{-- Helpline Note --}}
                    <div class="text-center pt-1 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">support_agent</span>
                            <span>Butuh bantuan jadwal?</span>
                        </span>
                        <a href="https://wa.me/{{ config('app.support_whatsapp', '6281234567890') }}" target="_blank" class="font-bold text-[#1a146b] hover:underline">
                            Hubungi CS
                        </a>
                    </div>

                </div>
            </div>

        </div>

    </div>

</div>
@endsection
