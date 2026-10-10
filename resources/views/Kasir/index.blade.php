@extends('layouts.kasir')

@section('title', 'Validasi Check-in & Pelunasan Kasir - LapangIn')

@section('content')
@php
    $bookingCode = $activeBooking->kode_qr ?? $activeBooking->token_manual ?? ($activeBooking ? substr($activeBooking->id, 0, 8) : '-');
    $customerName = $activeBooking?->pelanggan?->nama ?? $activeBooking?->nama_tamu ?? '-';
    $customerPhone = $activeBooking?->pelanggan?->no_hp ?? $activeBooking?->no_wa_tamu ?? '-';
    $courtName = $activeBooking?->lapangan?->nama ?? '-';
    $jamMulai = $activeBooking ? substr($activeBooking->jam_mulai, 0, 5) : '--:--';
    $jamSelesai = $activeBooking ? substr($activeBooking->jam_selesai, 0, 5) : '--:--';
    $initialDue = (int) $sisaTagihan;
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
                Pindai QR Code E-Ticket pemain melalui kamera scanner atau barcode reader untuk memverifikasi reservasi dan memproses pelunasan di tempat.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white shadow-sm border border-[#e5eeff]">
                <span class="w-2.5 h-2.5 rounded-full bg-[#416900] animate-pulse"></span>
                <span class="text-xs font-bold text-[#0b1c30]">Scanner USB: Ready</span>
            </div>
            <a href="{{ route('kasir.transaksi') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-[#dce9ff] hover:bg-[#c3c0ff] text-[#0b1c30] text-xs font-bold transition-all shadow-sm">
                <span class="material-symbols-outlined text-base">history</span>
                <span>Riwayat Sesi Ini</span>
            </a>
        </div>
    </div>

    <!-- Main Workspace Split Grid (5 cols / 7 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- LEFT COLUMN: QR Scanner & Scan Guide (5 cols) -->
        <div class="lg:col-span-5 flex flex-col gap-5">
            <!-- Scanner Card -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5eeff] flex flex-col gap-5">
                <div class="flex items-center justify-between pb-3 border-b border-[#eff4ff]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#eff4ff] flex items-center justify-center text-[#1a146b]">
                            <span class="material-symbols-outlined text-2xl">qr_code_scanner</span>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-[#0b1c30] leading-tight">Pemindai QR E-Ticket</h2>
                            <p class="text-xs text-[#777682]">Kamera Scanner &amp; 2D Optical Reader</p>
                        </div>
                    </div>
                    <span class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#acf847] text-[#102000] text-[11px] font-extrabold">
                        <span class="w-2 h-2 rounded-full bg-[#416900] animate-pulse"></span>
                        Kamera Standby
                    </span>
                </div>

                <!-- Camera Viewfinder with Laser & Corner Accents -->
                <div class="relative w-full h-64 rounded-2xl overflow-hidden bg-[#1c2437] flex flex-col items-center justify-center shadow-inner group">
                    <div class="absolute inset-0 opacity-15 bg-[radial-gradient(#acf847_1px,transparent_1px)] [background-size:16px_16px]"></div>

                    <div class="relative w-48 h-48 rounded-xl flex flex-col items-center justify-center p-4">
                        <span class="absolute top-0 left-0 w-6 h-6 border-t-[3px] border-l-[3px] border-[#acf847] rounded-tl-sm"></span>
                        <span class="absolute top-0 right-0 w-6 h-6 border-t-[3px] border-r-[3px] border-[#acf847] rounded-tr-sm"></span>
                        <span class="absolute bottom-0 left-0 w-6 h-6 border-b-[3px] border-l-[3px] border-[#acf847] rounded-bl-sm"></span>
                        <span class="absolute bottom-0 right-0 w-6 h-6 border-b-[3px] border-r-[3px] border-[#acf847] rounded-br-sm"></span>

                        <div class="absolute left-2 right-2 h-[2px] bg-gradient-to-r from-transparent via-[#acf847] to-transparent shadow-[0_0_12px_#acf847] animate-pulse" style="top: 50%;"></div>

                        <div class="flex flex-col items-center justify-center gap-2 text-center z-10">
                            <span class="material-symbols-outlined text-5xl text-white/30">qr_code_2</span>
                            <p class="text-white/90 text-xs font-semibold leading-tight px-3">
                                Arahkan QR Code E-Tiket pemain tepat pada area bidik
                            </p>
                            <span class="text-[11px] text-white/60">Sensor otomatis memverifikasi saat terdeteksi</span>
                        </div>
                    </div>

                    <div class="absolute top-3 left-3 flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-black/60 backdrop-blur-md text-white text-[11px] font-semibold">
                        <span class="material-symbols-outlined text-xs text-[#acf847]">center_focus_strong</span>
                        <span>Autofokus 2D Aktif</span>
                    </div>

                    <div class="absolute bottom-3 inset-x-3 flex items-center justify-between">
                        <span class="text-[11px] text-white/70 bg-black/50 px-2.5 py-1 rounded-md backdrop-blur-sm">
                            Sensor siap mendeteksi
                        </span>
                        <button type="button" onclick="alert('Pilih file gambar E-Tiket untuk diunggah.');"
                                class="flex items-center gap-1.5 text-xs font-bold text-[#acf847] bg-black/60 hover:bg-black/80 px-3 py-1 rounded-md backdrop-blur-sm transition-colors">
                            <span class="material-symbols-outlined text-sm">upload_file</span>
                            <span>Unggah QR</span>
                        </button>
                    </div>
                </div>

                <!-- Manual Booking Code Input Form (Submits to Controller) -->
                <form method="GET" action="{{ route('kasir.index') }}" class="flex flex-col gap-2 pt-1">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-[#0b1c30]">Atau Input Kode Reservasi Manual</label>
                        <span class="text-[10px] text-[#777682] bg-[#eff4ff] px-2 py-0.5 rounded font-mono">F2 Shortcut</span>
                    </div>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#777682] material-symbols-outlined text-lg">search</span>
                            <input type="text" name="code" x-model="bookingCode"
                                   class="w-full pl-10 pr-3 py-2.5 rounded-xl bg-[#eff4ff] text-[#0b1c30] text-sm font-bold tracking-wider outline-none focus:bg-white focus:ring-2 focus:ring-[#acf847] transition-all border border-[#e5eeff]"
                                   placeholder="Contoh: LPGN-20250513-8821">
                        </div>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#1a146b] hover:bg-[#312e81] text-white text-xs font-bold transition-all shadow-sm flex items-center gap-1.5 shrink-0">
                            <span class="material-symbols-outlined text-base">check</span>
                            <span>Verifikasi</span>
                        </button>
                    </div>
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
                            $antreanNama = $antrean->pelanggan->nama ?? $antrean->nama_tamu ?? 'Penyewa Tamu';
                            $antreanDp = (float) $antrean->transaksis->where('status', 'berhasil')->sum('jumlah');
                            $antreanSisa = max(0, (float)$antrean->harga_disepakati - $antreanDp);
                        @endphp
                        <a href="{{ route('kasir.index', ['code' => $antrean->kode_qr ?? $antrean->token_manual ?? $antrean->id]) }}"
                           class="p-3.5 rounded-xl transition-all border {{ $isSelected ? 'bg-[#dce9ff] border-[#1a146b] shadow-xs' : 'bg-[#eff4ff] hover:bg-[#e5eeff] border-transparent' }} flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $isSelected ? 'bg-[#416900]' : 'bg-[#1a146b]' }}"></span>
                                    <span class="text-xs font-bold text-[#0b1c30]">{{ $antreanNama }}</span>
                                </div>
                                @if ($isSelected)
                                    <span class="px-2 py-0.5 rounded-md bg-[#acf847] text-[#102000] text-[10px] font-extrabold">Dipilih</span>
                                @elseif ($antrean->status_pembayaran === 'lunas_online' || $antreanSisa == 0)
                                    <span class="px-2 py-0.5 rounded-md bg-white text-[#416900] text-[10px] font-bold">Lunas</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md bg-[#ffdad6] text-[#ba1a1a] text-[10px] font-bold">DP 50%</span>
                                @endif
                            </div>

                            <div class="flex items-center justify-between text-xs text-[#474651]">
                                <span>{{ substr($antrean->jam_mulai, 0, 5) }} - {{ substr($antrean->jam_selesai, 0, 5) }} ({{ $antrean->lapangan->kategori ?? 'Lapangan' }})</span>
                                @if ($antreanSisa > 0)
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

            <!-- Verified Booking Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-[#e5eeff] overflow-hidden">
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

                <div class="p-6 flex flex-col gap-6">
                    <!-- 2 Columns: Pemesan vs Spesifikasi Lapangan -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-[#eff4ff] border border-[#e5eeff] flex flex-col gap-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#777682]">PEMESAN</span>
                            <div class="text-base font-bold text-[#0b1c30]">{{ $customerName }}</div>
                            <div class="flex items-center gap-2 text-xs text-[#416900] font-semibold mt-0.5">
                                <span class="material-symbols-outlined text-base">chat</span>
                                <span>{{ $customerPhone }}</span>
                            </div>
                            <div class="text-xs text-[#474651] mt-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm text-[#1a146b]">groups</span>
                                <span>{{ $activeBooking->nama_tamu ?: 'Penyewa Terdaftar LapangIn' }}</span>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-[#eff4ff] border border-[#e5eeff] flex flex-col gap-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#777682]">SPESIFIKASI LAPANGAN</span>
                            <div class="text-base font-bold text-[#0b1c30]">{{ $courtName }}</div>
                            <div class="flex items-center gap-2 text-xs text-[#1a146b] font-medium mt-0.5">
                                <span class="material-symbols-outlined text-base">schedule</span>
                                <span>{{ $jamMulai }} - {{ $jamSelesai }} WIB</span>
                            </div>
                            <div class="text-xs text-[#474651] mt-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm text-[#416900]">sports</span>
                                <span>{{ $activeBooking->catatan_kasir ?: 'Termasuk Rompi Latihan & Bola Match' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Breakdown & Sisa Tagihan Onsite -->
                    <div class="rounded-2xl bg-[#eff4ff] p-5 border border-[#e5eeff] flex flex-col gap-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#474651]">RINCIAN FINANSIAL &amp; RIWAYAT DP</span>

                        <div class="flex items-center justify-between text-sm text-[#0b1c30]">
                            <span>Total Tarif Lapangan</span>
                            <span class="font-bold">Rp {{ number_format($totalHarga, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex items-center justify-between text-sm text-[#416900]">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-base">check_circle</span>
                                <span>DP Dibayar Online (via Midtrans / QRIS)</span>
                            </div>
                            <span class="font-extrabold">- Rp {{ number_format($dpPaid, 0, ',', '.') }}</span>
                        </div>

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
                    </div>

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
                                        class="py-3 px-4 rounded-xl bg-[#eff4ff] hover:bg-[#e5eeff] text-[#0b1c30] text-xs font-bold flex items-center justify-center gap-2 border border-[#dce9ff] transition-colors">
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
                </div>
            </div>

        </div>

    </div>
</div>
@endsection