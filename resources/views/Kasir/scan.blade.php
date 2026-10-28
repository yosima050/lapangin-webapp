@extends('layouts.kasir')

@section('title', 'Pemindai Kamera QR Code & Check-in - LapangIn')

@section('content')
<div class="w-full px-6 lg:px-8 py-8 flex flex-col gap-6" x-data="{
    paymentMethod: 'cash',
    receivedAmount: 0,
    remainingDue: 0,
    notes: '',
    get changeAmount() {
        return Math.max(0, this.receivedAmount - this.remainingDue);
    },
    formatRupiah(val) {
        return new Intl.NumberFormat('id-ID').format(val || 0);
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
                    SPRINT 4 • CHECK-IN POS
                </span>
                <span class="text-xs text-[#474651] flex items-center gap-1 font-medium">
                    <span class="material-symbols-outlined text-sm text-[#416900]">photo_camera</span>
                    HTML5-QRCode Camera Scanner
                </span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-headline font-extrabold text-[#0b1c30] tracking-tight">
                Pemindai Kamera QR Code &amp; Check-in
            </h1>
            <p class="text-sm text-[#474651] mt-1 max-w-2xl">
                Arahkan kamera ke QR Code tiket e-booking pelanggan untuk deteksi instan, atau gunakan opsi input alternatif token manual 6 karakter.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div id="camera-status-badge" class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#acf847] text-[#102000] text-[11px] font-extrabold">
                <span class="w-2 h-2 rounded-full bg-[#416900] animate-pulse"></span>
                <span>Kamera Menghubungkan...</span>
            </div>
            <a href="{{ route('kasir.index') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-[#eff4ff] hover:bg-[#dce9ff] text-[#0b1c30] text-xs font-bold transition-all shadow-sm border border-[#e5eeff]">
                <span class="material-symbols-outlined text-base">desktop_windows</span>
                <span>Meja Kasir</span>
            </a>
        </div>
    </div>

    <!-- Main Workspace Split Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- LEFT COLUMN: Live Camera QR Scanner & Manual Token Input (7 cols) -->
        <div class="lg:col-span-7 flex flex-col gap-6">

            <!-- Camera Scanner Card -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5eeff] flex flex-col gap-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-[#eff4ff]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#eff4ff] flex items-center justify-center text-[#1a146b]">
                            <span class="material-symbols-outlined text-2xl">qr_code_scanner</span>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-[#0b1c30] leading-tight">Pemindai Kamera Aktif</h2>
                            <p class="text-xs text-[#777682]">Deteksi otomatis via Web Camera / Barcode Reader</p>
                        </div>
                    </div>

                    <!-- Camera Device Selector & Action Buttons -->
                    <div class="flex items-center gap-2">
                        <select id="camera-select" class="px-3 py-1.5 rounded-xl bg-[#eff4ff] text-[#0b1c30] text-xs font-semibold outline-none border border-[#e5eeff] cursor-pointer">
                            <option value="">Memindai Kamera...</option>
                        </select>
                        <button type="button" id="btn-stop-camera" class="px-3 py-1.5 rounded-xl bg-[#ffdad6] hover:bg-[#ffb4ab] text-[#ba1a1a] text-xs font-bold transition-colors hidden flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">videocam_off</span>
                            <span>Stop</span>
                        </button>
                        <button type="button" id="btn-start-camera" class="px-3 py-1.5 rounded-xl bg-[#1a146b] hover:bg-[#2b249b] text-white text-xs font-bold transition-colors flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">videocam</span>
                            <span>Mulai</span>
                        </button>
                    </div>
                </div>

                <!-- Video Viewfinder Container with html5-qrcode -->
                <div class="relative w-full rounded-2xl overflow-hidden bg-[#0c1424] flex flex-col items-center justify-center min-h-[340px] shadow-inner border border-[#1a253d]">
                    <!-- Viewfinder Corner Accents -->
                    <div class="pointer-events-none absolute inset-4 z-20 flex flex-col justify-between">
                        <div class="flex justify-between">
                            <span class="w-8 h-8 border-t-[4px] border-l-[4px] border-[#acf847] rounded-tl-lg"></span>
                            <span class="w-8 h-8 border-t-[4px] border-r-[4px] border-[#acf847] rounded-tr-lg"></span>
                        </div>
                        <!-- Animated Scanning Laser -->
                        <div class="w-full h-[2px] bg-gradient-to-r from-transparent via-[#acf847] to-transparent shadow-[0_0_15px_#acf847] animate-pulse"></div>
                        <div class="flex justify-between">
                            <span class="w-8 h-8 border-b-[4px] border-l-[4px] border-[#acf847] rounded-bl-lg"></span>
                            <span class="w-8 h-8 border-b-[4px] border-r-[4px] border-[#acf847] rounded-br-lg"></span>
                        </div>
                    </div>

                    <!-- html5-qrcode reader element -->
                    <div id="qr-reader" class="w-full h-full min-h-[320px] flex items-center justify-center"></div>

                    <!-- Overlay hint -->
                    <div class="absolute bottom-3 inset-x-3 z-20 flex items-center justify-between pointer-events-none">
                        <span class="text-[11px] text-white/80 bg-black/60 px-3 py-1 rounded-md backdrop-blur-md font-medium">
                            Arahkan QR Tiket ke area bidik kamera
                        </span>
                        <span class="text-[11px] text-[#acf847] bg-black/60 px-3 py-1 rounded-md backdrop-blur-md font-bold">
                            Deteksi Instan 2D
                        </span>
                    </div>
                </div>
            </div>

            <!-- Alternative Manual Token Input Card (Cadangan) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5eeff] flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#eff4ff]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-[#acf847]/40 flex items-center justify-center text-[#102000]">
                            <span class="material-symbols-outlined text-xl">dialpad</span>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-[#0b1c30]">Opsi Cadangan: Token Manual 6 Karakter</h2>
                            <p class="text-xs text-[#777682]">Gunakan jika kamera kasir terkendala atau QR tiket rusak</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-mono font-bold text-[#474651] bg-[#eff4ff] px-2.5 py-1 rounded-lg">F2 Shortcut</span>
                </div>

                <form id="form-token-manual" class="flex flex-col sm:flex-row gap-3 items-stretch">
                    <div class="relative flex-1">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#777682] material-symbols-outlined text-xl">key</span>
                        <input type="text" id="manual-token-input" maxlength="6"
                               placeholder="Contoh: TEST99"
                               autocomplete="off"
                               class="w-full pl-11 pr-4 py-3 rounded-xl bg-[#eff4ff] text-[#0b1c30] text-base font-mono font-extrabold uppercase tracking-widest outline-none focus:bg-white focus:ring-2 focus:ring-[#acf847] border border-[#e5eeff] transition-all">
                    </div>
                    <button type="submit" id="btn-verify-token" class="px-6 py-3 rounded-xl bg-[#1a146b] hover:bg-[#2b249b] text-white text-xs font-bold transition-all shadow-sm flex items-center justify-center gap-2 shrink-0 cursor-pointer">
                        <span class="material-symbols-outlined text-lg">search_check</span>
                        <span>Verifikasi Token</span>
                    </button>
                </form>
            </div>

        </div>

        <!-- RIGHT COLUMN: Panduan Operasional & Riwayat Check-in Hari Ini (5 cols) -->
        <div class="lg:col-span-5 flex flex-col gap-6">

            <!-- Panduan Operasional Check-in -->
            <div class="bg-gradient-to-br from-[#eff4ff] to-[#e5eeff] rounded-2xl p-6 border border-[#dce9ff] flex flex-col gap-4 shadow-xs">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#1a146b] text-xl">verified</span>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#1a146b]">PANDUAN OPERASIONAL KASIR</span>
                    </div>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-white text-[#416900] shadow-xs">POS Ready</span>
                </div>

                <div class="flex flex-col gap-3 text-xs text-[#0b1c30]">
                    <div class="flex items-start gap-3 bg-white/70 p-3 rounded-xl border border-white">
                        <span class="w-6 h-6 rounded-full bg-[#1a146b] text-white flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">1</span>
                        <div>
                            <span class="font-bold block">Pindai QR / Ketikkan Token</span>
                            <span class="text-[#474651]">Arahkan tiket e-booking pelanggan ke kamera atau input 6 kode alfanumerik.</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 bg-white/70 p-3 rounded-xl border border-white">
                        <span class="w-6 h-6 rounded-full bg-[#1a146b] text-white flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">2</span>
                        <div>
                            <span class="font-bold block">Validasi Otomatis &amp; Buka Modal</span>
                            <span class="text-[#474651]">Sistem memvalidasi status: tiket valid, kedaluwarsa, atau duplikat (sudah dipakai).</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 bg-white/70 p-3 rounded-xl border border-white">
                        <span class="w-6 h-6 rounded-full bg-[#1a146b] text-white flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">3</span>
                        <div>
                            <span class="font-bold block">Pelunasan &amp; Konfirmasi Onsite</span>
                            <span class="text-[#474651]">Selesaikan pelunasan sisa biaya sewa dan konfirmasi check-in pelanggan.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Check-in Kasir Hari Ini -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5eeff] flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#eff4ff]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#416900] text-xl">history_edu</span>
                        <h2 class="text-sm font-bold text-[#0b1c30]">Riwayat Check-in Hari Ini</h2>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full bg-[#eff4ff] text-[#474651] text-[10px] font-bold">
                        {{ $recentCheckIns->count() }} Sesi
                    </span>
                </div>

                <div class="flex flex-col gap-2.5">
                    @forelse ($recentCheckIns as $item)
                        @php
                            $nama = $item->pelanggan->nama ?? $item->nama_tamu ?? 'Penyewa';
                        @endphp
                        <div class="p-3 rounded-xl bg-[#eff4ff] border border-[#e5eeff] flex flex-col gap-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-[#0b1c30]">{{ $nama }}</span>
                                <span class="px-2 py-0.5 rounded-md bg-[#acf847] text-[#102000] text-[10px] font-extrabold">Check-in OK</span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-[#474651]">
                                <span>{{ $item->lapangan->nama ?? 'Lapangan' }} • {{ substr($item->jam_mulai, 0, 5) }} WIB</span>
                                <span class="font-mono text-[11px] text-[#1a146b] font-bold">{{ $item->token_manual ?: substr($item->id, 0, 8) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-[#777682] flex flex-col items-center gap-1.5">
                            <span class="material-symbols-outlined text-3xl text-[#777682]/40">receipt_long</span>
                            <span>Belum ada check-in yang dicatat kasir hari ini.</span>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <!-- MODAL DETAIL BOOKING & VALIDASI CHECK-IN -->
    <div id="booking-modal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-[#e5eeff] overflow-hidden my-8 animate-in fade-in zoom-in duration-200">

            <!-- Modal Header -->
            <div class="px-6 py-5 bg-[#eff4ff] border-b border-[#e5eeff] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#1a146b] text-[#acf847] flex items-center justify-center shadow-xs">
                        <span class="material-symbols-outlined text-2xl">confirmation_number</span>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-[#0b1c30]">Detail Booking Reservasi</h3>
                        <p class="text-xs text-[#777682]">Verifikasi identitas dan rincian e-tiket pelanggan</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span id="modal-status-badge" class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-[#acf847] text-[#102000]">
                        TIKET VALID
                    </span>
                    <button type="button" data-close-modal class="w-8 h-8 rounded-full bg-white hover:bg-[#dce9ff] text-[#0b1c30] flex items-center justify-center transition-colors">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 flex flex-col gap-5 max-h-[75vh] overflow-y-auto">

                <!-- Dynamic Warning Container (Untuk Tiket Kedaluwarsa / Duplikat) -->
                <div id="modal-warning-container" class="hidden p-4 rounded-xl">
                    <span class="material-symbols-outlined text-2xl shrink-0 mt-0.5">warning</span>
                    <div class="flex flex-col gap-0.5">
                        <span id="modal-warning-title" class="font-extrabold text-sm"></span>
                        <span id="modal-warning-message" class="text-xs"></span>
                    </div>
                </div>

                <!-- Info Grid: Tamu & Lapangan -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl bg-[#eff4ff] border border-[#e5eeff] flex flex-col gap-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#777682]">IDENTITAS PEMESAN</span>
                        <div id="modal-customer-name" class="text-base font-extrabold text-[#0b1c30]">-</div>
                        <div class="flex items-center gap-1.5 text-xs text-[#416900] font-semibold mt-0.5">
                            <span class="material-symbols-outlined text-sm">call</span>
                            <span id="modal-customer-phone">-</span>
                        </div>
                        <div class="text-xs text-[#474651] mt-1 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-[#1a146b]">qr_code</span>
                            <span class="font-mono font-bold text-[11px]" id="modal-booking-code">-</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-[#eff4ff] border border-[#e5eeff] flex flex-col gap-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#777682]">SPESIFIKASI JADWAL &amp; LAPANGAN</span>
                        <div class="flex items-center justify-between">
                            <div id="modal-court-name" class="text-base font-extrabold text-[#0b1c30]">-</div>
                            <span id="modal-court-category" class="px-2 py-0.5 rounded-md bg-[#dce9ff] text-[#1a146b] text-[10px] font-bold">-</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-[#1a146b] font-medium mt-0.5">
                            <span class="material-symbols-outlined text-sm">schedule</span>
                            <span id="modal-schedule-date">-</span> • <span id="modal-schedule-time">-</span>
                        </div>
                        <div class="text-xs text-[#474651] mt-1 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-[#416900]">pin</span>
                            <span>Token Manual: <strong class="font-mono text-[#0b1c30]" id="modal-token-manual">-</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Financial Breakdown -->
                <div class="rounded-2xl bg-[#eff4ff] p-5 border border-[#e5eeff] flex flex-col gap-2.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#474651]">RINCIAN STATUS PEMBAYARAN</span>
                    <div class="flex items-center justify-between text-xs text-[#0b1c30]">
                        <span>Total Tarif Sewa Lapangan</span>
                        <span id="modal-total-price" class="font-bold">Rp 0</span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-[#416900]">
                        <span>DP Terbayar (Online / Midtrans)</span>
                        <span id="modal-dp-paid" class="font-bold">Rp 0</span>
                    </div>
                    <div class="pt-2 border-t border-[#dce9ff] flex items-center justify-between">
                        <span class="text-xs font-extrabold text-[#ba1a1a] uppercase">Sisa Tagihan Onsite</span>
                        <span id="modal-remaining-due" class="text-xl font-black text-[#ba1a1a]">Rp 0</span>
                    </div>
                </div>

                <!-- Form Checkin & Pelunasan Kasir -->
                <form id="modal-checkin-form" method="POST" action="" class="flex flex-col gap-4">
                    @csrf

                    <!-- Pelunasan Onsite (Jika ada sisa tagihan) -->
                    <div id="modal-payment-section" class="flex flex-col gap-3">
                        <div class="flex items-center gap-2 text-xs font-bold text-[#0b1c30]">
                            <span class="material-symbols-outlined text-base text-[#1a146b]">payments</span>
                            <span>Metode Pelunasan Sisa Pembayaran</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="flex items-center justify-center gap-1.5 p-2.5 rounded-xl border border-[#dce9ff] bg-[#f8f9ff] text-xs font-bold text-[#0b1c30] cursor-pointer hover:bg-white has-[:checked]:bg-[#1a146b] has-[:checked]:text-white">
                                <input type="radio" name="metode" value="tunai" checked class="hidden">
                                <span class="material-symbols-outlined text-base">payments</span>
                                <span>Tunai</span>
                            </label>
                            <label class="flex items-center justify-center gap-1.5 p-2.5 rounded-xl border border-[#dce9ff] bg-[#f8f9ff] text-xs font-bold text-[#0b1c30] cursor-pointer hover:bg-white has-[:checked]:bg-[#1a146b] has-[:checked]:text-white">
                                <input type="radio" name="metode" value="online" class="hidden">
                                <span class="material-symbols-outlined text-base">qr_code_2</span>
                                <span>QRIS</span>
                            </label>
                            <label class="flex items-center justify-center gap-1.5 p-2.5 rounded-xl border border-[#dce9ff] bg-[#f8f9ff] text-xs font-bold text-[#0b1c30] cursor-pointer hover:bg-white has-[:checked]:bg-[#1a146b] has-[:checked]:text-white">
                                <input type="radio" name="metode" value="debit" class="hidden">
                                <span class="material-symbols-outlined text-base">credit_card</span>
                                <span>Debit</span>
                            </label>
                        </div>

                        <input type="hidden" name="jumlah" id="modal-input-amount" value="0">
                        <input type="text" name="catatan" placeholder="Catatan kasir tambahan (opsional)" class="w-full px-3.5 py-2.5 rounded-xl bg-[#eff4ff] text-xs text-[#0b1c30] outline-none border border-[#e5eeff] focus:bg-white focus:ring-2 focus:ring-[#acf847]">
                    </div>

                    <!-- Modal Action Buttons -->
                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-[#eff4ff]">
                        <button type="button" data-close-modal class="px-5 py-3 rounded-xl bg-[#eff4ff] hover:bg-[#e5eeff] text-[#0b1c30] text-xs font-bold transition-colors">
                            Tutup
                        </button>
                        <button type="submit" id="modal-btn-confirm-checkin" class="px-6 py-3 rounded-xl bg-[#acf847] hover:bg-[#91db2a] text-[#102000] text-xs font-black transition-all shadow-md flex items-center gap-2 cursor-pointer">
                            <span class="material-symbols-outlined text-lg">check_circle</span>
                            <span>Konfirmasi Check-in</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Scanner Loading Spinner -->
    <div id="scanner-loading-spinner" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center hidden">
        <div class="p-6 rounded-2xl bg-white shadow-xl flex items-center gap-3">
            <span class="material-symbols-outlined text-3xl text-[#1a146b] animate-spin">progress_activity</span>
            <span class="text-xs font-bold text-[#0b1c30]">Memvalidasi Tiket...</span>
        </div>
    </div>

    <!-- Notification Toast Popup -->
    <div id="scanner-toast" class="fixed bottom-6 right-6 z-50 hidden flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-xl transition-all">
        <span id="scanner-toast-icon" class="material-symbols-outlined text-xl"></span>
        <span id="scanner-toast-text" class="text-xs font-bold"></span>
    </div>

</div>

<!-- Pustaka JavaScript html5-qrcode (Lokal 'self' untuk kehandalan tanpa dependensi CDN) -->
<script src="{{ asset('js/html5-qrcode.min.js') }}"></script>

<!-- Script Scanner LapangIn POS Kasir -->
<script src="{{ asset('js/kasir-scanner.js') }}"></script>
@endsection

