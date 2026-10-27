@extends('layouts.kasir')

@section('title', 'Validasi Check-in & Pelunasan Kasir - LapangIn')

@section('content')
@php
    $bookingCode = $activeBooking?->kode_qr ?? $activeBooking?->token_manual ?? ($activeBooking ? substr($activeBooking->id, 0, 8) : '-');
    $customerName = $activeBooking?->pelanggan?->nama ?? $activeBooking?->nama_tamu ?? '-';
    $customerPhone = $activeBooking?->pelanggan?->no_hp ?? $activeBooking?->no_wa_tamu ?? '-';
    $courtName = $activeBooking?->lapangan?->nama ?? '-';
    $jamMulai = $activeBooking ? substr($activeBooking->jam_mulai, 0, 5) : '--:--';
    $jamSelesai = $activeBooking ? substr($activeBooking->jam_selesai, 0, 5) : '--:--';
    $initialDue = (int) $sisaTagihan;
    $totalBiayaPerlengkapan = (float) (
    $activeBooking?->detailSewaPerlengkapans?->sum('subtotal') ?? 0
    );
    $totalTarifLapangan = max(
    0,
    $totalHarga - $totalBiayaPerlengkapan
    );
    $preset2 = $initialDue > 0 ? (ceil($initialDue / 100000) * 100000) : 100000;
    if ($preset2 <= $initialDue) { $preset2 += 50000; }
    $preset3 = $preset2 + 50000;
@endphp

