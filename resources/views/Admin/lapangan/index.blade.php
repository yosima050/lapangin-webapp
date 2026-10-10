@extends('layouts.admin')

@section('title', 'Master Data Lapangan & Tarif Sewa - Admin LapangIn')

@section('content')
<div class="w-full px-8 py-8 flex flex-col gap-6" x-data="{
    drawerOpen: false,
    selectedCourt: 'Lapangan 1 - Vinyl Pro',
    offPeakRate: 120000,
    primeRate: 180000,
    weekendRate: 200000,
    syncCalendar: true,
    openDrawer(courtName, offPeak, prime, weekend) {
        this.selectedCourt = courtName;
        this.offPeakRate = offPeak;
        this.primeRate = prime;
        this.weekendRate = weekend;
        this.drawerOpen = true;
    }
}">
    <!-- Top Context Header Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="text-[11px] font-bold uppercase tracking-widest text-[#102000] bg-[#acf847] px-2.5 py-0.5 rounded-full">
                    FR-12 • UC12
                </span>
                <span class="text-xs font-semibold text-[#474651] flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#416900]"></span>
                    Master Engine V2.4
                </span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-headline font-extrabold text-[#0b1c30] tracking-tight">
                Daftar Lapangan Olahraga &amp; Master Tarif Sewa
            </h1>
            <p class="text-xs lg:text-sm text-[#474651] mt-0.5 max-w-2xl">
                Konfigurasi spesifikasi arena, skema harga bertingkat, dan sinkronisasi otomatis ke sistem reservasi publik LapangIn.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#eff4ff] hover:bg-[#dce9ff] text-[#0b1c30] font-bold text-xs border border-[#dce9ff] transition-all shadow-xs">
                <span class="material-symbols-outlined text-base">tune</span>
                <span>Filter Kategori</span>
            </button>
            <a href="{{ route('admin.lapangan.create') }}"
               class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#1a146b] hover:bg-[#312e81] text-white font-headline font-bold text-xs shadow-md transition-all">
                <span class="material-symbols-outlined text-base text-[#acf847]">add_circle</span>
                <span>+ Tambah Lapangan Baru</span>
            </a>
        </div>
    </div>

    <!-- Summary KPI Section (3 Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Card 1: Total Lapangan Aktif -->
        <div class="p-5 rounded-2xl bg-white shadow-sm border border-[#e5eeff] flex items-start justify-between relative overflow-hidden group hover:shadow-md transition-all">
            <div class="flex flex-col z-10">
                <span class="text-[11px] uppercase font-bold tracking-wider text-[#777682]">TOTAL LAPANGAN AKTIF</span>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-headline font-black text-[#0b1c30]">{{ $lapangans->count() }}</span>
                    <span class="text-xs font-semibold text-[#416900]">Siap Sewa</span>
                </div>
                <div class="flex items-center gap-1.5 mt-3 text-[11px] text-[#474651]">
                    <span class="material-symbols-outlined text-sm text-[#416900]">check_circle</span>
                    <span>100% kapasitas fasilitas online</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#eff4ff] flex items-center justify-center text-[#1a146b]">
                <span class="material-symbols-outlined text-2xl text-[#416900]">sports_soccer</span>
            </div>
        </div>

        <!-- Card 2: Tarif Dasar Rata-Rata -->
        <div class="p-5 rounded-2xl bg-white shadow-sm border border-[#e5eeff] flex items-start justify-between relative overflow-hidden group hover:shadow-md transition-all">
            <div class="flex flex-col z-10">
                <span class="text-[11px] uppercase font-bold tracking-wider text-[#777682]">TARIF DASAR RATA-RATA</span>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-2xl font-headline font-black text-[#0b1c30]">Rp {{ number_format($averageRate ?? 150000, 0, ',', '.') }}</span>
                    <span class="text-xs text-[#777682]">/jam</span>
                </div>
                <div class="flex items-center gap-1.5 mt-3 text-[11px] text-[#474651]">
                    <span class="material-symbols-outlined text-sm text-[#1a146b]">trending_up</span>
                    <span>Rentang aktif Rp {{ number_format(($minRate ?? 60000) / 1000, 0) }}rb - Rp {{ number_format(($maxRate ?? 200000) / 1000, 0) }}rb/jam</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#eff4ff] flex items-center justify-center text-[#1a146b]">
                <span class="material-symbols-outlined text-2xl">payments</span>
            </div>
        </div>

        <!-- Card 3: Status Operasional -->
        <div class="p-5 rounded-2xl bg-white shadow-sm border border-[#e5eeff] flex items-start justify-between relative overflow-hidden group hover:shadow-md transition-all">
            <div class="flex flex-col z-10">
                <span class="text-[11px] uppercase font-bold tracking-wider text-[#777682]">STATUS OPERASIONAL</span>
                <div class="flex items-center gap-2 mt-2">
                    <span class="px-2.5 py-1 rounded-full bg-[#acf847] text-[#102000] text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#416900] animate-pulse"></span>
                        Buka Sekarang
                    </span>
                </div>
                <div class="flex items-center gap-1.5 mt-3 text-[11px] text-[#474651]">
                    <span class="material-symbols-outlined text-sm">schedule</span>
                    <span>Jam operasional 07:00 – 24:00 WIB</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#eff4ff] flex items-center justify-center text-[#1a146b]">
                <span class="material-symbols-outlined text-2xl">storefront</span>
            </div>
        </div>
    </div>

    <!-- Validasi Master Price Proteksi Aktif Info Banner -->
    <div class="p-4 rounded-2xl bg-[#eff4ff] border border-[#dce9ff] flex items-center justify-between gap-4 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-[#1a146b] flex items-center justify-center text-[#acf847] shrink-0">
                <span class="material-symbols-outlined text-xl">verified_user</span>
            </div>
            <div>
                <h4 class="text-xs font-bold text-[#0b1c30]">Validasi Master Price Proteksi Aktif</h4>
                <p class="text-xs text-[#474651]">
                    Perubahan tarif lapangan baru berlaku otomatis untuk booking baru tanpa mengubah booking terkonfirmasi.
                </p>
            </div>
        </div>
        <span class="px-3 py-1 rounded-lg bg-white border border-[#dce9ff] text-[11px] font-bold text-[#1a146b] shrink-0">
            HARGA_MASTER v1.9
        </span>
    </div>

    <!-- Lapangan Card Stack -->
    <div class="flex flex-col gap-5">
        @forelse ($lapangans as $index => $lapangan)
            @php
                $weekdayOffPeak = $lapangan->hargaMasters->whereNull('tanggal_khusus')->where('jenis_hari', 'weekday')->first()?->harga ?? 120000;
                $weekdayPrime = $weekdayOffPeak * 1.5;
                $weekendRate = $lapangan->hargaMasters->whereNull('tanggal_khusus')->where('jenis_hari', 'weekend')->first()?->harga ?? 200000;
                $courtId = 'ID: LAP-0' . ($index + 1);
            @endphp
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5eeff] hover:shadow-md transition-all flex flex-col lg:flex-row gap-6 items-start">
                <!-- Media Pane with Badge -->
                <div class="relative w-full lg:w-72 h-48 rounded-2xl overflow-hidden bg-gray-900 shrink-0">
                    <img src="{{ $lapangan->foto_url }}" alt="{{ $lapangan->nama }}"
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/20"></div>

                    <div class="absolute top-3 left-3 px-2.5 py-1 rounded-md bg-[#1a146b]/90 backdrop-blur-md text-[#acf847] text-[10px] font-extrabold uppercase tracking-wider">
                        {{ strtoupper($lapangan->kategori) }} INDOOR
                    </div>

                    <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-md bg-white/90 backdrop-blur-md text-[#0b1c30] text-[11px] font-mono font-bold">
                        {{ $courtId }}
                    </div>
                </div>

                <!-- Content & Tarif Specs Pane -->
                <div class="flex-1 flex flex-col justify-between w-full h-full gap-4">
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-headline font-bold text-[#0b1c30]">{{ $lapangan->nama }}</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#acf847] text-[#102000]">AKTIF</span>
                            </div>
                            <span class="flex items-center gap-1.5 text-xs text-[#416900] font-semibold">
                                <span class="material-symbols-outlined text-sm">wifi</span> Online Terbuka
                            </span>
                        </div>

                        <!-- Facility Chips -->
                        <div class="flex flex-wrap gap-2 mt-2.5">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#eff4ff] text-[11px] font-medium text-[#474651]">
                                <span class="material-symbols-outlined text-xs text-[#1a146b]">layers</span> Lantai Vinyl Tarkett 8mm
                            </span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#eff4ff] text-[11px] font-medium text-[#474651]">
                                <span class="material-symbols-outlined text-xs text-[#1a146b]">grid_view</span> Jaring Pengaman Keliling
                            </span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#eff4ff] text-[11px] font-medium text-[#474651]">
                                <span class="material-symbols-outlined text-xs text-[#1a146b]">scoreboard</span> Papan Skor Digital Wireless
                            </span>
                        </div>
                    </div>

                    <!-- Pricing Tier Box (2 Columns) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-xl bg-[#eff4ff] border border-[#e5eeff]">
                        <!-- Left: Tarif Weekday -->
                        <div class="flex flex-col gap-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#777682]">TARIF WEEKDAY (SENIN – JUMAT)</span>
                            <div class="flex items-center justify-between text-xs text-[#474651]">
                                <span>Pagi – Siang (07:00 – 16:00):</span>
                                <span class="font-bold text-[#0b1c30]">Rp {{ number_format($weekdayOffPeak, 0, ',', '.') }}/jam</span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-[#474651]">
                                <span>Malam (16:00 – 24:00):</span>
                                <span class="font-bold text-[#0b1c30]">Rp {{ number_format($weekdayPrime, 0, ',', '.') }}/jam</span>
                            </div>
                        </div>

                        <!-- Right: Tarif Weekend -->
                        <div class="flex flex-col gap-1.5 md:border-l md:border-[#dce9ff] md:pl-4">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#777682]">TARIF WEEKEND (SABTU – MINGGU)</span>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-[#474651]">Tarif Seharian (07:00 – 24:00):</span>
                                <span class="font-extrabold text-[#416900]">Rp {{ number_format($weekendRate, 0, ',', '.') }}/jam flat</span>
                            </div>
                            <span class="text-[10px] text-[#416900] flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">bolt</span> Otomatis tier harga peak hours
                            </span>
                        </div>
                    </div>

                    <!-- Footer Details & Action Buttons -->
                    <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                        <span class="text-[#777682] flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">schedule</span>
                            Terakhir dimodifikasi: 12 Jul 2025, 14:20 oleh Bambang S.
                        </span>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.lapangan.edit', $lapangan->id) }}"
                               class="px-3.5 py-1.5 rounded-xl bg-[#eff4ff] hover:bg-[#dce9ff] text-[#0b1c30] font-bold text-xs border border-[#dce9ff] transition-colors flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">edit</span>
                                <span>Edit Detail</span>
                            </a>
                            <button type="button" @click="openDrawer('{{ $lapangan->nama }}', {{ $weekdayOffPeak }}, {{ $weekdayPrime }}, {{ $weekendRate }})"
                                    class="px-3.5 py-1.5 rounded-xl bg-[#1a146b] hover:bg-[#312e81] text-white font-bold text-xs shadow-xs transition-colors flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm text-[#acf847]">tune</span>
                                <span>Atur Tarif</span>
                            </button>
                            <button type="button" onclick="alert('Status lapangan {{ $lapangan->nama }} dialihkan ke nonaktif sementara.');"
                                    class="px-3.5 py-1.5 rounded-xl bg-[#eff4ff] hover:bg-[#ffdad6] text-[#ba1a1a] font-semibold text-xs transition-colors">
                                Nonaktifkan Sementara
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-12 text-center text-[#777682] border border-[#e5eeff]">
                <p class="text-3xl mb-2">🏟️</p>
                <p class="font-bold text-[#0b1c30]">Belum Ada Data Lapangan</p>
                <a href="{{ route('admin.lapangan.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-[#1a146b] text-white rounded-xl text-xs font-bold">
                    Tambah Lapangan Baru
                </a>
            </div>
        @endforelse
    </div>

    <!-- Side Slide-over Drawer / Modal: Pengaturan Tarif Master -->
    <div x-show="drawerOpen" style="display: none;"
         class="fixed inset-0 z-50 overflow-hidden"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-xs" @click="drawerOpen = false"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-md bg-white shadow-2xl p-6 flex flex-col justify-between border-l border-[#e5eeff]"
                 x-transition:enter="transform transition ease-in-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">

                <!-- Drawer Header -->
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-[#eff4ff]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-[#1a146b] text-[#acf847] flex items-center justify-center">
                                <span class="material-symbols-outlined text-lg">tune</span>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-[#0b1c30]">Pengaturan Tarif Master</h3>
                                <p class="text-[10px] text-[#777682] uppercase font-mono">RULE_ENGINE: HARGA_MASTER</p>
                            </div>
                        </div>
                        <button type="button" @click="drawerOpen = false" class="text-[#777682] hover:text-[#0b1c30] p-1 rounded-lg">
                            <span class="material-symbols-outlined text-xl">close</span>
                        </button>
                    </div>

                    <!-- Selected Target Court Chip -->
                    <div class="my-4 p-3 rounded-xl bg-[#eff4ff] border border-[#dce9ff] flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#777682]">LAPANGAN SASARAN</span>
                            <p class="text-xs font-bold text-[#0b1c30]" x-text="selectedCourt"></p>
                        </div>
                        <span class="px-2 py-0.5 rounded-md bg-[#dce9ff] text-[#1a146b] text-[10px] font-bold">Slot 60 Menit</span>
                    </div>

                    <!-- Price Lock Alert -->
                    <div class="p-3.5 rounded-xl bg-[#eff4ff] border border-[#dce9ff] flex items-start gap-2.5 text-xs text-[#474651] mb-5">
                        <span class="material-symbols-outlined text-base text-[#1a146b] shrink-0 mt-0.5">lock</span>
                        <div class="text-[11px] leading-relaxed">
                            <span class="font-bold text-[#0b1c30]">Aturan Penguncian Validasi Harga (Price Lock):</span>
                            Perubahan tidak akan mengubah harga order berstatus Confirmed maupun Menunggu Pembayaran.
                        </div>
                    </div>

                    <!-- Form Inputs -->
                    <form class="flex flex-col gap-4 text-xs" @submit.prevent="alert('✅ Perubahan tarif master untuk ' + selectedCourt + ' berhasil disimpan!'); drawerOpen = false;">
                        <div>
                            <span class="font-bold text-[#0b1c30] uppercase text-[11px] tracking-wider block mb-2">
                                1. TARIF WEEKDAY (SENIN – JUMAT)
                            </span>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-[#777682] block mb-1">Off-Peak Pagi – Siang (07:00 – 16:00)</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#777682] font-bold">Rp</span>
                                        <input type="number" x-model.number="offPeakRate" class="w-full pl-9 pr-14 py-2 rounded-xl bg-[#eff4ff] border border-[#dce9ff] text-[#0b1c30] font-bold outline-none focus:bg-white focus:ring-1 focus:ring-[#1a146b]">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[#777682] text-[10px]">/jam</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-[#777682] block mb-1">Prime Time Malam (16:00 – 24:00)</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#777682] font-bold">Rp</span>
                                        <input type="number" x-model.number="primeRate" class="w-full pl-9 pr-14 py-2 rounded-xl bg-[#eff4ff] border border-[#dce9ff] text-[#0b1c30] font-bold outline-none focus:bg-white focus:ring-1 focus:ring-[#1a146b]">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[#777682] text-[10px]">/jam</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-[#eff4ff]">
                            <span class="font-bold text-[#0b1c30] uppercase text-[11px] tracking-wider block mb-2">
                                2. TARIF WEEKEND &amp; HARI LIBUR UMUM
                            </span>
                            <div>
                                <label class="text-[#777682] block mb-1">Tarif All-Day (Sabtu – Minggu Flat)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#777682] font-bold">Rp</span>
                                    <input type="number" x-model.number="weekendRate" class="w-full pl-9 pr-14 py-2 rounded-xl bg-[#eff4ff] border border-[#dce9ff] text-[#0b1c30] font-bold outline-none focus:bg-white focus:ring-1 focus:ring-[#1a146b]">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[#777682] text-[10px]">/jam</span>
                                </div>
                            </div>
                        </div>

                        <label class="flex items-start gap-2 pt-2 text-[#474651] cursor-pointer">
                            <input type="checkbox" x-model="syncCalendar" class="rounded text-[#1a146b] focus:ring-[#acf847] mt-0.5">
                            <span class="text-[11px]">Sinkronisasikan penyesuaian tarif ini ke modul kalender kasir pegawai secara instan.</span>
                        </label>

                        <!-- Buttons -->
                        <div class="pt-6 border-t border-[#eff4ff] flex items-center justify-end gap-3">
                            <button type="button" @click="drawerOpen = false"
                                    class="px-4 py-2.5 rounded-xl bg-[#eff4ff] hover:bg-[#dce9ff] text-[#0b1c30] font-bold text-xs transition-colors">
                                Batalkan
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-[#1a146b] hover:bg-[#312e81] text-white font-bold text-xs shadow-md transition-all">
                                Simpan Perubahan Tarif
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection
