@extends('layouts.app')

@section('title', 'Checkout Booking & Pembayaran')

@section('content')
<div class="min-h-screen bg-[#f8f9ff] text-[#0b1c30]"
     style="font-family: 'Plus Jakarta Sans', sans-serif;">

    {{-- Progress Booking (1 baris kompak di HP, lengkap di Desktop) --}}
    <section class="border-b border-[#e5eeff] bg-[#eff4ff] px-4 py-3 sm:py-5 sm:px-8">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
            <div class="flex w-full sm:w-auto items-center justify-between sm:justify-start gap-1.5 sm:gap-3 text-xs sm:text-sm">
                <div class="flex items-center gap-1.5 sm:gap-2 text-[#416900] shrink-0">
                    <span class="flex h-6 w-6 sm:h-7 sm:w-7 items-center justify-center rounded-full bg-[#416900] text-white shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <span class="text-gray-500 line-through whitespace-nowrap"><span class="hidden sm:inline">Pilih </span>Jadwal</span>
                </div>

                <span class="h-0.5 flex-1 sm:flex-none sm:w-8 bg-[#91db2a] rounded-full"></span>

                <div class="flex items-center gap-1.5 sm:gap-2 font-bold text-[#1a146b] shrink-0">
                    <span class="flex h-6 w-6 sm:h-7 sm:w-7 items-center justify-center rounded-full bg-[#1a146b] text-white text-xs sm:text-sm shrink-0">2</span>
                    <span class="whitespace-nowrap">
                        <span class="sm:hidden">Bayar</span>
                        <span class="hidden sm:inline">Pembayaran (<span id="breadcrumb-tipe-text">{{ $tipeBayar === 'full' ? 'Lunas 100%' : 'DP 50%' }}</span>)</span>
                    </span>
                </div>

                <span class="h-0.5 flex-1 sm:flex-none sm:w-8 bg-[#d3e4fe] rounded-full"></span>

                <div class="flex items-center gap-1.5 sm:gap-2 text-gray-400 shrink-0">
                    <span class="flex h-6 w-6 sm:h-7 sm:w-7 items-center justify-center rounded-full bg-[#dce9ff] text-xs sm:text-sm shrink-0">3</span>
                    <span class="whitespace-nowrap">E-Tiket<span class="hidden sm:inline"> Siap</span></span>
                </div>
            </div>

            <span class="hidden md:inline-flex rounded-full bg-[#dce9ff] px-4 py-2 text-xs font-bold text-[#1a146b] shrink-0">
                LAPANGIN · CHECKOUT BOOKING
            </span>
        </div>
    </section>

    <main class="mx-auto max-w-7xl px-4 py-5 sm:px-8 sm:py-8 lg:py-10">

        {{-- Judul halaman --}}
        <div class="mb-4 sm:mb-7">
            <a href="{{ route('katalog.show', $lapangan->id) }}"
               class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#416900] hover:underline">
                ← Kembali ke Lapangan
            </a>

            <h1 class="mt-2.5 sm:mt-4 text-xl font-extrabold tracking-tight text-[#0b1c30] sm:text-3xl leading-snug">
                Checkout Booking &amp; Pembayaran <span id="title-tipe-text">{{ $tipeBayar === 'full' ? 'Lunas (100%)' : 'DP (50%)' }}</span>
            </h1>

            <p class="mt-1.5 sm:mt-2 text-xs sm:text-sm leading-relaxed sm:leading-6 text-[#474651]">
                Periksa detail booking sebelum melanjutkan proses pembayaran resmi via Midtrans Snap.
            </p>
        </div>

        {{-- Banner Timer 10 Menit & Row Locking Slot Sementara --}}
        <div id="countdown-banner" class="mb-5 sm:mb-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 sm:gap-4 rounded-2xl border-2 border-amber-300 bg-amber-50 p-3.5 sm:p-5 shadow-sm">
            <div class="flex items-start sm:items-center gap-3 flex-1 min-w-0">
                <div class="flex h-9 w-9 sm:h-11 sm:w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white shadow-xs mt-0.5 sm:mt-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[11px] sm:text-xs font-black uppercase tracking-wider text-amber-900">
                        Batas Waktu Pembayaran
                    </span>
                    <p class="mt-0.5 text-[11px] sm:text-xs text-amber-800 leading-snug sm:leading-relaxed">
                        Slot waktu ini dikunci eksklusif selama 10 menit. Jika waktu habis, pesanan otomatis dibatalkan &amp; slot dibuka kembali.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end shrink-0">
                <div class="flex w-full sm:w-auto items-center justify-between sm:justify-start gap-2 rounded-xl bg-white px-3.5 py-2 sm:px-4 sm:py-2.5 border border-amber-200 shadow-xs">
                    <span class="text-[11px] sm:text-xs font-semibold text-gray-500">Sisa<span class="sm:hidden"> Waktu</span>:</span>
                    <span id="countdown-display" class="font-mono text-base sm:text-xl font-black text-amber-900">
                        {{ sprintf('%02d:%02d', floor(($sisaDetik ?? 600) / 60), ($sisaDetik ?? 600) % 60) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 items-start gap-5 sm:gap-6 lg:grid-cols-12 lg:gap-8">

            {{-- KOLOM KIRI --}}
            <div class="space-y-5 sm:space-y-6 lg:col-span-7">

                {{-- Informasi Pemesan --}}
                <section class="rounded-2xl border border-[#e8edfa] bg-white p-4 shadow-sm sm:p-6">
                    <div class="mb-4 sm:mb-5 flex items-center justify-between gap-3">
                        <h2 class="flex items-center gap-2 text-base sm:text-lg font-bold text-[#0b1c30]">
                            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-[#eff4ff] text-[#1a146b] shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            </span>
                            Informasi Pemesan
                        </h2>

                        <span class="text-[11px] sm:text-xs font-semibold text-[#416900] whitespace-nowrap">
                            Data Booking
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                        <div class="rounded-xl bg-[#eff4ff] p-3 sm:p-4">
                            <p class="text-[10px] sm:text-xs font-bold uppercase tracking-wide text-[#474651]">
                                Nama Pemesan
                            </p>
                            @if($pelanggan)
                                <p class="mt-1 sm:mt-2 break-words text-xs sm:text-sm font-bold text-[#0b1c30]">
                                    {{ $pelanggan->nama ?? $pelanggan->name ?? 'Belum tersedia' }}
                                </p>
                            @else
                                <input type="text" id="guest_nama" name="guest_nama" placeholder="Contoh: Budi Santoso"
                                    class="mt-1.5 w-full rounded-lg border border-blue-200 bg-white px-2.5 sm:px-3 py-2 text-xs sm:text-sm font-semibold text-[#0b1c30] placeholder-gray-400 focus:border-[#416900] focus:outline-none focus:ring-1 focus:ring-[#416900]" required>
                            @endif
                        </div>

                        <div class="rounded-xl bg-[#eff4ff] p-3 sm:p-4">
                            <p class="text-[10px] sm:text-xs font-bold uppercase tracking-wide text-[#474651]">
                                Nomor WhatsApp
                            </p>
                            @if($pelanggan)
                                <p class="mt-1 sm:mt-2 break-words text-xs sm:text-sm font-bold text-[#0b1c30]">
                                    {{ $pelanggan->no_hp ?? $pelanggan->no_telp ?? 'Belum tersedia' }}
                                </p>
                            @else
                                <input type="tel" id="guest_no_hp" name="guest_no_hp" placeholder="Contoh: 08123456789"
                                    class="mt-1.5 w-full rounded-lg border border-blue-200 bg-white px-2.5 sm:px-3 py-2 text-xs sm:text-sm font-semibold text-[#0b1c30] placeholder-gray-400 focus:border-[#416900] focus:outline-none focus:ring-1 focus:ring-[#416900]" required>
                            @endif
                        </div>

                        <div class="rounded-xl bg-[#eff4ff] p-3 sm:p-4 col-span-2">
                            <p class="text-[10px] sm:text-xs font-bold uppercase tracking-wide text-[#474651]">
                                Lapangan yang Dipilih
                            </p>
                            <p class="mt-1 sm:mt-2 break-words text-xs sm:text-sm font-bold text-[#0b1c30]">
                                {{ $lapangan->nama }}
                            </p>
                        </div>
                    </div>
                </section>

                {{-- PILIHAN METODE PEMBAYARAN (ACCORDION DENGAN GRID CHANNEL PERSIS CONTOH GAMBAR) --}}
                <section class="rounded-2xl border border-[#e8edfa] bg-white p-4 shadow-sm sm:p-6">
                    <div class="mb-3 sm:mb-4 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 sm:gap-2.5 min-w-0">
                            <span class="flex h-6 w-6 sm:h-7 sm:w-7 shrink-0 items-center justify-center rounded-lg bg-[#1a146b] text-xs font-black text-[#acf847]">
                                2
                            </span>
                            <h2 class="text-sm sm:text-base font-extrabold text-[#0b1c30] truncate">
                                Pilih Metode Pembayaran
                            </h2>
                        </div>
                        <span class="rounded-full bg-[#eff4ff] px-2.5 py-1 text-[10px] sm:text-[11px] font-bold text-[#1a146b] whitespace-nowrap shrink-0">
                            Verifikasi Otomatis
                        </span>
                    </div>

                    <p class="mb-5 text-xs text-[#474651]">
                        Pilih kategori pembayaran di bawah ini. Klik kategori untuk membuka pilihan bank atau e-wallet.
                    </p>

                    <div class="space-y-3.5" id="accordion-payment-methods">

                        {{-- 1. QRIS (All E-Wallet & Mobile Banking) --}}
                        <div class="rounded-2xl border border-gray-200/80 bg-[#f8f9fc] overflow-hidden shadow-2xs transition" id="group-qris">
                            {{-- Header Accordion --}}
                            <button type="button" onclick="togglePaymentGroup('qris')"
                                    class="flex w-full flex-col p-4 text-left hover:bg-gray-100/70 transition">
                                <div class="flex w-full items-center justify-between">
                                    <span class="text-sm sm:text-base font-extrabold text-[#0b1c30]">QRIS</span>
                                    <svg id="chevron-qris" class="w-5 h-5 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                                <div class="mt-2.5 flex flex-wrap items-center gap-1.5 opacity-90">
                                    <img src="{{ asset('images/payments/QRIS_logo.svg') }}" alt="QRIS" width="55" height="16" class="h-4 w-auto object-contain shrink-0">
                                </div>
                            </button>

                            {{-- Sub-items Grid (2 kolom di HP & Desktop) --}}
                            <div id="content-qris" class="accordion-panel border-t border-transparent bg-white">
                                <div class="accordion-inner">
                                    <div class="p-3 sm:p-4 pt-2.5 sm:pt-3">
                                        <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                                            {{-- Sub Card: QRIS Instant --}}
                                            <div onclick="selectPaymentChannel('qris', 'QRIS (Semua E-Wallet & M-Banking)')"
                                                 id="channel-box-qris"
                                                 class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 sm:p-3.5 transition-all duration-150 active:scale-[0.97] flex flex-col justify-between shadow-2xs">
                                                <div>
                                                    <div class="h-7 sm:h-8 px-2 rounded-lg bg-white border border-gray-200/80 inline-flex items-center justify-center max-w-full">
                                                        <img src="{{ asset('images/payments/QRIS_logo.svg') }}"
                                                             alt="QRIS"
                                                             width="60"
                                                             height="20"
                                                             class="h-4 sm:h-5 w-auto max-w-full object-contain"
                                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                        <span style="display:none;" class="font-black text-xs text-slate-900">QRIS</span>
                                                    </div>
                                                    <p class="channel-nominal-display mt-2.5 text-xs sm:text-sm font-black text-[#1a146b]">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                                </div>
                                                <div class="mt-2 pt-2 border-t border-dashed border-gray-200">
                                                    <p class="text-[11px] sm:text-xs font-extrabold text-[#0b1c30] truncate">QRIS</p>
                                                    <p class="text-[10px] text-[#416900] font-semibold truncate">E-Wallet &amp; M-Banking</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Virtual Account (Transfer Bank) --}}
                        <div class="rounded-2xl border border-gray-200/80 bg-[#f8f9fc] overflow-hidden shadow-2xs transition" id="group-va">
                            {{-- Header Accordion --}}
                            <button type="button" onclick="togglePaymentGroup('va')"
                                    class="flex w-full flex-col p-4 text-left hover:bg-gray-100/70 transition">
                                <div class="flex w-full items-center justify-between">
                                    <span class="text-sm sm:text-base font-extrabold text-[#0b1c30]">Virtual Account</span>
                                    <svg id="chevron-va" class="w-5 h-5 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                                <div class="mt-2.5 flex flex-wrap items-center gap-2 opacity-90">
                                    <img src="{{ asset('images/payments/Bank_Central_Asia.svg') }}" alt="BCA" width="44" height="14" class="h-3.5 w-auto object-contain shrink-0">
                                    <img src="{{ asset('images/payments/Bank_Mandiri_logo.svg') }}" alt="Mandiri" width="40" height="12" class="h-3 w-auto object-contain shrink-0">
                                    <img src="{{ asset('images/payments/BANK_BRI_logo.svg') }}" alt="BRI" width="40" height="12" class="h-3 w-auto object-contain shrink-0">
                                    <img src="{{ asset('images/payments/Bank_Negara_Indonesia_logo.svg') }}" alt="BNI" width="38" height="12" class="h-3 w-auto object-contain shrink-0">
                                    <img src="{{ asset('images/payments/Permata_Bank.svg') }}" alt="Permata" width="45" height="12" class="h-3 w-auto object-contain shrink-0">
                                    <img src="{{ asset('images/payments/CIMB_Niaga_logo.svg') }}" alt="CIMB" width="50" height="12" class="h-3 w-auto object-contain shrink-0">
                                    <img src="{{ asset('images/payments/Danamon.svg') }}" alt="Danamon" width="50" height="12" class="h-3 w-auto object-contain shrink-0">
                                    <img src="{{ asset('images/payments/Bank_Syariah_Indonesia.svg') }}" alt="BSI" width="45" height="12" class="h-3 w-auto object-contain shrink-0">
                                    <img src="{{ asset('images/payments/SeaBank.svg') }}" alt="SeaBank" width="42" height="12" class="h-3 w-auto object-contain shrink-0">
                                    <img src="{{ asset('images/payments/Bank_Saqu.svg') }}" alt="Bank Saqu" width="42" height="12" class="h-3 w-auto object-contain shrink-0">
                                </div>
                            </button>

                            {{-- Sub-items Grid (2 kolom di HP, 3 kolom di Desktop) --}}
                            <div id="content-va" class="accordion-panel border-t border-transparent bg-white">
                                <div class="accordion-inner">
                                    <div class="p-3 sm:p-4 pt-2.5 sm:pt-3">
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2.5 sm:gap-3">
                                    {{-- 1. BCA VA --}}
                                    <div onclick="selectPaymentChannel('bca_va', 'BCA Virtual Account')"
                                         id="channel-box-bca_va"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 transition-all duration-150 active:scale-[0.97] flex flex-col justify-between shadow-2xs">
                                        <div>
                                            <div class="h-7 px-2 rounded-lg bg-white border border-gray-200/80 inline-flex items-center justify-center max-w-full">
                                                <img src="{{ asset('images/payments/Bank_Central_Asia.svg') }}"
                                                     alt="BCA"
                                                     class="h-3.5 sm:h-4 w-auto max-w-full object-contain"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                <span style="display:none;" class="text-xs font-black text-[#0060af]">BCA</span>
                                            </div>
                                            <p class="channel-nominal-display mt-2.5 text-xs sm:text-sm font-black text-[#1a146b]">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="mt-2 pt-1.5 border-t border-dashed border-gray-200 flex items-center justify-between gap-1">
                                            <p class="text-[11px] sm:text-xs font-extrabold text-[#0b1c30] truncate">BCA VA</p>
                                            <span class="text-[9px] sm:text-[10px] font-bold text-[#416900] shrink-0">Otomatis</span>
                                        </div>
                                    </div>

                                    {{-- 2. BRI VA --}}
                                    <div onclick="selectPaymentChannel('bri_va', 'BRI Virtual Account')"
                                         id="channel-box-bri_va"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 transition-all duration-150 active:scale-[0.97] flex flex-col justify-between shadow-2xs">
                                        <div>
                                            <div class="h-7 px-2 rounded-lg bg-white border border-gray-200/80 inline-flex items-center justify-center max-w-full">
                                                <img src="{{ asset('images/payments/BANK_BRI_logo.svg') }}"
                                                     alt="BRI"
                                                     class="h-3.5 sm:h-4 w-auto max-w-full object-contain"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                <span style="display:none;" class="text-xs font-black text-[#00529c]">BRI</span>
                                            </div>
                                            <p class="channel-nominal-display mt-2.5 text-xs sm:text-sm font-black text-[#1a146b]">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="mt-2 pt-1.5 border-t border-dashed border-gray-200 flex items-center justify-between gap-1">
                                            <p class="text-[11px] sm:text-xs font-extrabold text-[#0b1c30] truncate">BRI VA</p>
                                            <span class="text-[9px] sm:text-[10px] font-bold text-[#416900] shrink-0">Otomatis</span>
                                        </div>
                                    </div>

                                    {{-- 3. BNI VA --}}
                                    <div onclick="selectPaymentChannel('bni_va', 'BNI Virtual Account')"
                                         id="channel-box-bni_va"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 transition-all duration-150 active:scale-[0.97] flex flex-col justify-between shadow-2xs">
                                        <div>
                                            <div class="h-7 px-2 rounded-lg bg-white border border-gray-200/80 inline-flex items-center justify-center max-w-full">
                                                <img src="{{ asset('images/payments/Bank_Negara_Indonesia_logo.svg') }}"
                                                     alt="BNI"
                                                     class="h-3.5 sm:h-4 w-auto max-w-full object-contain"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                <span style="display:none;" class="text-xs font-black text-[#e05828]">BNI</span>
                                            </div>
                                            <p class="channel-nominal-display mt-2.5 text-xs sm:text-sm font-black text-[#1a146b]">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="mt-2 pt-1.5 border-t border-dashed border-gray-200 flex items-center justify-between gap-1">
                                            <p class="text-[11px] sm:text-xs font-extrabold text-[#0b1c30] truncate">BNI VA</p>
                                            <span class="text-[9px] sm:text-[10px] font-bold text-[#416900] shrink-0">Otomatis</span>
                                        </div>
                                    </div>

                                    {{-- 4. Permata VA --}}
                                    <div onclick="selectPaymentChannel('permata_va', 'Permata Virtual Account')"
                                         id="channel-box-permata_va"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 transition-all duration-150 active:scale-[0.97] flex flex-col justify-between shadow-2xs">
                                        <div>
                                            <div class="h-7 px-2 rounded-lg bg-white border border-gray-200/80 inline-flex items-center justify-center max-w-full">
                                                <img src="{{ asset('images/payments/Permata_Bank.svg') }}"
                                                     alt="Permata"
                                                     class="h-3.5 sm:h-4 w-auto max-w-full object-contain"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                <span style="display:none;" class="text-xs font-black text-[#1a7f37]">PERMATA</span>
                                            </div>
                                            <p class="channel-nominal-display mt-2.5 text-xs sm:text-sm font-black text-[#1a146b]">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="mt-2 pt-1.5 border-t border-dashed border-gray-200 flex items-center justify-between gap-1">
                                            <p class="text-[11px] sm:text-xs font-extrabold text-[#0b1c30] truncate">Permata VA</p>
                                            <span class="text-[9px] sm:text-[10px] font-bold text-[#416900] shrink-0">Otomatis</span>
                                        </div>
                                    </div>

                                    {{-- 5. CIMB VA --}}
                                    <div onclick="selectPaymentChannel('cimb_va', 'CIMB Niaga Virtual Account')"
                                         id="channel-box-cimb_va"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 transition-all duration-150 active:scale-[0.97] flex flex-col justify-between shadow-2xs">
                                        <div>
                                            <div class="h-7 px-2 rounded-lg bg-white border border-gray-200/80 inline-flex items-center justify-center max-w-full">
                                                <img src="{{ asset('images/payments/CIMB_Niaga_logo.svg') }}"
                                                     alt="CIMB Niaga"
                                                     class="h-3.5 sm:h-4 w-auto max-w-full object-contain"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                <span style="display:none;" class="text-xs font-black text-[#7a0019]">CIMB NIAGA</span>
                                            </div>
                                            <p class="channel-nominal-display mt-2.5 text-xs sm:text-sm font-black text-[#1a146b]">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="mt-2 pt-1.5 border-t border-dashed border-gray-200 flex items-center justify-between gap-1">
                                            <p class="text-[11px] sm:text-xs font-extrabold text-[#0b1c30] truncate">CIMB VA</p>
                                            <span class="text-[9px] sm:text-[10px] font-bold text-[#416900] shrink-0">Otomatis</span>
                                        </div>
                                    </div>

                                    {{-- 6. Mandiri Bill --}}
                                    <div onclick="selectPaymentChannel('mandiri_va', 'Mandiri Bill Payment')"
                                         id="channel-box-mandiri_va"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 transition-all duration-150 active:scale-[0.97] flex flex-col justify-between shadow-2xs">
                                        <div>
                                            <div class="h-7 px-2 rounded-lg bg-white border border-gray-200/80 inline-flex items-center justify-center max-w-full">
                                                <img src="{{ asset('images/payments/Bank_Mandiri_logo.svg') }}"
                                                     alt="Mandiri"
                                                     class="h-3.5 sm:h-4 w-auto max-w-full object-contain"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                <span style="display:none;" class="text-xs font-black text-[#003d79]">MANDIRI</span>
                                            </div>
                                            <p class="channel-nominal-display mt-2.5 text-xs sm:text-sm font-black text-[#1a146b]">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="mt-2 pt-1.5 border-t border-dashed border-gray-200 flex items-center justify-between gap-1">
                                            <p class="text-[11px] sm:text-xs font-extrabold text-[#0b1c30] truncate">Mandiri Bill</p>
                                            <span class="text-[9px] sm:text-[10px] font-bold text-[#416900] shrink-0">Otomatis</span>
                                        </div>
                                    </div>

                                    {{-- 7. Danamon VA --}}
                                    <div onclick="selectPaymentChannel('danamon_va', 'Danamon Virtual Account')"
                                         id="channel-box-danamon_va"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 transition-all duration-150 active:scale-[0.97] flex flex-col justify-between shadow-2xs">
                                        <div>
                                            <div class="h-7 px-2 rounded-lg bg-white border border-gray-200/80 inline-flex items-center justify-center max-w-full">
                                                <img src="{{ asset('images/payments/Danamon.svg') }}"
                                                     alt="Danamon"
                                                     class="h-3.5 sm:h-4 w-auto max-w-full object-contain"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                <span style="display:none;" class="text-xs font-black text-[#e87722]">DANAMON</span>
                                            </div>
                                            <p class="channel-nominal-display mt-2.5 text-xs sm:text-sm font-black text-[#1a146b]">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="mt-2 pt-1.5 border-t border-dashed border-gray-200 flex items-center justify-between gap-1">
                                            <p class="text-[11px] sm:text-xs font-extrabold text-[#0b1c30] truncate">Danamon VA</p>
                                            <span class="text-[9px] sm:text-[10px] font-bold text-[#416900] shrink-0">Otomatis</span>
                                        </div>
                                    </div>

                                    {{-- 8. BSI VA --}}
                                    <div onclick="selectPaymentChannel('bsi_va', 'BSI Virtual Account')"
                                         id="channel-box-bsi_va"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 transition-all duration-150 active:scale-[0.97] flex flex-col justify-between shadow-2xs">
                                        <div>
                                            <div class="h-7 px-2 rounded-lg bg-white border border-gray-200/80 inline-flex items-center justify-center max-w-full">
                                                <img src="{{ asset('images/payments/Bank_Syariah_Indonesia.svg') }}"
                                                     alt="BSI"
                                                     class="h-3.5 sm:h-4 w-auto max-w-full object-contain"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                <span style="display:none;" class="text-xs font-black text-[#00a39d]">BSI</span>
                                            </div>
                                            <p class="channel-nominal-display mt-2.5 text-xs sm:text-sm font-black text-[#1a146b]">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="mt-2 pt-1.5 border-t border-dashed border-gray-200 flex items-center justify-between gap-1">
                                            <p class="text-[11px] sm:text-xs font-extrabold text-[#0b1c30] truncate">BSI VA</p>
                                            <span class="text-[9px] sm:text-[10px] font-bold text-[#416900] shrink-0">Otomatis</span>
                                        </div>
                                    </div>

                                    {{-- 9. SeaBank VA --}}
                                    <div onclick="selectPaymentChannel('seabank_va', 'SeaBank Virtual Account')"
                                         id="channel-box-seabank_va"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 transition-all duration-150 active:scale-[0.97] flex flex-col justify-between shadow-2xs">
                                        <div>
                                            <div class="h-7 px-2 rounded-lg bg-white border border-gray-200/80 inline-flex items-center justify-center max-w-full">
                                                <img src="{{ asset('images/payments/SeaBank.svg') }}"
                                                     alt="SeaBank"
                                                     class="h-3.5 sm:h-4 w-auto max-w-full object-contain"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                <span style="display:none;" class="text-xs font-black text-[#ff5722]">SEABANK</span>
                                            </div>
                                            <p class="channel-nominal-display mt-2.5 text-xs sm:text-sm font-black text-[#1a146b]">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="mt-2 pt-1.5 border-t border-dashed border-gray-200 flex items-center justify-between gap-1">
                                            <p class="text-[11px] sm:text-xs font-extrabold text-[#0b1c30] truncate">SeaBank VA</p>
                                            <span class="text-[9px] sm:text-[10px] font-bold text-[#416900] shrink-0">Otomatis</span>
                                        </div>
                                    </div>

                                    {{-- 10. Saqu VA (Bank Saqu) --}}
                                    <div onclick="selectPaymentChannel('saqu_va', 'Bank Saqu Virtual Account')"
                                         id="channel-box-saqu_va"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 transition-all duration-150 active:scale-[0.97] flex flex-col justify-between shadow-2xs">
                                        <div>
                                            <div class="h-7 px-2 rounded-lg bg-white border border-gray-200/80 inline-flex items-center justify-center max-w-full">
                                                <img src="{{ asset('images/payments/Bank_Saqu.svg') }}"
                                                     alt="Bank Saqu"
                                                     class="h-3.5 sm:h-4 w-auto max-w-full object-contain"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                <span style="display:none;" class="text-xs font-black text-[#6c5ce7]">BANK SAQU</span>
                                            </div>
                                            <p class="channel-nominal-display mt-2.5 text-xs sm:text-sm font-black text-[#1a146b]">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="mt-2 pt-1.5 border-t border-dashed border-gray-200 flex items-center justify-between gap-1">
                                            <p class="text-[11px] sm:text-xs font-extrabold text-[#0b1c30] truncate">Saqu VA</p>
                                            <span class="text-[9px] sm:text-[10px] font-bold text-[#416900] shrink-0">Otomatis</span>
                                        </div>
                                    </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Convenience Store (Indomaret & Alfamart) --}}
                        <div class="rounded-2xl border border-gray-200/80 bg-[#f8f9fc] overflow-hidden shadow-2xs transition" id="group-cstore">
                            {{-- Header Accordion --}}
                            <button type="button" onclick="togglePaymentGroup('cstore')"
                                    class="flex w-full flex-col p-3.5 sm:p-4 text-left hover:bg-gray-100/70 transition cursor-pointer">
                                <div class="flex w-full items-center justify-between">
                                    <span class="text-sm sm:text-base font-extrabold text-[#0b1c30]">Convenience Store</span>
                                    <svg id="chevron-cstore" class="w-5 h-5 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                                <div class="mt-2 flex flex-wrap items-center gap-2 opacity-90">
                                    <img src="{{ asset('images/payments/Indomaret.svg') }}" alt="Indomaret" width="55" height="16" class="h-4 w-auto object-contain shrink-0">
                                    <img src="{{ asset('images/payments/Alfamart_logo.svg') }}" alt="Alfamart" width="55" height="16" class="h-4 w-auto object-contain shrink-0">
                                </div>
                            </button>

                            {{-- Sub-items Grid (2 kolom sejajar di HP & Desktop) --}}
                            <div id="content-cstore" class="accordion-panel border-t border-transparent bg-white">
                                <div class="accordion-inner">
                                    <div class="p-3 sm:p-4 pt-2.5 sm:pt-3">
                                        <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                                            {{-- Indomaret --}}
                                            <div onclick="selectPaymentChannel('indomaret', 'Indomaret')"
                                                 id="channel-box-indomaret"
                                                 class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 sm:p-3.5 transition-all duration-150 active:scale-[0.97] flex flex-col justify-between shadow-2xs">
                                                <div>
                                                    <div class="h-7 sm:h-8 px-2 rounded-lg border border-gray-200/80 bg-white inline-flex items-center justify-center max-w-full">
                                                        <img src="{{ asset('images/payments/Indomaret.svg') }}"
                                                             alt="Indomaret"
                                                             class="h-4 sm:h-5 w-auto max-w-full object-contain"
                                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                        <span style="display:none;" class="text-xs font-black text-blue-700">INDOMARET</span>
                                                    </div>
                                                    <p class="channel-nominal-display mt-2.5 text-xs sm:text-sm font-black text-[#1a146b]">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                                </div>
                                                <div class="mt-2 pt-2 border-t border-dashed border-gray-200">
                                                    <p class="text-[11px] sm:text-xs font-extrabold text-[#0b1c30] truncate">Indomaret</p>
                                                    <p class="text-[10px] text-[#416900] font-semibold truncate">Bayar di Kasir</p>
                                                </div>
                                            </div>

                                            {{-- Alfamart --}}
                                            <div onclick="selectPaymentChannel('alfamart', 'Alfamart')"
                                                 id="channel-box-alfamart"
                                                 class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 sm:p-3.5 transition-all duration-150 active:scale-[0.97] flex flex-col justify-between shadow-2xs">
                                                <div>
                                                    <div class="h-7 sm:h-8 px-2 rounded-lg border border-gray-200/80 bg-white inline-flex items-center justify-center max-w-full">
                                                        <img src="{{ asset('images/payments/Alfamart_logo.svg') }}"
                                                             alt="Alfamart"
                                                             class="h-4 sm:h-5 w-auto max-w-full object-contain"
                                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                        <span style="display:none;" class="text-xs font-black text-red-600">ALFAMART</span>
                                                    </div>
                                                    <p class="channel-nominal-display mt-2.5 text-xs sm:text-sm font-black text-[#1a146b]">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                                </div>
                                                <div class="mt-2 pt-2 border-t border-dashed border-gray-200">
                                                    <p class="text-[11px] sm:text-xs font-extrabold text-[#0b1c30] truncate">Alfamart / Alfamidi</p>
                                                    <p class="text-[10px] text-[#416900] font-semibold truncate">Bayar di Kasir</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>
            </div>

            {{-- KOLOM KANAN: CARD RINGKASAN PEMESANAN (STICKY DENGAN JARAK DARI NAVBAR) --}}
            <aside class="space-y-4 lg:sticky lg:top-28 lg:col-span-5" style="position: sticky; top: 110px; height: fit-content;">

                {{-- Card Ringkasan Pemesanan Utama (Sesuai Referensi Gambar 2) --}}
                <section id="checkout-summary-card" class="overflow-hidden rounded-2xl border border-[#e8edfa] bg-white shadow-md">
                    <div class="p-5 sm:p-6">

                        {{-- Item Header: Thumbnail + Nama Lapangan + Venue --}}
                        <div class="flex items-center gap-3.5 pb-4 border-b border-gray-100">
                            @php
                                $thumbUrl = $lapangan->foto_thumbnail_url ?? $lapangan->foto_url;
                                $fallbackThumb = 'https://ui-avatars.com/api/?name=' . urlencode($lapangan->nama) . '&background=1a146b&color=acf847&size=256';
                                $kategoriLabel = is_object($lapangan->kategori) ? ($lapangan->kategori->nama ?? 'Olahraga') : ($lapangan->kategori ?: 'Olahraga');
                            @endphp
                            <img src="{{ $thumbUrl }}"
                                 alt="{{ $lapangan->nama }}"
                                 width="56"
                                 height="56"
                                 class="h-14 w-14 rounded-xl object-cover border border-gray-100 shadow-2xs shrink-0"
                                 onerror="this.onerror=null; this.src='{{ $fallbackThumb }}'">
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate text-sm font-extrabold text-[#0b1c30]">
                                    {{ $lapangan->nama }}
                                </h3>
                                <p class="text-xs font-semibold text-[#416900] mt-0.5 truncate">
                                    {{ $kategoriLabel }} &bull; {{ $lapangan->lokasi ?? 'Venue Mitra' }}
                                </p>
                            </div>
                        </div>

                        {{-- Pilihan Skema Pembayaran (DP 50% vs Lunas 100%) --}}
                        <div class="py-4 border-b border-gray-100">
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#474651] mb-2.5">
                                Skema Pembayaran
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                {{-- Opsi A: Bayar DP 50% --}}
                                <button type="button"
                                        id="card-tipe-dp"
                                        onclick="setTipeBayar('dp')"
                                        class="text-left flex flex-col justify-between p-3 rounded-xl border-2 transition-all cursor-pointer {{ $tipeBayar === 'dp' ? 'border-[#1a146b] bg-[#eff4ff] shadow-xs ring-1 ring-[#1a146b]' : 'border-[#e8edfa] bg-white hover:border-[#cbd5e1]' }}">
                                    <div class="flex items-center justify-between">
                                        <span id="label-tipe-dp" class="text-[11px] font-bold uppercase tracking-wide {{ $tipeBayar === 'dp' ? 'text-[#1a146b]' : 'text-[#474651]' }}">
                                            DP 50% <span class="sr-only">Bayar DP (50%)</span>
                                        </span>
                                        <span id="radio-tipe-dp" class="h-3.5 w-3.5 rounded-full border-2 flex items-center justify-center {{ $tipeBayar === 'dp' ? 'border-[#1a146b] bg-[#1a146b]' : 'border-gray-300' }}">
                                            @if($tipeBayar === 'dp')
                                                <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                                            @endif
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm font-black text-[#0b1c30]">
                                        Rp{{ number_format($nominalDp, 0, ',', '.') }}
                                    </p>
                                    <p class="text-[10px] text-gray-500 mt-0.5">Sisa di kasir</p>
                                </button>

                                {{-- Opsi B: Bayar Lunas 100% --}}
                                <button type="button"
                                        id="card-tipe-full"
                                        onclick="setTipeBayar('full')"
                                        class="text-left flex flex-col justify-between p-3 rounded-xl border-2 transition-all cursor-pointer {{ $tipeBayar === 'full' ? 'border-[#1a146b] bg-[#eff4ff] shadow-xs ring-1 ring-[#1a146b]' : 'border-[#e8edfa] bg-white hover:border-[#cbd5e1]' }}">
                                    <div class="flex items-center justify-between">
                                        <span id="label-tipe-full" class="text-[11px] font-bold uppercase tracking-wide {{ $tipeBayar === 'full' ? 'text-[#1a146b]' : 'text-[#474651]' }}">
                                            Lunas 100% <span class="sr-only">Bayar Lunas (100%)</span>
                                        </span>
                                        <span id="radio-tipe-full" class="h-3.5 w-3.5 rounded-full border-2 flex items-center justify-center {{ $tipeBayar === 'full' ? 'border-[#1a146b] bg-[#1a146b]' : 'border-gray-300' }}">
                                            @if($tipeBayar === 'full')
                                                <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                                            @endif
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm font-black text-[#0b1c30]">
                                        Rp{{ number_format($totalHarga, 0, ',', '.') }}
                                    </p>
                                    <p class="text-[10px] text-[#416900] font-semibold mt-0.5">Bebas antre</p>
                                </button>
                            </div>
                        </div>

                        {{-- Rincian Biaya Sesuai Format Referensi (Harga, Jumlah/Durasi, Biaya, Total Pembayaran) --}}
                        <div class="py-4 space-y-3 text-xs">
                            <div class="flex justify-between items-center text-[#474651]">
                                <span>Jadwal Main</span>
                                <span class="font-bold text-[#0b1c30]">
                                    {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }} &bull; {{ $jamMulai }}–{{ $jamSelesai }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center text-[#474651]">
                                <span>Harga Sewa Lapangan</span>
                                <span class="font-semibold text-[#0b1c30]">
                                    Rp{{ number_format($totalHarga, 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center text-[#474651]">
                                <span>Durasi Pemesanan</span>
                                <span class="font-semibold text-[#0b1c30]">
                                    {{ $durasi }} Jam
                                </span>
                            </div>

                            <div class="flex justify-between items-center text-[#474651]">
                                <span>Biaya Layanan</span>
                                <span class="font-bold text-[#416900]">
                                    Rp0
                                </span>
                            </div>

                            {{-- Info Sisa Tagihan Kasir Jika DP --}}
                            <div class="flex justify-between items-center text-[#474651]">
                                <span id="sidebar-status-label">{{ $tipeBayar === 'full' ? 'Sisa di Kasir' : 'Sisa Bayar di Kasir' }}</span>
                                <span id="sidebar-status-val" class="font-bold {{ $tipeBayar === 'full' ? 'text-[#416900]' : 'text-amber-600' }}">
                                    {{ $tipeBayar === 'full' ? 'Rp0 (Lunas)' : 'Rp' . number_format($sisaPembayaran, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-bold text-[#0b1c30] block">
                                        Total Pembayaran
                                    </span>
                                    <span id="sidebar-panel-title" class="text-[10px] text-[#777682]">
                                        {{ $tipeBayar === 'full' ? 'Pembayaran Lunas 100%' : 'DP yang Dibayar Sekarang' }}
                                    </span>
                                </div>
                                <span id="sidebar-panel-amount" class="text-xl sm:text-2xl font-black text-[#1a146b]">
                                    Rp{{ number_format($nominalBayar, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        {{-- Hidden elements for backward compatibility JS updates --}}
                        <span id="section-badge-tipe" class="hidden">{{ $tipeBayar === 'full' ? 'Lunas 100%' : 'DP 50%' }}</span>
                        <span id="sidebar-panel-badge" class="hidden">{{ $tipeBayar === 'full' ? '100% LUNAS' : '50% DP' }}</span>
                        <p id="sidebar-status-note" class="hidden"></p>

                        {{-- Tombol Aksi Utama: Pesan Sekarang! (Sesuai Referensi Gambar 2) --}}
                        <button id="pay-button"
                                type="button"
                                class="mt-5 flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#1a146b] px-6 py-4 text-base font-extrabold text-white shadow-md transition hover:bg-[#130d52] active:scale-[0.99]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <span id="pay-button-text">Pesan Sekarang!</span>
                            <span class="sr-only">Bayar Sekarang</span>
                        </button>
                    </div>
                </section>
            </aside>
        </div>
    </main>

    {{-- Modal Pop-up Custom Bertema LapangIn --}}
    <div id="theme-alert-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <!-- Backdrop Blur -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeNotificationModal()"></div>

        <!-- Modal Dialog Card -->
        <div class="relative w-full max-w-sm sm:max-w-md transform overflow-hidden rounded-3xl bg-white p-6 sm:p-7 text-center shadow-2xl border border-[#e5eeff] transition-all z-10 animate-in fade-in zoom-in-95">
            <!-- Close Button (X) -->
            <button type="button" onclick="closeNotificationModal()"
                    class="absolute right-4 top-4 p-2 text-gray-400 hover:text-gray-600 rounded-xl hover:bg-gray-100 transition">
                <svg class="w-5 h-5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Dynamic Icon Container -->
            <div class="mx-auto mb-4 flex items-center justify-center">
                <!-- Warning Icon -->
                <div id="theme-alert-icon-warning" class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center shadow-xs">
                    <svg class="w-7 h-7 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <!-- Error Icon -->
                <div id="theme-alert-icon-error" class="hidden w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center shadow-xs">
                    <svg class="w-7 h-7 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <!-- Info Icon -->
                <div id="theme-alert-icon-info" class="hidden w-14 h-14 rounded-2xl bg-indigo-50 text-[#1a146b] border border-indigo-200 flex items-center justify-center shadow-xs">
                    <svg class="w-7 h-7 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                </div>
            </div>

            <!-- Title & Message -->
            <h3 id="theme-alert-title" class="text-base sm:text-lg font-black text-[#0b1c30] mb-2 font-headline">
                Perhatian
            </h3>
            <p id="theme-alert-message" class="text-xs sm:text-sm text-[#474651] leading-relaxed mb-6 font-medium">
                Pesan notifikasi
            </p>

            <!-- Action Button -->
            <button type="button" id="theme-alert-btn" onclick="closeNotificationModal()"
                    class="w-full py-3.5 px-6 rounded-xl bg-[#1a146b] hover:bg-[#130d52] active:scale-[0.99] text-white font-headline font-bold text-xs sm:text-sm shadow-md transition-all cursor-pointer">
                Mengerti &amp; Lengkapi Data
            </button>
        </div>
    </div>
</div>

{{-- Integrasi Script Midtrans Snap SDK & Timer Countdown 10 Menit --}}
<script src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
    // Konfigurasi dinamis Tipe Pembayaran (DP vs Full) tanpa refresh halaman
    const totalHarga = {{ (float) $totalHarga }};
    const nominalDp = {{ (float) $nominalDp }};
    window.currentTipeBayar = "{{ $tipeBayar }}";
    window.selectedPaymentMethod = null;
    window.selectedPaymentLabel = null;
    window.currentModalCallback = null;

    // Toggle Accordion Group (Animasi smooth CSS Grid 0fr -> 1fr)
    window.togglePaymentGroup = function(groupId) {
        const content = document.getElementById('content-' + groupId);
        const chevron = document.getElementById('chevron-' + groupId);
        if (!content) return;

        const isOpen = content.classList.contains('is-open');
        if (!isOpen) {
            content.classList.add('is-open', 'border-gray-200/60');
            content.classList.remove('border-transparent');
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        } else {
            content.classList.remove('is-open', 'border-gray-200/60');
            content.classList.add('border-transparent');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }
    };

    // Pilih spesifik Channel Pembayaran di dalam Grid/Accordion
    window.selectPaymentChannel = function(methodCode, methodLabel) {
        window.selectedPaymentMethod = methodCode;
        window.selectedPaymentLabel = methodLabel;

        // Reset styling semua sub-card channel
        document.querySelectorAll('.payment-channel-card').forEach(card => {
            card.classList.remove('border-2', 'border-[#1a146b]', 'bg-[#eff4ff]', 'shadow-xs', 'ring-1', 'ring-[#1a146b]', 'animate-select-pop');
            card.classList.add('border-[#e8edfa]', 'bg-white');
        });

        // Highlight kartu channel yang dipilih dengan pop halus
        const activeCard = document.getElementById('channel-box-' + methodCode);
        if (activeCard) {
            activeCard.classList.remove('border-[#e8edfa]', 'bg-white');
            activeCard.classList.add('border-2', 'border-[#1a146b]', 'bg-[#eff4ff]', 'shadow-xs', 'ring-1', 'ring-[#1a146b]', 'animate-select-pop');
        }

        // Otomatis scroll halus ke bagian atas kartu Ringkasan Pemesanan
        const summaryCard = document.getElementById('checkout-summary-card');
        if (summaryCard) {
            const rect = summaryCard.getBoundingClientRect();
            const isCardVisible = rect.top >= 76 && rect.bottom <= window.innerHeight;
            if (!isCardVisible) {
                setTimeout(() => {
                    const targetTop = summaryCard.getBoundingClientRect().top + window.scrollY - 92;
                    window.scrollTo({ top: Math.max(0, targetTop), behavior: 'smooth' });
                }, 120);
            }
        }
    };

    window.showNotificationModal = function({
        title = 'Perhatian',
        message = '',
        type = 'warning',
        confirmText = 'Mengerti',
        onConfirm = null
    }) {
        const modal = document.getElementById('theme-alert-modal');
        const titleEl = document.getElementById('theme-alert-title');
        const msgEl = document.getElementById('theme-alert-message');
        const iconWarning = document.getElementById('theme-alert-icon-warning');
        const iconError = document.getElementById('theme-alert-icon-error');
        const iconInfo = document.getElementById('theme-alert-icon-info');
        const btn = document.getElementById('theme-alert-btn');

        if (!modal) {
            alert(message);
            if (typeof onConfirm === 'function') onConfirm();
            return;
        }

        if (titleEl) titleEl.textContent = title;
        if (msgEl) msgEl.textContent = message;
        if (btn) btn.textContent = confirmText;

        if (iconWarning) iconWarning.classList.add('hidden');
        if (iconError) iconError.classList.add('hidden');
        if (iconInfo) iconInfo.classList.add('hidden');

        if (type === 'error' && iconError) {
            iconError.classList.remove('hidden');
        } else if (type === 'info' && iconInfo) {
            iconInfo.classList.remove('hidden');
        } else if (iconWarning) {
            iconWarning.classList.remove('hidden');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        window.currentModalCallback = onConfirm;
    };

    window.closeNotificationModal = function() {
        const modal = document.getElementById('theme-alert-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        if (typeof window.currentModalCallback === 'function') {
            const cb = window.currentModalCallback;
            window.currentModalCallback = null;
            cb();
        }
    };

    function formatRupiah(num) {
        return 'Rp' + new Intl.NumberFormat('id-ID').format(num);
    }

    window.setTipeBayar = function(type) {
        if (window.currentTipeBayar === type) return;
        window.currentTipeBayar = type;

        // Perbarui query parameter di URL secara halus tanpa reload browser
        try {
            const url = new URL(window.location.href);
            url.searchParams.set('tipe_bayar', type);
            window.history.replaceState({}, '', url);
        } catch (e) {}

        const isFull = (type === 'full');
        const nominal = isFull ? totalHarga : nominalDp;
        const sisa = isFull ? 0 : (totalHarga - nominalDp);

        // 1. Breadcrumb, Judul, dan Badge
        const breadcrumb = document.getElementById('breadcrumb-tipe-text');
        if (breadcrumb) breadcrumb.textContent = isFull ? 'Lunas 100%' : 'DP 50%';

        const titleTipe = document.getElementById('title-tipe-text');
        if (titleTipe) titleTipe.textContent = isFull ? 'Lunas (100%)' : 'DP (50%)';

        const sectionBadge = document.getElementById('section-badge-tipe');
        if (sectionBadge) sectionBadge.textContent = isFull ? 'Lunas 100%' : 'DP 50%';

        // 2. Styling Tombol Radio Card
        const btnDp = document.getElementById('card-tipe-dp');
        const btnFull = document.getElementById('card-tipe-full');
        const radioDp = document.getElementById('radio-tipe-dp');
        const radioFull = document.getElementById('radio-tipe-full');
        const textDp = document.getElementById('label-tipe-dp');
        const textFull = document.getElementById('label-tipe-full');

        const activeClasses = ['border-[#1a146b]', 'bg-[#eff4ff]', 'shadow-sm', 'ring-1', 'ring-[#1a146b]'];
        const inactiveClasses = ['border-[#e8edfa]', 'bg-white'];

        if (isFull) {
            if (btnFull) {
                btnFull.classList.add(...activeClasses);
                btnFull.classList.remove(...inactiveClasses);
            }
            if (btnDp) {
                btnDp.classList.remove(...activeClasses);
                btnDp.classList.add(...inactiveClasses);
            }
            if (radioFull) {
                radioFull.className = 'h-4 w-4 rounded-full border-2 flex items-center justify-center border-[#1a146b] bg-[#1a146b]';
                radioFull.innerHTML = '<span class="h-1.5 w-1.5 rounded-full bg-white"></span>';
            }
            if (radioDp) {
                radioDp.className = 'h-4 w-4 rounded-full border-2 flex items-center justify-center border-gray-300';
                radioDp.innerHTML = '';
            }
            if (textFull) textFull.className = 'text-xs font-bold uppercase tracking-wide text-[#1a146b]';
            if (textDp) textDp.className = 'text-xs font-bold uppercase tracking-wide text-[#474651]';
        } else {
            if (btnDp) {
                btnDp.classList.add(...activeClasses);
                btnDp.classList.remove(...inactiveClasses);
            }
            if (btnFull) {
                btnFull.classList.remove(...activeClasses);
                btnFull.classList.add(...inactiveClasses);
            }
            if (radioDp) {
                radioDp.className = 'h-4 w-4 rounded-full border-2 flex items-center justify-center border-[#1a146b] bg-[#1a146b]';
                radioDp.innerHTML = '<span class="h-1.5 w-1.5 rounded-full bg-white"></span>';
            }
            if (radioFull) {
                radioFull.className = 'h-4 w-4 rounded-full border-2 flex items-center justify-center border-gray-300';
                radioFull.innerHTML = '';
            }
            if (textDp) textDp.className = 'text-xs font-bold uppercase tracking-wide text-[#1a146b]';
            if (textFull) textFull.className = 'text-xs font-bold uppercase tracking-wide text-[#474651]';
        }

        // 3. Rincian Card Midtrans Snap
        const snapLabel = document.getElementById('snap-nominal-label');
        if (snapLabel) snapLabel.textContent = isFull ? '100% Lunas' : '50% DP';

        const snapSub = document.getElementById('snap-nominal-sub');
        if (snapSub) snapSub.textContent = isFull ? 'Pembayaran lunas online penuh via Midtrans Snap' : 'Pembayaran awal uang muka via Midtrans Snap';

        const snapVal = document.getElementById('snap-nominal-val');
        if (snapVal) snapVal.textContent = formatRupiah(nominal);

        // 4. Panel Sidebar Ringkasan Kanan
        const sideTitle = document.getElementById('sidebar-panel-title');
        if (sideTitle) sideTitle.textContent = isFull ? 'Pembayaran Lunas 100%' : 'DP yang Dibayar Sekarang';

        const sideBadge = document.getElementById('sidebar-panel-badge');
        if (sideBadge) sideBadge.textContent = isFull ? '100% LUNAS' : '50% DP';

        const sideAmount = document.getElementById('sidebar-panel-amount');
        if (sideAmount) sideAmount.textContent = formatRupiah(nominal);

        const sideStatusLabel = document.getElementById('sidebar-status-label');
        if (sideStatusLabel) sideStatusLabel.textContent = isFull ? 'Sisa di Kasir' : 'Sisa Bayar di Kasir';

        const sideStatusVal = document.getElementById('sidebar-status-val');
        if (sideStatusVal) {
            sideStatusVal.textContent = isFull ? 'Rp0 (Lunas)' : formatRupiah(sisa);
            sideStatusVal.className = 'font-bold ' + (isFull ? 'text-[#416900]' : 'text-amber-600');
        }

        const sideStatusNote = document.getElementById('sidebar-status-note');
        if (sideStatusNote) {
            sideStatusNote.textContent = isFull
                ? 'Pembayaran sewa lunas online. Tidak ada tagihan sewa lapangan lagi saat tiba di kasir venue.'
                : 'Sisa tagihan dibayarkan di kasir. Pastikan pembayaran DP sudah terverifikasi sebelum menggunakan e-tiket.';
        }

        // 5. Update nominal di setiap kartu channel pembayaran
        document.querySelectorAll('.channel-nominal-display').forEach(el => {
            el.textContent = formatRupiah(nominal);
        });

        // 6. Teks Tombol Bayar Sekarang (Tetap mempertahankan Pesan Sekarang!)
        const payText = document.getElementById('pay-button-text');
        if (payText) payText.textContent = `Pesan Sekarang!`;
    };

    document.addEventListener('DOMContentLoaded', function () {
        const payButton = document.getElementById('pay-button');
        const countdownDisplay = document.getElementById('countdown-display');
        const sidebarCountdown = document.getElementById('sidebar-countdown');

        let remainingSeconds = {{ (int) ($sisaDetik ?? 600) }};
        let isExpired = false;

        function updateTimerUI() {
            if (remainingSeconds <= 0) {
                remainingSeconds = 0;
                if (countdownDisplay) countdownDisplay.innerText = '00:00 (Habis)';
                if (sidebarCountdown) sidebarCountdown.innerText = '00:00';
                handleTimeout();
                return;
            }

            const mins = Math.floor(remainingSeconds / 60);
            const secs = remainingSeconds % 60;
            const formatted = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');

            if (countdownDisplay) countdownDisplay.innerText = formatted;
            if (sidebarCountdown) sidebarCountdown.innerText = formatted;

            remainingSeconds--;
        }

        function handleTimeout() {
            if (isExpired) return;
            isExpired = true;

            if (payButton) {
                payButton.disabled = true;
                payButton.classList.add('opacity-50', 'cursor-not-allowed');
                const buttonText = document.getElementById('pay-button-text') || payButton;
                buttonText.innerText = 'Batas Waktu Habis (Dibatalkan)';
            }

            @if (!empty($jadwalId))
            fetch("{{ route('checkout.expire', ['id' => $jadwalId]) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                }
            }).catch(console.error);
            @endif

            showThemeAlert({
                title: "Waktu Pembayaran Habis",
                message: "Batas waktu pembayaran 10 menit telah kedaluwarsa. Pemesanan slot ini otomatis dibatalkan dan dibuka kembali untuk pengguna lain.",
                type: "warning",
                confirmText: "Kembali ke Jadwal",
                onConfirm: function () {
                    window.location.href = "{{ route('katalog.show', $lapangan->id) }}";
                }
            });
        }

        updateTimerUI();
        const intervalId = setInterval(function () {
            if (remainingSeconds <= 0) {
                clearInterval(intervalId);
                updateTimerUI();
            } else {
                updateTimerUI();
            }
        }, 1000);

        if (!payButton) return;

        payButton.addEventListener('click', function (e) {
            e.preventDefault();

            if (isExpired || remainingSeconds <= 0) {
                handleTimeout();
                return;
            }

            const buttonText = document.getElementById('pay-button-text') || payButton;
            const originalText = buttonText.innerText;

            const isGuest = {{ $pelanggan ? 'false' : 'true' }};
            let guestNama = '';
            let guestNoHp = '';

            if (isGuest) {
                const elNama = document.getElementById('guest_nama');
                const elNoHp = document.getElementById('guest_no_hp');
                guestNama = elNama ? elNama.value.trim() : '';
                guestNoHp = elNoHp ? elNoHp.value.trim() : '';

                if (elNama) {
                    elNama.addEventListener('input', function() {
                        elNama.classList.remove('!border-rose-500', 'ring-2', 'ring-rose-200');
                    }, { once: true });
                }
                if (elNoHp) {
                    elNoHp.addEventListener('input', function() {
                        elNoHp.classList.remove('!border-rose-500', 'ring-2', 'ring-rose-200');
                    }, { once: true });
                }

                if (!guestNama) {
                    if (elNama) elNama.classList.add('!border-rose-500', 'ring-2', 'ring-rose-200');
                    showNotificationModal({
                        title: 'Nama Pemesan Belum Diisi',
                        message: 'Silakan masukkan Nama Pemesan terlebih dahulu untuk mencetak e-tiket dan reservasi.',
                        type: 'warning',
                        confirmText: 'Lengkapi Nama Pemesan',
                        onConfirm: function() {
                            elNama?.focus();
                        }
                    });
                    return;
                }

                if (!guestNoHp) {
                    if (elNoHp) elNoHp.classList.add('!border-rose-500', 'ring-2', 'ring-rose-200');
                    showNotificationModal({
                        title: 'Nomor WhatsApp Belum Diisi',
                        message: 'Silakan masukkan Nomor WhatsApp aktif untuk verifikasi dan konfirmasi booking Anda.',
                        type: 'warning',
                        confirmText: 'Lengkapi Nomor WhatsApp',
                        onConfirm: function() {
                            elNoHp?.focus();
                        }
                    });
                    return;
                }
            }

            if (!window.selectedPaymentMethod) {
                showNotificationModal({
                    title: 'Metode Pembayaran Belum Dipilih',
                    message: 'Silakan klik dan pilih salah satu metode pembayaran terlebih dahulu (QRIS, Bank Virtual Account, atau E-Wallet).',
                    type: 'warning',
                    confirmText: 'Pilih Metode Pembayaran',
                    onConfirm: function() {
                        const targetSection = document.getElementById('accordion-payment-methods');
                        if (targetSection) {
                            targetSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                });
                return;
            }

            payButton.disabled = true;
            payButton.classList.add('opacity-70', 'cursor-not-allowed');
            buttonText.innerText = 'Memproses Pembayaran...';

            fetch("{{ route('checkout.pay') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    lapangan_id: "{{ $lapangan->id }}",
                    tanggal: "{{ $tanggal }}",
                    jam_mulai: "{{ $jamMulai }}",
                    durasi: {{ $durasi }},
                    tipe_bayar: window.currentTipeBayar,
                    metode_bayar: window.selectedPaymentMethod || 'qris',
                    jadwal_id: "{{ $jadwalId ?? '' }}",
                    booking_id: "{{ $bookingId ?? ($jadwalId ?? '') }}",
                    nama: guestNama || "{{ $pelanggan?->nama ?? '' }}",
                    no_hp: guestNoHp || "{{ $pelanggan?->no_hp ?? '' }}"
                })
            })
            .then(function (response) {
                if (!response.ok) {
                    return response.json().then(function (err) {
                        throw new Error(err.message || 'Gagal memproses transaksi.');
                    });
                }
                return response.json();
            })
            .then(function (data) {
                const snapToken = data.snap_token;
                if (!snapToken) {
                    throw new Error('Snap token tidak diterima dari server.');
                }

                if (typeof window.snap === 'undefined') {
                    showNotificationModal({
                        title: 'Koneksi Midtrans Terputus',
                        message: 'Midtrans Snap SDK tidak berhasil dimuat. Pastikan koneksi internet Anda aktif.',
                        type: 'error',
                        confirmText: 'Coba Lagi'
                    });
                    payButton.disabled = false;
                    payButton.classList.remove('opacity-70', 'cursor-not-allowed');
                    buttonText.innerText = originalText;
                    return;
                }

                window.snap.pay(snapToken, {
                    onSuccess: function(result) {
                        const oid = (result && result.order_id) ? result.order_id : data.order_id;
                        window.location.href = "{{ url('/checkout/success') }}/" + encodeURIComponent(oid);
                    },
                    onPending: function(result) {
                        // Jangan langsung anggap sukses atau arahkan ke tiket berhasil jika user belum membayar!
                        payButton.disabled = false;
                        payButton.classList.remove('opacity-70', 'cursor-not-allowed');
                        buttonText.innerText = originalText;

                        showNotificationModal({
                            title: 'Menunggu Pembayaran',
                            message: 'Instruksi pembayaran telah diterbitkan oleh Midtrans. Silakan selesaikan pembayaran Anda sebelum batas waktu berakhir.',
                            type: 'info',
                            confirmText: 'Lihat Riwayat Booking',
                            onConfirm: function() {
                                window.location.href = "{{ route('pelanggan.riwayat') }}";
                            }
                        });
                    },
                    onError: function(result) {
                        showNotificationModal({
                            title: 'Pembayaran Belum Berhasil',
                            message: 'Pembayaran tidak berhasil diselesaikan. Silakan coba kembali atau gunakan metode bayar lain.',
                            type: 'error',
                            confirmText: 'Tutup'
                        });
                        payButton.disabled = false;
                        payButton.classList.remove('opacity-70', 'cursor-not-allowed');
                        buttonText.innerText = originalText;
                    },
                    onClose: function() {
                        console.log("Customer closed the payment popup without finishing.");
                        payButton.disabled = false;
                        payButton.classList.remove('opacity-70', 'cursor-not-allowed');
                        buttonText.innerText = originalText;
                    }
                });
            })
            .catch(function (error) {
                showNotificationModal({
                    title: 'Terjadi Kendala Sistem',
                    message: error.message || 'Terjadi kesalahan sistem, silakan coba kembali beberapa saat lagi.',
                    type: 'error',
                    confirmText: 'Tutup'
                });
                payButton.disabled = false;
                payButton.classList.remove('opacity-70', 'cursor-not-allowed');
                buttonText.innerText = originalText;
            });
        });
    });
</script>
@endsection
