@extends('layouts.app')

@section('title', 'Pembayaran Berhasil & E-Tiket Masuk')

@section('content')
<style>
    @keyframes lapangin-circle-draw {
        0% { stroke-dashoffset: 152; opacity: 0.35; }
        100% { stroke-dashoffset: 0; opacity: 1; }
    }
    @keyframes lapangin-check-draw {
        0% { stroke-dashoffset: 36; }
        100% { stroke-dashoffset: 0; }
    }
    @keyframes lapangin-check-pop {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.09); }
    }
    .lapangin-success-icon {
        animation: lapangin-check-pop 0.35s ease-out 0.62s both;
    }
    .lapangin-success-circle {
        stroke-dasharray: 152;
        stroke-dashoffset: 152;
        animation: lapangin-circle-draw 0.52s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }
    .lapangin-success-check {
        stroke-dasharray: 36;
        stroke-dashoffset: 36;
        animation: lapangin-check-draw 0.32s cubic-bezier(0.65, 0, 0.45, 1) 0.4s forwards;
    }
    @media (prefers-reduced-motion: reduce) {
        .lapangin-success-icon,
        .lapangin-success-circle,
        .lapangin-success-check {
            animation: none;
            stroke-dashoffset: 0;
            opacity: 1;
        }
    }
</style>