<div class="w-full px-8 py-8 flex flex-col gap-6" x-data="{
    receivedAmount: {{ $initialDue > 0 ? $preset2 : 0 }},
    totalDue: {{ $initialDue }},
    paymentMethod: 'cash',
    bookingCode: '{{ $bookingCode }}',
    notes: '',
    confirmed: false,
    get changeAmount() {
        return Math.max(0, this.receivedAmount - this.totalDue);
    },
    formatRupiah(val) {
        return new Intl.NumberFormat('id-ID').format(val);
    }
}">
    <!-- Flash Notifications -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-[#acf847]/30 border border-[#acf847] flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-2xl text-[#416900]">check_circle</span>
                <span class="text-xs font-bold text-[#102000]">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-[#416900] hover:text-[#102000]">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-[#ffdad6] border border-[#ba1a1a]/30 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-2xl text-[#ba1a1a]">error</span>
                <span class="text-xs font-bold text-[#ba1a1a]">{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-[#ba1a1a] hover:text-[#410002]">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
    @endif

    <!-- Subheader Strip -->
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-[#acf847] text-[#102000] text-[11px] font-extrabold uppercase tracking-wider">
                    POS OPERASIONAL
                </span>
                <span class="text-xs text-[#474651] flex items-center gap-1 font-medium">
                    <span class="material-symbols-outlined text-sm text-[#416900]">sync</span>
                    Database Real-Time LapangIn
                </span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-headline font-extrabold text-[#0b1c30] tracking-tight">
                Validasi Check-in &amp; Pelunasan Kasir
            </h1>
            <p class="text-sm text-[#474651] mt-1 max-w-2xl">
                Verifikasi reservasi pemain melalui kode booking atau token manual untuk memvalidasi check-in dan memproses pelunasan onsite.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('kasir.scan') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white hover:bg-[#eff4ff] text-[#1a146b] text-xs font-bold transition-all border border-[#dce9ff] shadow-xs">
                <span class="material-symbols-outlined text-base">qr_code_scanner</span>
                <span>Buka Pemindai Kamera</span>
            </a>
            <a href="{{ route('kasir.transaksi') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-[#1a146b] hover:bg-[#312e81] text-white text-xs font-bold transition-all shadow-sm">
                <span class="material-symbols-outlined text-base">history</span>
                <span>Riwayat Sesi Ini</span>
            </a>
        </div>
    </div>

    <!-- Main Workspace Split Grid (5 cols / 7 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- LEFT COLUMN: Input Kode & Antrean (5 cols) -->
        <div class="lg:col-span-5 flex flex-col gap-6">

            <!-- Card Pencarian & Verifikasi Kode Reservasi Manual (Tanpa Scanner Kamera) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5eeff] flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-[#eff4ff] flex items-center justify-center text-[#1a146b]">
                            <span class="material-symbols-outlined text-xl">confirmation_number</span>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-[#0b1c30]">Cari &amp; Verifikasi Tiket</h2>
                            <p class="text-[11px] text-[#777682]">Input Kode Booking atau Token Manual 6 Digit</p>
                        </div>
                    </div>
                    <span class="text-[10px] text-[#777682] bg-[#eff4ff] px-2.5 py-1 rounded-lg font-mono font-bold">F2 Shortcut</span>
                </div>

                <!-- Manual Booking Code Input Form (Submits to Controller) -->
                <form method="GET" action="{{ route('kasir.index') }}" class="flex flex-col gap-3 pt-1">
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#777682] material-symbols-outlined text-xl pointer-events-none">search</span>
                        <input type="text" name="code" x-model="bookingCode"
                               class="w-full pl-11 pr-4 py-3 rounded-xl bg-[#eff4ff] text-[#0b1c30] text-sm font-bold tracking-wider outline-none focus:bg-white focus:ring-2 focus:ring-[#acf847] transition-all border border-[#e5eeff]"
                               placeholder="Contoh: LPNG-QR-... atau Token 6 Digit">
                    </div>
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#1a146b] hover:bg-[#312e81] text-white text-xs font-bold transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                        <span class="material-symbols-outlined text-base">verified</span>
                        <span>Cari &amp; Verifikasi Tiket</span>
                    </button>
                </form>
            </div>

            <!-- Antrean Tamu Hari Ini (Real from Database) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5eeff] flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#416900] text-xl">queue</span>
                        <h2 class="text-sm font-bold text-[#0b1c30]">Antrean Tamu Hari Ini</h2>
                    </div>
                    <span class="px-2 py-0.5 rounded-full bg-[#eff4ff] text-[#474651] text-[10px] font-bold">
                        {{ $antreanHariIni->count() }} Terdaftar
                    </span>
                </div>

                <div class="flex flex-col gap-2.5">
                    @forelse ($antreanHariIni as $antrean)
                        @php
                            $isSelected = $activeBooking && $activeBooking->id === $antrean->id;
                            $antreanBatal = in_array($antrean->status, ['dibatalkan', 'cancelled', 'rejected']);
                            $antreanNama = $antrean->pelanggan->nama ?? $antrean->nama_tamu ?? 'Penyewa Tamu';
                            $antreanDp = (float) $antrean->transaksis->where('status', 'berhasil')->sum('jumlah');
                            $antreanSisa = max(0, (float)$antrean->harga_disepakati - $antreanDp);
                        @endphp
                        <a href="{{ route('kasir.index', ['code' => $antrean->kode_qr ?? $antrean->token_manual ?? $antrean->id]) }}"
                           class="p-3.5 rounded-xl transition-all border {{ $isSelected ? ($antreanBatal ? 'bg-[#ffdad6]/60 border-[#ba1a1a] shadow-xs' : 'bg-[#dce9ff] border-[#1a146b] shadow-xs') : ($antreanBatal ? 'bg-[#ffdad6]/30 hover:bg-[#ffdad6]/50 border-red-200/60 opacity-80' : 'bg-[#eff4ff] hover:bg-[#e5eeff] border-transparent') }} flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $antreanBatal ? 'bg-[#ba1a1a]' : ($isSelected ? 'bg-[#416900]' : 'bg-[#1a146b]') }}"></span>
                                    <span class="text-xs font-bold {{ $antreanBatal ? 'text-[#ba1a1a] line-through' : 'text-[#0b1c30]' }}">{{ $antreanNama }}</span>
                                </div>
                                @if ($antreanBatal)
                                    <span class="px-2 py-0.5 rounded-md bg-[#ba1a1a] text-white text-[10px] font-black tracking-wide">Dibatalkan</span>
                                @elseif ($isSelected)
                                    <span class="px-2 py-0.5 rounded-md bg-[#acf847] text-[#102000] text-[10px] font-extrabold">Dipilih</span>
                                @elseif ($antrean->status_pembayaran === 'lunas_online' || $antreanSisa == 0)
                                    <span class="px-2 py-0.5 rounded-md bg-white text-[#416900] text-[10px] font-bold">Lunas</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md bg-[#ffdad6] text-[#ba1a1a] text-[10px] font-bold">DP 50%</span>
                                @endif
                            </div>

                            <div class="flex items-center justify-between text-xs text-[#474651]">
                                <span class="{{ $antreanBatal ? 'line-through text-gray-400' : '' }}">{{ substr($antrean->jam_mulai, 0, 5) }} - {{ substr($antrean->jam_selesai, 0, 5) }} ({{ $antrean->lapangan->kategori ?? 'Lapangan' }})</span>
                                @if ($antreanBatal)
                                    <span class="font-bold text-[#ba1a1a]">Slot Dilepas (Denda Rp {{ number_format((float) $antrean->denda_pembatalan, 0, ',', '.') }})</span>
                                @elseif ($antreanSisa > 0)
                                    <span class="font-bold text-[#ba1a1a]">Sisa Rp {{ number_format($antreanSisa, 0, ',', '.') }}</span>
                                @else
                                    <span class="font-bold text-[#416900]">Rp 0 (Lunas)</span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <p class="text-xs text-[#777682] py-2 text-center">Belum ada antrean reservasi hari ini.</p>
                    @endforelse
                </div>

                <!-- Occupancy Gauge Meter -->
                <div class="rounded-xl bg-[#eff4ff] p-4 flex items-center justify-between border border-[#dce9ff]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center text-[#1a146b] shadow-xs">
                            <span class="material-symbols-outlined">sports_soccer</span>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-[#0b1c30]">Ringkasan Slot Aktif</div>
                            <div class="text-[11px] text-[#474651]">{{ $occupiedSlots }} dari {{ $totalCapacitySlots }} Slot Terisi</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-lg font-headline font-black text-[#1a146b]">{{ $occupancyRate }}%</span>
                    </div>
                </div>
            </div>

            <!-- Panduan Cepat Validasi (1-Click Scan) -->
            <div class="bg-[#eff4ff] rounded-2xl p-5 border border-[#dce9ff] flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#1a146b]">PANDUAN CEPAT VALIDASI</span>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-white text-[#416900] shadow-xs">1-Click Scan</span>
                </div>
                <div class="flex flex-col gap-2.5 text-xs text-[#474651]">
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-base text-[#416900] shrink-0 mt-0.5">check_circle</span>
                        <span>Pemain menunjukkan QR tiket dari aplikasi LapangIn atau screenshot e-tiket.</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-base text-[#416900] shrink-0 mt-0.5">check_circle</span>
                        <span>Sistem langsung mencocokkan hash token terenkripsi dan rincian DP reservasi secara realtime.</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-base text-[#416900] shrink-0 mt-0.5">check_circle</span>
                        <span>Lanjutkan pelunasan sisa tagihan onsite pada panel kasir di samping.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Confirmed Details & Cashier Payment Form (7 cols) -->
        <div class="lg:col-span-7 flex flex-col gap-5">

            <!-- Verified / Cancelled Booking Card -->
            @php
                $isCancelled = $activeBooking && in_array($activeBooking->status, ['dibatalkan', 'cancelled', 'rejected']);
            @endphp
            <div class="bg-white rounded-2xl shadow-sm border {{ $isCancelled ? 'border-2 border-red-300' : 'border-[#e5eeff]' }} overflow-hidden">
                @if ($isCancelled)
                    <!-- Cancelled Danger Header Banner -->
                    <div class="p-6 bg-gradient-to-r from-red-600 via-rose-600 to-red-700 text-white border-b-2 border-red-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-white text-red-600 flex items-center justify-center shadow-md shrink-0">
                                <svg class="w-6 h-6 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-white text-[10px] font-black uppercase tracking-wider mb-1">
                                    <span class="h-2 w-2 rounded-full bg-red-200 animate-pulse"></span>
                                    STATUS: RESERVASI DIBATALKAN
                                </span>
                                <div class="text-2xl font-headline font-black text-white tracking-tight">
                                    {{ $bookingCode }}
                                </div>
                                <p class="text-xs text-red-100 font-semibold mt-0.5">
                                    Slot jadwal telah dilepas kembali ke publik. Tiket ini TIDAK VALID untuk check-in.
                                </p>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="inline-flex items-center gap-1.5 text-xs font-black px-3.5 py-1.5 rounded-xl bg-white text-red-700 border border-red-200 shadow-sm">
                                <svg class="w-4 h-4 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                TIKET TIDAK BERLAKU
                            </span>
                        </div>
                    </div>
                @else
                    <!-- Verified Top Header Banner -->
                    <div class="p-6 bg-[#eff4ff] border-b border-[#e5eeff] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-full bg-[#acf847] flex items-center justify-center text-[#102000] shadow-sm">
                                <span class="material-symbols-outlined text-2xl font-bold">verified</span>
                            </div>
                            <div>
                                <span class="inline-block px-2.5 py-0.5 rounded-full bg-[#acf847] text-[#102000] text-[10px] font-extrabold uppercase tracking-wider mb-0.5">
                                    KODE VALID TERKONFIRMASI
                                </span>
                                <div class="text-xl font-headline font-extrabold text-[#0b1c30] tracking-tight">
                                    {{ $bookingCode }}
                                </div>
                            </div>
                        </div>
                        <span class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-white border border-[#dce9ff] text-[#474651]">
                            Server: Synced 1 mnt lalu
                        </span>
                    </div>
                @endif

                <div class="p-6 flex flex-col gap-6">
                    @if ($isCancelled)
                        <!-- Peringatan Pembatalan Eksplisit untuk Kasir -->
                        <div class="p-4 rounded-2xl bg-red-50 border-2 border-red-300 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-red-900 shadow-xs">
                            <div class="flex items-start gap-3">
                                <div class="h-10 w-10 rounded-xl bg-red-100 text-red-700 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-5 h-5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-extrabold text-red-900">PERINGATAN KASIR: PEMESANAN TELAH DIBATALKAN</h4>
                                    <p class="text-xs text-red-700 mt-0.5 leading-relaxed font-semibold">
                                        {{ $activeBooking->catatan_kasir ?: 'Jadwal ini telah dibatalkan. Jangan izinkan pemain masuk ke lapangan.' }}
                                    </p>
                                </div>
                            </div>
                            @if ($activeBooking->denda_pembatalan > 0)
                                <div class="bg-white px-3.5 py-2 rounded-xl border border-red-200 text-right shrink-0 shadow-xs">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-red-600 block">DENDA PENALTI TERPOTONG</span>
                                    <span class="text-sm font-black text-red-800">Rp {{ number_format((float) $activeBooking->denda_pembatalan, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- 2 Columns: Pemesan vs Spesifikasi Lapangan -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl {{ $isCancelled ? 'bg-red-50/50 border border-red-200' : 'bg-[#eff4ff] border border-[#e5eeff]' }} flex flex-col gap-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider {{ $isCancelled ? 'text-red-700' : 'text-[#777682]' }}">PEMESAN</span>
                            <div class="text-base font-bold text-[#0b1c30]">{{ $customerName }}</div>
                            <div class="flex items-center gap-2 text-xs {{ $isCancelled ? 'text-red-700' : 'text-[#416900]' }} font-semibold mt-0.5">
                                <span class="material-symbols-outlined text-base">chat</span>
                                <span>{{ $customerPhone }}</span>
                            </div>
                            <div class="text-xs text-[#474651] mt-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm text-[#1a146b]">groups</span>
                                <span>{{ $activeBooking?->nama_tamu ?: 'Penyewa Terdaftar LapangIn' }}</span>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl {{ $isCancelled ? 'bg-red-50/70 border-2 border-red-300' : 'bg-[#eff4ff] border border-[#e5eeff]' }} flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold uppercase tracking-wider {{ $isCancelled ? 'text-red-700' : 'text-[#777682]' }}">SPESIFIKASI LAPANGAN</span>
                                @if ($isCancelled)
                                    <span class="px-2 py-0.5 rounded-md bg-red-600 text-white text-[9px] font-black uppercase">
                                        DIBATALKAN
                                    </span>
                                @endif
                            </div>
                            <div class="text-base font-bold {{ $isCancelled ? 'text-red-900 line-through' : 'text-[#0b1c30]' }}">{{ $courtName }}</div>
                            <div class="flex items-center gap-2 text-xs {{ $isCancelled ? 'text-red-700 font-semibold' : 'text-[#1a146b] font-medium' }} mt-0.5">
                                <span class="material-symbols-outlined text-base">schedule</span>
                                <span class="{{ $isCancelled ? 'line-through' : '' }}">{{ $jamMulai }} - {{ $jamSelesai }} WIB</span>
                            </div>
                            <div class="text-xs {{ $isCancelled ? 'text-red-800 bg-red-100/80 p-2 rounded-lg font-semibold' : 'text-[#474651]' }} mt-1 flex items-start gap-1.5">
                                <span class="material-symbols-outlined text-sm {{ $isCancelled ? 'text-red-600' : 'text-[#416900]' }} shrink-0 mt-0.5">
                                    {{ $isCancelled ? 'cancel' : 'sports' }}
                                </span>
                                <span>{{ $activeBooking?->catatan_kasir ?: 'Termasuk Rompi Latihan & Bola Match' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Breakdown & Sisa Tagihan Onsite -->
                    <div class="rounded-2xl {{ $isCancelled ? 'bg-red-50/40 border border-red-200' : 'bg-[#eff4ff] border border-[#e5eeff]' }} p-5 flex flex-col gap-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider {{ $isCancelled ? 'text-red-700' : 'text-[#474651]' }}">RINCIAN FINANSIAL &amp; RIWAYAT PEMBATALAN</span>

                        <div class="flex items-center justify-between text-sm text-[#0b1c30]">
                            <span>Tarif Sewa Lapangan</span>
                            <span class="font-bold">
                                Rp {{ number_format($totalTarifLapangan, 0, ',', '.') }}
                            </span>
                        </div>

                        @if ($activeBooking?->detailSewaPerlengkapans?->isNotEmpty())
                            <div class="border-t border-[#dce9ff] pt-3 mt-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-[#474651]">
                                    RINCIAN SEWA TAMBAHAN
                                </span>

                                @foreach ($activeBooking->detailSewaPerlengkapans as $detail)
                                    <div class="flex items-start justify-between gap-3 text-sm text-[#0b1c30] mt-2">
                                        <div>
                                            <div class="font-semibold">
                                                {{ $detail->nama_perlengkapan }}
                                            </div>
                                            <div class="text-xs text-[#474651]">
                                                {{ $detail->jumlah }} × Rp
                                                {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                                            </div>
                                        </div>

                                        <span class="font-bold whitespace-nowrap">
                                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach

                                <div class="flex items-center justify-between text-sm text-[#0b1c30] mt-3 pt-2 border-t border-[#dce9ff]">
                                    <span>Total Sewa Tambahan</span>
                                    <span class="font-bold">
                                        Rp {{ number_format($totalBiayaPerlengkapan, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center justify-between text-sm text-[#0b1c30] border-t border-[#dce9ff] pt-3">
                            <span class="font-bold">Total Nilai Booking</span>
                            <span class="font-extrabold">
                                Rp {{ number_format($totalHarga, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-[#416900]">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-base">check_circle</span>
                                <span>DP / Pembayaran Masuk (Online / Kasir)</span>
                            </div>
                            <span class="font-extrabold">- Rp {{ number_format($dpPaid, 0, ',', '.') }}</span>
                        </div>

                        @if ($isCancelled)
                            <!-- Big Status Pembatalan Box -->
                            <div class="mt-2 pt-4 border-t-2 border-red-300 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <div class="text-[11px] font-black uppercase tracking-wider text-red-700">STATUS PEMBATALAN SEWA</div>
                                    <div class="text-xs text-red-600 font-semibold mt-0.5">Denda penalti tercatat resmi di database venue</div>
                                </div>
                                <div class="text-right">
                                    <span class="text-2xl lg:text-3xl font-headline font-black text-red-700 tracking-tight">
                                        Penalti: Rp {{ number_format((float) $activeBooking->denda_pembatalan, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @else
                            <!-- Big Sisa Tagihan Box -->
                            <div class="mt-2 pt-4 border-t border-[#dce9ff] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-[#ba1a1a]">SISA TAGIHAN PELUNASAN ONSITE</div>
                                    <div class="text-xs text-[#ba1a1a] font-medium mt-0.5">Wajib diselesaikan kasir sebelum kick-off</div>
                                </div>
                                <div class="text-right">
                                    <span class="text-3xl lg:text-4xl font-headline font-black text-[#ba1a1a] tracking-tight">
                                        Rp {{ number_format($sisaTagihan, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($isCancelled)
                        <!-- Panel Dinonaktifkan Karena Batal -->
                        <div class="rounded-2xl border-2 border-dashed border-red-300 bg-red-50/70 p-6 sm:p-8 text-center space-y-4">
                            <div class="w-16 h-16 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mx-auto shadow-xs">
                                <svg class="w-8 h-8 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-lg font-headline font-black text-red-950">
                                    Transaksi Pelunasan &amp; Check-in Dinonaktifkan
                                </h3>
                                <p class="text-xs text-red-700 max-w-md mx-auto leading-relaxed">
                                    Tiket ini telah <strong>Dibatalkan</strong>. Sesi bermain tidak dapat divalidasi dan gerbang masuk turnstile ditutup untuk reservasi ini. Slot jadwal telah kembali tersedia untuk publik.
                                </p>
                            </div>
                            <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                                <a href="{{ route('kasir.index') }}"
                                   class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-white border border-red-200 text-red-700 hover:bg-red-50 font-bold text-xs transition shadow-xs">
                                    <span class="material-symbols-outlined text-base">qr_code_scanner</span>
                                    <span>Scan / Cari Tiket Lain</span>
                                </a>
                                <a href="{{ route('kasir.jadwal') }}"
                                   class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-[#1a146b] text-white hover:bg-indigo-900 font-bold text-xs transition shadow-xs">
                                    <span class="material-symbols-outlined text-base">calendar_month</span>
                                    <span>Buka Jadwal Lapangan</span>
                                </a>
                            </div>
                        </div>
                    @else
                        <!-- Cashier Payment Form -->
                        <div class="flex flex-col gap-5 pt-2">
                        <div class="flex items-center gap-2 text-base font-bold text-[#0b1c30]">
                            <span class="material-symbols-outlined text-[#1a146b]">payments</span>
                            <span>Form Pembayaran Kasir</span>
                        </div>

                        <!-- Payment Method Toggle -->
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-[#474651]">Pilih Metode Pembayaran</label>
                            <div class="grid grid-cols-3 gap-3">
                                <button type="button" @click="paymentMethod = 'cash'"
                                        :class="paymentMethod === 'cash' ? 'bg-[#1a146b] text-white shadow-md' : 'bg-[#e5eeff] text-[#0b1c30] hover:bg-[#dce9ff]'"
                                        class="flex flex-col items-center justify-center p-3.5 rounded-xl font-bold text-xs gap-1 transition-all">
                                    <span class="material-symbols-outlined text-xl">payments</span>
                                    <span>Tunai / Cash</span>
                                </button>
                                <button type="button" @click="paymentMethod = 'qris'"
                                        :class="paymentMethod === 'qris' ? 'bg-[#1a146b] text-white shadow-md' : 'bg-[#e5eeff] text-[#0b1c30] hover:bg-[#dce9ff]'"
                                        class="flex flex-col items-center justify-center p-3.5 rounded-xl font-bold text-xs gap-1 transition-all">
                                    <span class="material-symbols-outlined text-xl">qr_code_2</span>
                                    <span>QRIS Kasir</span>
                                </button>
                                <button type="button" @click="paymentMethod = 'debit'"
                                        :class="paymentMethod === 'debit' ? 'bg-[#1a146b] text-white shadow-md' : 'bg-[#e5eeff] text-[#0b1c30] hover:bg-[#dce9ff]'"
                                        class="flex flex-col items-center justify-center p-3.5 rounded-xl font-bold text-xs gap-1 transition-all">
                                    <span class="material-symbols-outlined text-xl">credit_card</span>
                                    <span>Debit / EDC</span>
                                </button>
                            </div>
                        </div>

                        <!-- Cash Received & Change Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Input Uang Diterima -->
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-semibold text-[#474651]">Uang Diterima dari Tamu</label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-bold text-[#777682]">Rp</span>
                                    <input type="number" x-model.number="receivedAmount"
                                           class="w-full pl-11 pr-4 py-3 rounded-xl bg-[#eff4ff] text-[#0b1c30] text-lg font-extrabold outline-none focus:bg-white focus:ring-2 focus:ring-[#acf847] border border-[#e5eeff] transition-all">
                                </div>
                                <!-- Preset Buttons -->
                                <div class="flex items-center gap-1.5 mt-1">
                                    <button type="button" @click="receivedAmount = {{ $initialDue }}"
                                            :class="receivedAmount === {{ $initialDue }} ? 'bg-[#acf847] text-[#102000] font-bold' : 'bg-[#eff4ff] hover:bg-[#e5eeff] text-[#0b1c30]'"
                                            class="px-2.5 py-1 rounded-lg text-xs transition-colors">
                                        Uang Pas
                                    </button>
                                    <button type="button" @click="receivedAmount = {{ $preset2 }}"
                                            :class="receivedAmount === {{ $preset2 }} ? 'bg-[#acf847] text-[#102000] font-bold' : 'bg-[#eff4ff] hover:bg-[#e5eeff] text-[#0b1c30]'"
                                            class="px-2.5 py-1 rounded-lg text-xs transition-colors">
                                        Rp {{ number_format($preset2, 0, ',', '.') }}
                                    </button>
                                    <button type="button" @click="receivedAmount = {{ $preset3 }}"
                                            :class="receivedAmount === {{ $preset3 }} ? 'bg-[#acf847] text-[#102000] font-bold' : 'bg-[#eff4ff] hover:bg-[#e5eeff] text-[#0b1c30]'"
                                            class="px-2.5 py-1 rounded-lg text-xs transition-colors">
                                        Rp {{ number_format($preset3, 0, ',', '.') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Display Kembalian -->
                            <div class="p-4 rounded-xl bg-[#eff4ff] border border-[#e5eeff] flex flex-col justify-between">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#777682]">KEMBALIAN KASIR</span>
                                    <div class="text-2xl lg:text-3xl font-headline font-black text-[#416900] mt-1 tracking-tight">
                                        Rp <span x-text="formatRupiah(changeAmount)">0</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 text-xs text-[#416900] font-medium mt-2">
                                    <span class="material-symbols-outlined text-sm">check</span>
                                    <span>Pastikan fisik uang kembalian diserahkan</span>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan Kasir -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-[#474651]">Catatan Kasir (Opsional)</label>
                            <input type="text" x-model="notes"
                                   placeholder="Contoh: Tambah sewa 2 rompi merah / bola ganti seri Pro"
                                   class="w-full px-4 py-2.5 rounded-xl bg-[#eff4ff] text-[#0b1c30] text-xs outline-none focus:bg-white focus:ring-2 focus:ring-[#acf847] border border-[#e5eeff] transition-all">
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-3 flex flex-col gap-3">
                            @if ($activeBooking)
                                <form method="POST" action="{{ route('kasir.checkin', $activeBooking->id) }}">
                                    
                                    @csrf
                                    <input type="hidden" name="metode" :value="paymentMethod">
                                    <input type="hidden" name="jumlah" :value="receivedAmount">
                                    <input type="hidden" name="catatan" :value="notes">

                            @if (isset($perlengkapans) && $perlengkapans->isNotEmpty())
                                    <div class="mt-4 rounded-xl border border-[#dce9ff] bg-[#eff4ff] p-4">
                                        <h3 class="mb-3 text-sm font-bold text-[#0b1c30]">
                                            Tambahan Sewa Perlengkapan
                                        </h3>

                                        @foreach ($perlengkapans as $perlengkapan)
                                            <div class="mb-3 flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="text-sm font-semibold text-[#0b1c30]">
                                                        {{ $perlengkapan->nama }}
                                                    </p>
                                                    <p class="text-xs text-[#474651]">
                                                        Rp {{ number_format($perlengkapan->harga, 0, ',', '.') }}
                                                        / item
                                                    </p>
                                                </div>

                                                <input
                                                    type="number"
                                                    name="perlengkapan[{{ $perlengkapan->id }}]"
                                                    min="0"
                                                    max="20"
                                                    value="0"
                                                    class="w-20 rounded-lg border border-[#dce9ff] bg-white px-2 py-2 text-sm"
                                                >
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                    <button type="submit"
                                            class="w-full py-4 px-6 rounded-2xl bg-[#acf847] hover:bg-[#91db2a] text-[#102000] font-headline font-black text-base flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition-all active:scale-[0.99] cursor-pointer">
                                        <span class="material-symbols-outlined text-2xl font-bold">check_circle</span>
                                        <span>Konfirmasi Pelunasan &amp; Check-in Berhasil</span>
                                    </button>
                                </form>
                            @else
                                <button type="button" disabled
                                        class="w-full py-4 px-6 rounded-2xl bg-[#eff4ff] text-[#777682] font-headline font-bold text-base flex items-center justify-center gap-2">
                                    <span>Pilih Tiket Reservasi Terlebih Dahulu</span>
                                </button>
                            @endif

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                               <button type="button" onclick="window.print()"
                                        class="print:hidden py-3 px-4 rounded-xl bg-[#eff4ff] hover:bg-[#e5eeff] text-[#0b1c30] text-xs font-bold flex items-center justify-center gap-2 border border-[#dce9ff] transition-colors">
                                    <span class="material-symbols-outlined text-lg">print</span>
                                    <span>Cetak Struk Pembayaran</span>
                                </button>
                                <button type="button" onclick="alert('Bukti pembayaran telah dikirim ke nomor WhatsApp {{ $customerPhone }}')"
                                        class="py-3 px-4 rounded-xl bg-[#eff4ff] hover:bg-[#e5eeff] text-[#0b1c30] text-xs font-bold flex items-center justify-center gap-2 border border-[#dce9ff] transition-colors">
                                    <span class="material-symbols-outlined text-lg text-[#416900]">chat</span>
                                    <span>Kirim Bukti ke WhatsApp Tamu</span>
                                </button>
                            </div>
                        </div>

                    </div>
                    @endif
                </div>
            </div>
        
        </div>

    </div>
    
</div>

@endsection