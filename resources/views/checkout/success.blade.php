@extends('layouts.app')

@section('title', 'Pembayaran Berhasil & E-Tiket Masuk')

@section('content')
<div class="min-h-screen bg-[#f8f9ff] text-[#0b1c30] py-8 sm:py-12 px-4 sm:px-6 lg:px-8 print:p-0 print:bg-white"
     style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Success Notice Banner --}}
        <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white shadow-lg flex flex-col sm:flex-row items-center justify-between gap-4 print:hidden">
            <div class="flex items-center gap-4 text-center sm:text-left">
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center shrink-0">
                    <svg class="w-8 h-8 text-white stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-white/25 text-white text-[10px] font-black uppercase tracking-wider mb-1">
                        PEMBAYARAN DIVERIFIKASI
                    </span>
                    <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight">
                        Pemesanan Lapangan Berhasil!
                    </h1>
                    <p class="text-xs sm:text-sm text-emerald-100 font-medium mt-0.5">
                        Slot jadwal pertandingan Anda telah resmi dikonfirmasi oleh sistem LapangIn.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button type="button" onclick="window.print()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-emerald-800 font-bold text-xs hover:bg-emerald-50 shadow-sm transition cursor-pointer">
                    <svg class="w-4 h-4 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.64-2.115-1.173-3.085m12.906 0a17.394 17.394 0 00-1.173 3.085m-10.56 0A17.917 17.917 0 0012 15c2.316 0 4.475-.436 6.44-1.171m-12.88 0C4.385 10.978 4 8.784 4 6.5 4 4.567 5.567 3 7.5 3h9C18.433 3 20 4.567 20 6.5c0 2.284-.385 4.478-1.56 7.329M6.72 13.829a17.92 17.92 0 00-1.173 3.085M6.72 13.829A17.92 17.92 0 0112 15m0 0v6m-4-3h8" />
                    </svg>
                    <span>Cetak E-Tiket</span>
                </button>
            </div>
        </div>

        {{-- Main Boarding Pass / E-Ticket Card --}}
        <div class="bg-white rounded-3xl border border-[#e5eeff] shadow-xl overflow-hidden print:border-none print:shadow-none">
            {{-- Ticket Header --}}
            <div class="bg-[#1a146b] text-white p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-indigo-900/50">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-[#acf847] shrink-0 border border-white/10">
                        <span class="material-symbols-outlined text-2xl">confirmation_number</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-[#acf847]">BOARDING PASS LAPANGIN</span>
                        <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white">
                            {{ $jadwal?->lapangan?->nama ?: 'Arena LapangIn' }}
                        </h2>
                        <p class="text-xs text-indigo-200">
                            Kategori: <strong class="text-white">{{ $jadwal?->lapangan?->kategori ?: 'Olahraga' }}</strong>
                        </p>
                    </div>
                </div>

                <div class="text-left sm:text-right bg-white/10 sm:bg-transparent p-3 sm:p-0 rounded-2xl border sm:border-none border-white/10">
                    <span class="text-[10px] uppercase font-bold text-indigo-200 block">KODE RESERVASI</span>
                    <span class="text-xl sm:text-2xl font-black font-mono tracking-wider text-[#acf847]">
                        {{ $jadwal?->token_manual ?: substr($transaksi->order_id, -6) }}
                    </span>
                    <span class="text-[10px] text-indigo-200 block mt-0.5">Order ID: {{ $transaksi->order_id }}</span>
                </div>
            </div>

            {{-- Ticket Body (2-Column Grid ala Boarding Pass) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-gray-100">

                {{-- Left Column: Booking Details --}}
                <div class="lg:col-span-8 p-6 sm:p-8 space-y-6">

                    {{-- Schedule & Venue Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl bg-[#eff4ff] border border-[#dce9ff]">
                            <span class="text-[10px] font-black uppercase tracking-wider text-[#1a146b]/70 block mb-1">
                                TANGGAL PERTANDINGAN
                            </span>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#1a146b] shrink-0 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                <span class="font-extrabold text-sm sm:text-base text-[#0b1c30]">
                                    {{ \Carbon\Carbon::parse($jadwal?->tanggal)->translatedFormat('l, d F Y') }}
                                </span>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-[#eff4ff] border border-[#dce9ff]">
                            <span class="text-[10px] font-black uppercase tracking-wider text-[#1a146b]/70 block mb-1">
                                JAM MAIN &amp; DURASI
                            </span>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#1a146b] shrink-0 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-extrabold text-sm sm:text-base text-[#0b1c30]">
                                    {{ substr((string) $jadwal?->jam_mulai, 0, 5) }} - {{ substr((string) $jadwal?->jam_selesai, 0, 5) }} WIB
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Data Pemesan --}}
                    <div class="border-t border-gray-100 pt-5">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">
                            Informasi Pemesan
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-xs text-gray-400 block">Nama Pemesan</span>
                                <strong class="text-gray-900 font-bold">{{ $namaPemesan }}</strong>
                            </div>
                            <div>
                                <span class="text-xs text-gray-400 block">Nomor WhatsApp</span>
                                <strong class="text-gray-900 font-bold font-mono">{{ $noHpPemesan ?: '-' }}</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Billing Breakdown --}}
                    <div class="border-t border-gray-100 pt-5">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">
                            Rincian Pembayaran
                        </h3>
                        <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Total Biaya Sewa:</span>
                                <span class="font-bold text-gray-900">Rp {{ number_format($totalHarga, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-emerald-700">
                                <span class="font-semibold">Sudah Dibayar Online ({{ $isFull ? 'Lunas 100%' : 'DP 50%' }}):</span>
                                <span class="font-bold">Rp {{ number_format($nominalTerbayar, 0, ',', '.') }}</span>
                            </div>
                            @if ($sisaBayar > 0)
                                <div class="pt-2 border-t border-gray-200 flex items-center justify-between text-[#1a146b]">
                                    <div>
                                        <span class="font-extrabold text-xs block">Sisa Bayar di Meja Kasir:</span>
                                        <span class="text-[10px] text-gray-500">(Bisa Tunai / QRIS saat tiba di lokasi)</span>
                                    </div>
                                    <span class="font-black text-sm text-[#1a146b]">
                                        Rp {{ number_format($sisaBayar, 0, ',', '.') }}
                                    </span>
                                </div>
                            @else
                                <div class="pt-2 border-t border-gray-200 flex items-center justify-between text-emerald-800">
                                    <span class="font-bold">Status Pelunasan:</span>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black">
                                        LUNAS (100% ONLINE)
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- Right Column: QR Code & Check-in Turnstile --}}
                <div class="lg:col-span-4 p-6 sm:p-8 bg-[#f4f7ff] flex flex-col items-center justify-center text-center">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#1a146b]/70 mb-3">
                        GATE CHECK-IN
                    </span>

                    {{-- QR Code Card --}}
                    <div class="bg-white p-4 rounded-3xl shadow-sm border border-indigo-100 mb-4 inline-block">
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
                    </div>

                    <p class="text-[11px] text-gray-500 mt-4 max-w-[220px] leading-relaxed">
                        Tunjukkan QR Code ini ke petugas kasir saat Anda tiba di gelanggang olahraga.
                    </p>
                </div>

            </div>
        </div>

        {{-- Guest Callout: Claim Account Banner (Only for Unauthenticated Users) --}}
        @guest
            <div class="p-6 sm:p-7 rounded-3xl bg-gradient-to-br from-[#1a146b] via-[#211a7a] to-[#120d4f] text-white shadow-lg border border-[#30269c] flex flex-col sm:flex-row sm:items-center justify-between gap-5 print:hidden">
                <div class="space-y-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#acf847] text-[#102000] text-[10px] font-black uppercase tracking-wider">
                        FITUR AUTO-CLAIM AKUN
                    </span>
                    <h3 class="text-base sm:text-lg font-bold text-white">
                        Ingin riwayat ini tersimpan &amp; bisa mengajukan Reschedule?
                    </h3>
                    <p class="text-xs text-indigo-200 max-w-xl leading-relaxed">
                        Cukup buat kata sandi untuk nomor <strong>{{ $noHpPemesan }}</strong>. Seluruh tiket dan riwayat booking Anda akan otomatis tersambung ke akun baru Anda.
                    </p>
                </div>

                <div class="shrink-0">
                    <a href="{{ route('register', ['no_hp' => $noHpPemesan, 'name' => $namaPemesan]) }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-[#acf847] hover:bg-[#9de03d] text-[#1a146b] font-headline font-black text-xs shadow-md transition">
                        <span>Daftarkan Akun Sekarang</span>
                        <svg class="w-4 h-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>
        @else
            <div class="flex items-center justify-between pt-2 print:hidden">
                <a href="{{ route('pelanggan.booking.index') }}"
                   class="inline-flex items-center gap-2 text-xs font-bold text-[#1a146b] hover:underline">
                    <svg class="w-4 h-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <span>Buka Riwayat Reservasi Saya</span>
                </a>

                <a href="{{ route('home') }}"
                   class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition">
                    Kembali ke Beranda
                </a>
            </div>
        @endguest

        <div class="text-center pt-2 print:hidden">
            <a href="{{ route('home') }}" class="text-xs font-semibold text-gray-500 hover:text-[#1a146b] transition">
                &larr; Selesai dan kembali ke Beranda LapangIn
            </a>
        </div>

    </div>
</div>
@endsection