<div class="min-h-screen bg-[#f8f9ff] text-[#0b1c30] py-4 sm:py-12 px-4 sm:px-6 lg:px-8 print:p-0 print:bg-white"
     style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <div class="max-w-4xl mx-auto space-y-4 sm:space-y-6">

        {{-- Success Notice Banner --}}
        <div class="p-4 sm:p-6 rounded-2xl sm:rounded-3xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white shadow-lg flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 sm:gap-4 print:hidden">
            <div class="flex items-center gap-3.5 sm:gap-4 text-left">
                <div class="w-12 h-12 sm:w-14 sm:h-14 shrink-0 flex items-center justify-center">
                    <svg class="lapangin-success-icon w-full h-full text-white" viewBox="0 0 52 52" fill="none" aria-hidden="true">
                        <circle class="lapangin-success-circle" cx="26" cy="26" r="24" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" transform="rotate(-90 26 26)" />
                        <path class="lapangin-success-check" d="M15.5 26.8 L22.6 33.8 L36.8 19.2" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h1 class="text-base sm:text-2xl font-extrabold tracking-tight leading-snug">
                        Pemesanan Lapangan Berhasil!
                    </h1>
                    <p class="text-[11px] sm:text-sm text-emerald-100 font-medium mt-0.5 leading-relaxed">
                        Slot jadwal pertandingan Anda telah resmi dikonfirmasi oleh sistem LapangIn.
                    </p>
                </div>
            </div>

            <div class="w-full sm:w-auto shrink-0">
                <a href="{{ route('checkout.tiketPdf', $transaksi->order_id) }}"
                   target="_blank"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#acf847] hover:bg-[#9be338] text-[#1a146b] font-extrabold text-xs shadow-sm transition whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>Unduh E-Tiket (PDF)</span>
                </a>
            </div>
        </div>

        {{-- Main Boarding Pass / E-Ticket Card --}}
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-[#e5eeff] shadow-xl overflow-hidden print:border-none print:shadow-none">
            {{-- Ticket Header --}}
            <div class="bg-[#1a146b] text-white p-4 sm:p-7 flex items-center gap-3.5 sm:gap-4 border-b border-indigo-900/50">
                <div class="w-9 h-9 sm:w-11 sm:h-11 flex items-center justify-center text-[#acf847] shrink-0">
                    <x-lucide-ticket class="w-full h-full" stroke-width="1.8" />
                </div>
                <div class="min-w-0">
                    <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-[#acf847]">BOARDING PASS LAPANGIN</span>
                    <h2 class="text-base sm:text-2xl font-black tracking-tight text-white leading-snug">
                        {{ $jadwal?->lapangan?->nama ?: 'Arena LapangIn' }}
                    </h2>
                    <p class="text-[11px] sm:text-xs text-indigo-200 mt-0.5">
                        Kategori: <strong class="text-white">{{ $jadwal?->lapangan?->kategori ?: 'Olahraga' }}</strong>
                    </p>
                </div>
            </div>

            {{-- Ticket Body (QR di atas di HP agar langsung siap scan, 2 kolom di Desktop) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12">

                {{-- Left Column (Desktop) / Bottom (Mobile): Booking Details --}}
                <div class="order-2 lg:order-1 lg:col-span-8 p-4 sm:p-8 space-y-4 sm:space-y-6">

                    {{-- Schedule & Venue Grid --}}
                    <div class="grid grid-cols-2 gap-2.5 sm:gap-4">
                        <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-[#eff4ff] border border-[#dce9ff]">
                            <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider text-[#1a146b]/70 block mb-1">
                                TANGGAL MAIN
                            </span>
                            <div class="flex items-start sm:items-center gap-1.5 sm:gap-2">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#1a146b] shrink-0 stroke-current mt-0.5 sm:mt-0" fill="none" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                <span class="font-extrabold text-xs sm:text-base text-[#0b1c30] leading-snug">
                                    {{ \Carbon\Carbon::parse($jadwal?->tanggal)->translatedFormat('l, d F Y') }}
                                </span>
                            </div>
                        </div>

                        <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-[#eff4ff] border border-[#dce9ff]">
                            <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider text-[#1a146b]/70 block mb-1">
                                JAM MAIN &amp; DURASI
                            </span>
                            <div class="flex items-start sm:items-center gap-1.5 sm:gap-2">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#1a146b] shrink-0 stroke-current mt-0.5 sm:mt-0" fill="none" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-extrabold text-xs sm:text-base text-[#0b1c30] leading-snug">
                                    {{ substr((string) $jadwal?->jam_mulai, 0, 5) }} – {{ substr((string) $jadwal?->jam_selesai, 0, 5) }} WIB
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Data Pemesan --}}
                    <div class="border-t border-gray-100 pt-4 sm:pt-5">
                        <h3 class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-gray-500 mb-2.5 sm:mb-3">
                            Informasi Pemesan
                        </h3>
                        <div class="grid grid-cols-2 gap-3 text-xs sm:text-sm">
                            <div class="min-w-0">
                                <span class="text-[11px] sm:text-xs text-gray-400 block">Nama Pemesan</span>
                                <strong class="text-gray-900 font-bold break-words">{{ $namaPemesan }}</strong>
                            </div>
                            <div class="min-w-0">
                                <span class="text-[11px] sm:text-xs text-gray-400 block">Nomor WhatsApp</span>
                                <strong class="text-gray-900 font-bold font-mono break-all">{{ $noHpPemesan ?: '-' }}</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Billing Breakdown --}}
                    <div class="border-t border-gray-100 pt-4 sm:pt-5">
                        <h3 class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-gray-500 mb-2.5 sm:mb-3">
                            Rincian Pembayaran
                        </h3>
                        <div class="p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-gray-50 border border-gray-100 space-y-2 text-xs">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-gray-600">Total Biaya Sewa:</span>
                                <span class="font-bold text-gray-900 shrink-0">Rp {{ number_format($totalHarga, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2 text-emerald-700">
                                <span class="font-semibold">Sudah Dibayar Online ({{ $isFull ? 'Lunas 100%' : 'DP 50%' }}):</span>
                                <span class="font-bold shrink-0">Rp {{ number_format($nominalTerbayar, 0, ',', '.') }}</span>
                            </div>
                            @if ($sisaBayar > 0)
                                <div class="pt-2 border-t border-gray-200 flex items-center justify-between gap-2 text-[#1a146b]">
                                    <div>
                                        <span class="font-extrabold text-xs block">Sisa Bayar di Meja Kasir:</span>
                                        <span class="text-[10px] text-gray-500">(Bisa Tunai / QRIS saat tiba di lokasi)</span>
                                    </div>
                                    <span class="font-black text-sm text-[#1a146b] shrink-0">
                                        Rp {{ number_format($sisaBayar, 0, ',', '.') }}
                                    </span>
                                </div>
                            @else
                                <div class="pt-2 border-t border-gray-200 flex items-center justify-between gap-2 text-emerald-800">
                                    <span class="font-bold">Status Pelunasan:</span>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black shrink-0">
                                        LUNAS (100% ONLINE)
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- Right Column (Desktop) / Top Highlight (Mobile): QR Code & Check-in Turnstile --}}
                <div class="order-1 lg:order-2 lg:col-span-4 p-5 sm:p-8 bg-[#f4f7ff] border-b border-dashed border-gray-200 lg:border-b-0 lg:border-l lg:border-solid lg:border-gray-100 flex flex-col items-center justify-center text-center">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#1a146b]/70 mb-3">
                        GATE CHECK-IN
                    </span>

                    {{-- QR Code Card --}}
                    <div class="bg-white p-3.5 sm:p-4 rounded-2xl sm:rounded-3xl shadow-sm border border-indigo-100 mb-3.5 sm:mb-4 inline-block">
                        @if (!empty($jadwal?->kode_qr))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(170)->margin(1)->generate($jadwal->kode_qr) !!}
                        @else
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(170)->margin(1)->generate($transaksi->order_id) !!}
                        @endif
                    </div>

                    <div class="space-y-1">
                        <span class="text-[11px] text-gray-500 block">Kode Token Manual:</span>
                        <div class="inline-block px-4 py-1.5 rounded-xl bg-white border border-indigo-200 font-mono font-black text-base tracking-widest text-[#1a146b]">
                            {{ $jadwal?->token_manual ?: substr($transaksi->order_id, -6) }}
                        </div>
                        <span class="text-[10px] text-gray-400 font-mono block pt-0.5">{{ $transaksi->order_id }}</span>
                    </div>

                    <p class="text-[11px] text-gray-500 mt-3.5 sm:mt-4 max-w-[240px] leading-relaxed">
                        Tunjukkan QR Code ini ke petugas kasir saat Anda tiba di gelanggang olahraga.
                    </p>
                </div>

            </div>
        </div>

        {{-- Guest Callout: Claim Account Banner (Only for Unauthenticated Users) --}}
        @guest
            <div class="p-4 sm:p-7 rounded-2xl sm:rounded-3xl bg-gradient-to-br from-[#1a146b] via-[#211a7a] to-[#120d4f] text-white shadow-lg border border-[#30269c] flex flex-col sm:flex-row sm:items-center justify-between gap-4 sm:gap-5 print:hidden">
                <div class="space-y-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-[#acf847] text-[#102000] text-[9px] sm:text-[10px] font-black uppercase tracking-wider">
                        FITUR AUTO-CLAIM AKUN
                    </span>
                    <h3 class="text-sm sm:text-lg font-bold text-white leading-snug">
                        Ingin riwayat ini tersimpan &amp; bisa mengajukan Reschedule?
                    </h3>
                    <p class="text-[11px] sm:text-xs text-indigo-200 max-w-xl leading-relaxed">
                        Cukup buat kata sandi untuk nomor <strong>{{ $noHpPemesan }}</strong>. Seluruh tiket dan riwayat booking Anda akan otomatis tersambung ke akun baru Anda.
                    </p>
                </div>

                <div class="shrink-0">
                    <a href="{{ route('register', ['no_hp' => $noHpPemesan, 'name' => $namaPemesan]) }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-3 rounded-xl bg-[#acf847] hover:bg-[#9de03d] text-[#1a146b] font-headline font-black text-xs shadow-md transition">
                        <span>Daftarkan Akun Sekarang</span>
                        <svg class="w-4 h-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="text-center pt-1 print:hidden">
                <a href="{{ route('home') }}" class="text-xs font-semibold text-gray-500 hover:text-[#1a146b] transition">
                    &larr; Selesai dan kembali ke Beranda LapangIn
                </a>
            </div>
        @else
            <div class="grid grid-cols-2 sm:flex sm:items-center sm:justify-between gap-2.5 pt-1 print:hidden">
                <a href="{{ route('pelanggan.booking.index') }}"
                   class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-4 py-2.5 rounded-xl border border-[#dce9ff] bg-white hover:bg-[#eff4ff] text-xs font-bold text-[#1a146b] shadow-2xs transition">
                    <svg class="w-4 h-4 stroke-current shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <span class="truncate">Riwayat Booking</span>
                </a>

                <a href="{{ route('home') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-4 sm:px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition">
                    Kembali ke Beranda
                </a>
            </div>
        @endguest

    </div>
</div>
@endsection

