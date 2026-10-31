@extends('layouts.app')

@section('title', 'Checkout Booking & Pembayaran')

@section('content')
<div class="min-h-screen bg-[#f8f9ff] text-[#0b1c30]"
     style="font-family: 'Plus Jakarta Sans', sans-serif;">

    {{-- Progress Booking --}}
    <section class="border-b border-[#e5eeff] bg-[#eff4ff] px-4 py-5 sm:px-8">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-5">
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <div class="flex items-center gap-2 text-[#416900]">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#416900] text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <span class="text-gray-500 line-through">Pilih Jadwal</span>
                </div>

                <span class="h-0.5 w-8 bg-[#91db2a]"></span>

                <div class="flex items-center gap-2 font-bold text-[#1a146b]">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#1a146b] text-white">2</span>
                    <span>Pembayaran (<span id="breadcrumb-tipe-text">{{ $tipeBayar === 'full' ? 'Lunas 100%' : 'DP 50%' }}</span>)</span>
                </div>

                <span class="h-0.5 w-8 bg-[#d3e4fe]"></span>

                <div class="flex items-center gap-2 text-gray-400">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#dce9ff]">3</span>
                    <span>E-Tiket Siap</span>
                </div>
            </div>

            <span class="rounded-full bg-[#dce9ff] px-4 py-2 text-xs font-bold text-[#1a146b]">
                LAPANGIN · CHECKOUT BOOKING
            </span>
        </div>
    </section>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-8 lg:py-10">

        {{-- Judul halaman --}}
        <div class="mb-7">
            <a href="{{ route('katalog.show', $lapangan->id) }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-[#416900] hover:underline">
                ← Kembali ke Lapangan
            </a>

            <h1 class="mt-4 text-2xl font-extrabold tracking-tight text-[#0b1c30] sm:text-3xl">
                Checkout Booking &amp; Pembayaran <span id="title-tipe-text">{{ $tipeBayar === 'full' ? 'Lunas (100%)' : 'DP (50%)' }}</span>
            </h1>

            <p class="mt-2 text-sm leading-6 text-[#474651]">
                Periksa detail booking sebelum melanjutkan proses pembayaran resmi via Midtrans Snap.
            </p>
        </div>

        {{-- Banner Timer 10 Menit & Row Locking Slot Sementara --}}
        <div id="countdown-banner" class="mb-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl border-2 border-amber-300 bg-amber-50 p-4 sm:p-5 shadow-sm">
            <div class="flex items-center gap-3.5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-black uppercase tracking-wider text-amber-900">
                            Batas Waktu Pembayaran
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-200/80 px-2 py-0.5 text-[10px] font-bold text-amber-900">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-600 animate-ping"></span>
                            Slot Terkunci Sementara
                        </span>
                    </div>
                    <p class="mt-0.5 text-xs text-amber-800 leading-relaxed">
                        Slot waktu ini dikunci eksklusif untuk Anda selama 10 menit. Jika waktu habis, pesanan otomatis dibatalkan &amp; slot dibuka kembali.
                    </p>
                </div>
            </div>

            <div class="flex items-center sm:justify-end shrink-0">
                <div class="flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 border border-amber-200 shadow-xs">
                    <span class="text-xs font-semibold text-gray-500">Sisa:</span>
                    <span id="countdown-display" class="font-mono text-xl font-black text-amber-900">
                        {{ sprintf('%02d:%02d', floor(($sisaDetik ?? 600) / 60), ($sisaDetik ?? 600) % 60) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12 lg:gap-8">

            {{-- KOLOM KIRI --}}
            <div class="space-y-6 lg:col-span-7">

                {{-- Informasi Pemesan --}}
                <section class="rounded-2xl border border-[#e8edfa] bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <h2 class="flex items-center gap-2 text-lg font-bold text-[#0b1c30]">
                            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-[#eff4ff] text-[#1a146b]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            </span>
                            Informasi Pemesan
                        </h2>

                        <span class="text-xs font-semibold text-[#416900]">
                            Data Booking
                        </span>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl bg-[#eff4ff] p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-[#474651]">
                                Nama Pemesan
                            </p>
                            @if($pelanggan)
                                <p class="mt-2 break-words font-bold text-[#0b1c30]">
                                    {{ $pelanggan->nama ?? $pelanggan->name ?? 'Belum tersedia' }}
                                </p>
                            @else
                                <input type="text" id="guest_nama" name="guest_nama" placeholder="Contoh: Budi Santoso"
                                    class="mt-1.5 w-full rounded-lg border border-blue-200 bg-white px-3 py-2 text-sm font-semibold text-[#0b1c30] placeholder-gray-400 focus:border-[#416900] focus:outline-none focus:ring-1 focus:ring-[#416900]" required>
                            @endif
                        </div>

                        <div class="rounded-xl bg-[#eff4ff] p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-[#474651]">
                                Nomor WhatsApp
                            </p>
                            @if($pelanggan)
                                <p class="mt-2 break-words font-bold text-[#0b1c30]">
                                    {{ $pelanggan->no_hp ?? $pelanggan->no_telp ?? 'Belum tersedia' }}
                                </p>
                            @else
                                <input type="tel" id="guest_no_hp" name="guest_no_hp" placeholder="Contoh: 08123456789"
                                    class="mt-1.5 w-full rounded-lg border border-blue-200 bg-white px-3 py-2 text-sm font-semibold text-[#0b1c30] placeholder-gray-400 focus:border-[#416900] focus:outline-none focus:ring-1 focus:ring-[#416900]" required>
                            @endif
                        </div>

                        <div class="rounded-xl bg-[#eff4ff] p-4 sm:col-span-2">
                            <p class="text-xs font-bold uppercase tracking-wide text-[#474651]">
                                Lapangan yang Dipilih
                            </p>
                            <p class="mt-2 break-words font-bold text-[#0b1c30]">
                                {{ $lapangan->nama }}
                            </p>
                        </div>
                    </div>
                </section>

                {{-- PILIHAN METODE PEMBAYARAN (ACCORDION DENGAN GRID CHANNEL PERSIS CONTOH GAMBAR) --}}
                <section class="rounded-2xl border border-[#e8edfa] bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#1a146b] text-xs font-black text-[#acf847]">
                                2
                            </span>
                            <h2 class="text-base font-extrabold text-[#0b1c30]">
                                Pilih Metode Pembayaran
                            </h2>
                        </div>
                        <span class="rounded-full bg-[#eff4ff] px-2.5 py-1 text-[11px] font-bold text-[#1a146b]">
                            Verifikasi Otomatis
                        </span>
                    </div>

                    <p class="mb-5 text-xs text-[#474651]">
                        Pilih kategori pembayaran di bawah ini. Klik kategori untuk membuka pilihan bank atau e-wallet.
                    </p>

                    <div class="space-y-3.5" id="accordion-payment-methods">

                        {{-- 1. QRIS (All E-Wallet & Mobile Banking) --}}
                        <div class="rounded-xl border border-[#e8edfa] bg-white shadow-xs transition" id="group-qris">
                            {{-- Header Accordion --}}
                            <button type="button" onclick="togglePaymentGroup('qris')"
                                    class="flex w-full items-center justify-between p-4 text-left hover:bg-slate-50 transition rounded-xl">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#eff4ff] text-[#1a146b] font-black text-xs">
                                        QR
                                    </span>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-extrabold text-[#0b1c30]">QRIS (Semua E-Wallet & M-Banking)</span>
                                            <span class="rounded bg-[#acf847] px-1.5 py-0.5 text-[9px] font-black uppercase text-[#304f00]">Best Choice</span>
                                        </div>
                                        <p class="text-xs text-[#777682] mt-0.5">GoPay, ShopeePay, OVO, Dana, LinkAja, BCA, dll</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="hidden sm:flex items-center gap-1.5 opacity-80">
                                        <span class="rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-black tracking-wider text-slate-800">QRIS</span>
                                        <span class="rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-black text-sky-600">GOPAY</span>
                                        <span class="rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-black text-orange-500">SHOPEEPAY</span>
                                    </div>
                                    <svg id="chevron-qris" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </button>

                            {{-- Sub-items Grid --}}
                            <div id="content-qris" class="p-4 pt-1 border-t border-gray-100">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                                    {{-- Sub Card: QRIS Instant --}}
                                    <div onclick="selectPaymentChannel('qris', 'QRIS Universal')"
                                         id="channel-box-qris"
                                         class="payment-channel-card cursor-pointer rounded-xl border-2 border-[#1a146b] bg-[#eff4ff] p-3.5 transition flex items-center justify-between shadow-xs">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-white border border-gray-200 flex items-center justify-center font-black text-xs text-slate-900 shadow-2xs">
                                                QRIS
                                            </div>
                                            <div>
                                                <p class="text-xs font-extrabold text-[#0b1c30]">QRIS Universal</p>
                                                <p class="text-[11px] text-[#416900] font-semibold">Diverifikasi Otomatis</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="channel-nominal-display text-xs font-black text-[#1a146b]">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                            <span class="inline-block mt-0.5 text-[10px] text-gray-500">Scan via m-Banking/E-Wallet</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Virtual Account (BCA, Mandiri, BNI, BRI, Permata, dll) --}}
                        <div class="rounded-xl border border-[#e8edfa] bg-white shadow-xs transition" id="group-va">
                            {{-- Header Accordion --}}
                            <button type="button" onclick="togglePaymentGroup('va')"
                                    class="flex w-full items-center justify-between p-4 text-left hover:bg-slate-50 transition rounded-xl">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#eff4ff] text-[#1a146b] font-black text-xs">
                                        VA
                                    </span>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-extrabold text-[#0b1c30]">Virtual Account (Transfer Bank)</span>
                                            <span class="rounded bg-sky-100 px-1.5 py-0.5 text-[9px] font-bold text-sky-800">Otomatis 24 Jam</span>
                                        </div>
                                        <p class="text-xs text-[#777682] mt-0.5">BCA, Mandiri, BRI, BNI, Permata &amp; ATM Lainnya</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="hidden sm:flex items-center gap-1.5 opacity-80">
                                        <span class="rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-black text-[#0060af]">BCA</span>
                                        <span class="rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-black text-[#003d79]">MANDIRI</span>
                                        <span class="rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-black text-[#00529c]">BRI</span>
                                        <span class="rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-black text-[#e05828]">BNI</span>
                                    </div>
                                    <svg id="chevron-va" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </button>

                            {{-- Sub-items Grid --}}
                            <div id="content-va" class="p-4 pt-1 border-t border-gray-100">
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mt-2">
                                    {{-- BCA VA --}}
                                    <div onclick="selectPaymentChannel('bca_va', 'BCA Virtual Account')"
                                         id="channel-box-bca_va"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 transition flex flex-col justify-between">
                                        <div class="flex items-center justify-between">
                                            <span class="rounded border border-gray-200 bg-slate-50 px-2 py-0.5 text-xs font-black text-[#0060af]">BCA</span>
                                            <span class="text-[10px] font-bold text-[#416900]">Otomatis</span>
                                        </div>
                                        <div class="mt-3">
                                            <p class="text-xs font-extrabold text-[#0b1c30]">BCA Virtual Account</p>
                                            <p class="channel-nominal-display text-xs font-bold text-[#1a146b] mt-1">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                        </div>
                                    </div>

                                    {{-- Mandiri VA --}}
                                    <div onclick="selectPaymentChannel('mandiri_va', 'Mandiri Virtual Account')"
                                         id="channel-box-mandiri_va"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 transition flex flex-col justify-between">
                                        <div class="flex items-center justify-between">
                                            <span class="rounded border border-gray-200 bg-slate-50 px-2 py-0.5 text-xs font-black text-[#003d79]">MANDIRI</span>
                                            <span class="text-[10px] font-bold text-[#416900]">Otomatis</span>
                                        </div>
                                        <div class="mt-3">
                                            <p class="text-xs font-extrabold text-[#0b1c30]">Mandiri Bill Payment</p>
                                            <p class="channel-nominal-display text-xs font-bold text-[#1a146b] mt-1">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                        </div>
                                    </div>

                                    {{-- BRI VA --}}
                                    <div onclick="selectPaymentChannel('bri_va', 'BRI Virtual Account')"
                                         id="channel-box-bri_va"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 transition flex flex-col justify-between">
                                        <div class="flex items-center justify-between">
                                            <span class="rounded border border-gray-200 bg-slate-50 px-2 py-0.5 text-xs font-black text-[#00529c]">BRI</span>
                                            <span class="text-[10px] font-bold text-[#416900]">Otomatis</span>
                                        </div>
                                        <div class="mt-3">
                                            <p class="text-xs font-extrabold text-[#0b1c30]">BRI (BRIVA)</p>
                                            <p class="channel-nominal-display text-xs font-bold text-[#1a146b] mt-1">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                        </div>
                                    </div>

                                    {{-- BNI VA --}}
                                    <div onclick="selectPaymentChannel('bni_va', 'BNI Virtual Account')"
                                         id="channel-box-bni_va"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 transition flex flex-col justify-between">
                                        <div class="flex items-center justify-between">
                                            <span class="rounded border border-gray-200 bg-slate-50 px-2 py-0.5 text-xs font-black text-[#e05828]">BNI</span>
                                            <span class="text-[10px] font-bold text-[#416900]">Otomatis</span>
                                        </div>
                                        <div class="mt-3">
                                            <p class="text-xs font-extrabold text-[#0b1c30]">BNI Virtual Account</p>
                                            <p class="channel-nominal-display text-xs font-bold text-[#1a146b] mt-1">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                        </div>
                                    </div>

                                    {{-- Permata VA --}}
                                    <div onclick="selectPaymentChannel('permata_va', 'Permata Virtual Account')"
                                         id="channel-box-permata_va"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 transition flex flex-col justify-between">
                                        <div class="flex items-center justify-between">
                                            <span class="rounded border border-gray-200 bg-slate-50 px-2 py-0.5 text-xs font-black text-[#1a7f37]">PERMATA</span>
                                            <span class="text-[10px] font-bold text-[#416900]">Otomatis</span>
                                        </div>
                                        <div class="mt-3">
                                            <p class="text-xs font-extrabold text-[#0b1c30]">Permata VA</p>
                                            <p class="channel-nominal-display text-xs font-bold text-[#1a146b] mt-1">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                        </div>
                                    </div>

                                    {{-- ATM Bersama / Prima (Other VA) --}}
                                    <div onclick="selectPaymentChannel('other_va', 'ATM Bersama / Prima')"
                                         id="channel-box-other_va"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3 transition flex flex-col justify-between">
                                        <div class="flex items-center justify-between">
                                            <span class="rounded border border-gray-200 bg-slate-50 px-2 py-0.5 text-xs font-black text-[#304f00]">ATM BERSAMA</span>
                                            <span class="text-[10px] font-bold text-[#416900]">Otomatis</span>
                                        </div>
                                        <div class="mt-3">
                                            <p class="text-xs font-extrabold text-[#0b1c30]">Bank Lainnya (Prima)</p>
                                            <p class="channel-nominal-display text-xs font-bold text-[#1a146b] mt-1">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3. E-Wallet Langsung (GoPay & ShopeePay App) --}}
                        <div class="rounded-xl border border-[#e8edfa] bg-white shadow-xs transition" id="group-ewallet">
                            {{-- Header Accordion --}}
                            <button type="button" onclick="togglePaymentGroup('ewallet')"
                                    class="flex w-full items-center justify-between p-4 text-left hover:bg-slate-50 transition rounded-xl">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#eff4ff] text-[#1a146b] font-black text-xs">
                                        EW
                                    </span>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-extrabold text-[#0b1c30]">E-Wallet Langsung</span>
                                            <span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[9px] font-bold text-emerald-800">Buka Aplikasi</span>
                                        </div>
                                        <p class="text-xs text-[#777682] mt-0.5">GoPay Apps, GoPay Later &amp; ShopeePay</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="hidden sm:flex items-center gap-1.5 opacity-80">
                                        <span class="rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-black text-sky-600">GOPAY</span>
                                        <span class="rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-black text-orange-500">SHOPEEPAY</span>
                                    </div>
                                    <svg id="chevron-ewallet" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </button>

                            {{-- Sub-items Grid --}}
                            <div id="content-ewallet" class="p-4 pt-1 border-t border-gray-100 hidden">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                                    {{-- GoPay --}}
                                    <div onclick="selectPaymentChannel('gopay', 'GoPay')"
                                         id="channel-box-gopay"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3.5 transition flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-black text-sky-600 shadow-2xs">GOPAY</span>
                                            <div>
                                                <p class="text-xs font-extrabold text-[#0b1c30]">GoPay / GoPay Later</p>
                                                <p class="text-[11px] text-[#416900] font-semibold">Buka Otomatis Aplikasi</p>
                                            </div>
                                        </div>
                                        <p class="channel-nominal-display text-xs font-black text-[#1a146b]">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                    </div>

                                    {{-- ShopeePay --}}
                                    <div onclick="selectPaymentChannel('shopeepay', 'ShopeePay')"
                                         id="channel-box-shopeepay"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3.5 transition flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-black text-orange-500 shadow-2xs">SHOPEE</span>
                                            <div>
                                                <p class="text-xs font-extrabold text-[#0b1c30]">ShopeePay / SPayLater</p>
                                                <p class="text-[11px] text-[#416900] font-semibold">Buka Otomatis Aplikasi</p>
                                            </div>
                                        </div>
                                        <p class="channel-nominal-display text-xs font-black text-[#1a146b]">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 4. Kartu Kredit / Debit --}}
                        <div class="rounded-xl border border-[#e8edfa] bg-white shadow-xs transition" id="group-cc">
                            {{-- Header Accordion --}}
                            <button type="button" onclick="togglePaymentGroup('cc')"
                                    class="flex w-full items-center justify-between p-4 text-left hover:bg-slate-50 transition rounded-xl">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#eff4ff] text-[#1a146b] font-black text-xs">
                                        CC
                                    </span>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-extrabold text-[#0b1c30]">Kartu Kredit &amp; Debit Online</span>
                                            <span class="rounded bg-violet-100 px-1.5 py-0.5 text-[9px] font-bold text-violet-800">3D Secure</span>
                                        </div>
                                        <p class="text-xs text-[#777682] mt-0.5">Visa, Mastercard, JCB, American Express</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="hidden sm:flex items-center gap-1.5 opacity-80">
                                        <span class="rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-black text-blue-800">VISA</span>
                                        <span class="rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-black text-red-600">MC</span>
                                        <span class="rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-black text-emerald-700">JCB</span>
                                    </div>
                                    <svg id="chevron-cc" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </button>

                            {{-- Sub-items Grid --}}
                            <div id="content-cc" class="p-4 pt-1 border-t border-gray-100 hidden">
                                <div class="grid grid-cols-1 gap-3 mt-2">
                                    {{-- Credit Card Channel Card --}}
                                    <div onclick="selectPaymentChannel('credit_card', 'Kartu Kredit / Debit')"
                                         id="channel-box-credit_card"
                                         class="payment-channel-card cursor-pointer rounded-xl border border-[#e8edfa] bg-white hover:border-[#1a146b] p-3.5 transition flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-1">
                                                <span class="rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-black text-blue-800">VISA</span>
                                                <span class="rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-black text-red-600">MC</span>
                                            </div>
                                            <div>
                                                <p class="text-xs font-extrabold text-[#0b1c30]">Semua Kartu Kredit / Debit Visa &amp; Mastercard</p>
                                                <p class="text-[11px] text-[#416900] font-semibold">Proteksi OTP 3D Secure</p>
                                            </div>
                                        </div>
                                        <p class="channel-nominal-display text-xs font-black text-[#1a146b]">Rp{{ number_format($nominalBayar, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>
            </div>

            {{-- KOLOM KANAN: RINGKASAN & PEMILIHAN TIPE BAYAR --}}
            <aside class="lg:sticky lg:top-6 lg:col-span-5">
                <section class="overflow-hidden rounded-2xl border border-[#e8edfa] bg-white shadow-md">

                    <div class="px-5 py-5 sm:px-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="flex items-center gap-2 text-sm font-extrabold uppercase tracking-wider text-[#416900]">
                                <span class="h-2 w-2 rounded-full bg-[#416900]"></span>
                                Ringkasan Booking
                            </h2>
                            <span id="section-badge-tipe" class="rounded-full bg-[#acf847] px-3 py-1 text-xs font-bold text-[#304f00]">
                                {{ $tipeBayar === 'full' ? 'Lunas 100%' : 'DP 50%' }}
                            </span>
                        </div>

                        {{-- Pilihan Tipe Pembayaran (Disatukan dalam Card Ringkasan) --}}
                        <div class="mb-5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#474651] mb-2.5">
                                Pilih Tipe Pembayaran
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                {{-- Opsi A: DP 50% --}}
                                <button type="button"
                                        id="card-tipe-dp"
                                        onclick="setTipeBayar('dp')"
                                        class="text-left w-full relative flex flex-col p-3 rounded-xl border-2 transition-all cursor-pointer {{ $tipeBayar === 'dp' ? 'border-[#1a146b] bg-[#eff4ff] shadow-xs ring-1 ring-[#1a146b]' : 'border-[#e8edfa] bg-white hover:border-[#cbd5e1]' }}">
                                    <div class="flex items-center justify-between">
                                        <span id="label-tipe-dp" class="text-[11px] font-bold uppercase tracking-wide {{ $tipeBayar === 'dp' ? 'text-[#1a146b]' : 'text-[#474651]' }}">
                                            Bayar DP (50%)
                                        </span>
                                        <span id="radio-tipe-dp" class="h-3.5 w-3.5 rounded-full border-2 flex items-center justify-center {{ $tipeBayar === 'dp' ? 'border-[#1a146b] bg-[#1a146b]' : 'border-gray-300' }}">
                                            @if($tipeBayar === 'dp')
                                                <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                                            @endif
                                        </span>
                                    </div>
                                    <p class="mt-1 text-base font-extrabold text-[#0b1c30]">
                                        Rp{{ number_format($nominalDp, 0, ',', '.') }}
                                    </p>
                                    <p class="mt-0.5 text-[10px] leading-snug text-[#474651]">
                                        Sisa dilunasi di kasir venue
                                    </p>
                                </button>

                                {{-- Opsi B: Bayar Lunas 100% --}}
                                <button type="button"
                                        id="card-tipe-full"
                                        onclick="setTipeBayar('full')"
                                        class="text-left w-full relative flex flex-col p-3 rounded-xl border-2 transition-all cursor-pointer {{ $tipeBayar === 'full' ? 'border-[#1a146b] bg-[#eff4ff] shadow-xs ring-1 ring-[#1a146b]' : 'border-[#e8edfa] bg-white hover:border-[#cbd5e1]' }}">
                                    <div class="flex items-center justify-between">
                                        <span id="label-tipe-full" class="text-[11px] font-bold uppercase tracking-wide {{ $tipeBayar === 'full' ? 'text-[#1a146b]' : 'text-[#474651]' }}">
                                            Bayar Lunas (100%)
                                        </span>
                                        <span id="radio-tipe-full" class="h-3.5 w-3.5 rounded-full border-2 flex items-center justify-center {{ $tipeBayar === 'full' ? 'border-[#1a146b] bg-[#1a146b]' : 'border-gray-300' }}">
                                            @if($tipeBayar === 'full')
                                                <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                                            @endif
                                        </span>
                                    </div>
                                    <p class="mt-1 text-base font-extrabold text-[#0b1c30]">
                                        Rp{{ number_format($totalHarga, 0, ',', '.') }}
                                    </p>
                                    <p class="mt-0.5 text-[10px] leading-snug text-[#416900] font-semibold">
                                        Main langsung tanpa kasir
                                    </p>
                                </button>
                            </div>
                        </div>

                        <div class="rounded-xl bg-[#eff4ff] p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-[#474651]">
                                Lapangan pilihan
                            </p>

                            <h3 class="mt-2 text-lg font-extrabold text-[#0b1c30]">
                                {{ $lapangan->nama }}
                            </h3>

                            <span class="mt-3 inline-flex rounded-md bg-[#dce9ff] px-2 py-1 text-xs font-bold text-[#1a146b]">
                                Booking Lapangan
                            </span>
                        </div>

                        <div class="mt-4 space-y-4 rounded-xl border border-[#e8edfa] p-4">
                            <div class="flex items-start justify-between gap-4">
                                <span class="text-sm text-[#474651]">Tanggal</span>
                                <span class="text-right text-sm font-bold text-[#1a146b]">
                                    {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
                                </span>
                            </div>

                            <div class="flex items-start justify-between gap-4">
                                <span class="text-sm text-[#474651]">Jam booking</span>
                                <span class="text-right text-sm font-bold text-[#0b1c30]">
                                    {{ $jamMulai }} – {{ $jamSelesai }}
                                </span>
                            </div>

                            <div class="flex items-start justify-between gap-4">
                                <span class="text-sm text-[#474651]">Durasi</span>
                                <span class="rounded-md bg-[#acf847] px-2 py-1 text-xs font-bold text-[#304f00]">
                                    {{ $durasi }} jam
                                </span>
                            </div>
                        </div>

                        {{-- Rincian Finansial --}}
                        <div class="mt-6">
                            <h3 class="mb-4 text-xs font-extrabold uppercase tracking-wider text-[#474651]">
                                Rincian Finansial
                            </h3>

                            <div class="space-y-3">
                                <div class="flex justify-between gap-3 text-sm">
                                    <span class="text-[#474651]">Harga rata-rata per jam</span>
                                    <span class="font-semibold text-[#0b1c30]">
                                        Rp{{ number_format($hargaPerJam, 0, ',', '.') }}
                                    </span>
                                </div>

                                <div class="flex justify-between gap-3 text-sm">
                                    <span class="text-[#474651]">Durasi sewa</span>
                                    <span class="font-semibold text-[#0b1c30]">
                                        {{ $durasi }} jam
                                    </span>
                                </div>

                                <div class="border-t border-dashed border-[#c8c5d3] pt-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="font-bold text-[#0b1c30]">
                                            Total Tagihan
                                        </span>
                                        <span class="text-right text-lg font-extrabold text-[#1a146b]">
                                            Rp{{ number_format($totalHarga, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Panel Pembayaran (DP vs Full) --}}
                        <div class="mt-5 rounded-xl bg-[#1a146b] p-5 text-white">
                            <div class="flex items-center justify-between gap-3">
                                <span id="sidebar-panel-title" class="text-xs font-bold uppercase tracking-wide text-[#e2dfff]">
                                    {{ $tipeBayar === 'full' ? 'Total Pembayaran Lunas' : 'DP yang Harus Dibayar' }}
                                </span>

                                <span id="sidebar-panel-badge" class="rounded-md bg-[#416900] px-2 py-1 text-xs font-bold text-white">
                                    {{ $tipeBayar === 'full' ? '100% LUNAS' : '50% DP' }}
                                </span>
                            </div>

                            <p id="sidebar-panel-amount" class="mt-3 text-2xl font-extrabold">
                                Rp{{ number_format($nominalBayar, 0, ',', '.') }}
                            </p>

                            <div class="mt-4 border-t border-white/20 pt-4">
                                <div class="flex items-center justify-between gap-3">
                                    <span id="sidebar-status-label" class="text-sm text-[#e2dfff]">
                                        {{ $tipeBayar === 'full' ? 'Status pelunasan' : 'Sisa pembayaran di kasir' }}
                                    </span>

                                    <span id="sidebar-status-val" class="text-right font-bold text-[#acf847]">
                                        @if($tipeBayar === 'full')
                                            Rp0 (Lunas Online)
                                        @else
                                            Rp{{ number_format($sisaPembayaran, 0, ',', '.') }}
                                        @endif
                                    </span>
                                </div>

                                <p id="sidebar-status-note" class="mt-2 text-xs leading-5 text-[#e2dfff]">
                                    @if($tipeBayar === 'full')
                                        Pembayaran sewa lunas online. Tidak ada tagihan sewa lapangan lagi saat tiba di kasir venue.
                                    @else
                                        Sisa tagihan dibayarkan di kasir. Pastikan pembayaran DP sudah terverifikasi sebelum menggunakan e-tiket.
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Status integrasi --}}
                        <div class="mt-4 rounded-xl bg-[#dce9ff] p-4">
                            <p class="text-sm font-bold text-[#1a146b]">
                                Periksa kembali pesanan
                            </p>

                            <p class="mt-1 text-xs leading-5 text-[#474651]">
                                Pastikan tanggal, waktu, durasi, dan lapangan sudah benar.
                                Nominal di atas merupakan rincian perhitungan, bukan bukti
                                pembayaran.
                            </p>
                        </div>

                        {{-- Timer Reminder Pill di Sidebar --}}
                        <div class="mt-4 flex items-center justify-between gap-2 rounded-xl border border-amber-200 bg-amber-50/90 px-3.5 py-2.5 text-xs text-amber-900">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-800 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="font-bold">Sisa Waktu Bayar:</span>
                            </div>
                            <span id="sidebar-countdown" class="font-mono font-extrabold text-amber-900">
                                {{ sprintf('%02d:%02d', floor(($sisaDetik ?? 600) / 60), ($sisaDetik ?? 600) % 60) }}
                            </span>
                        </div>

                        {{-- Tombol Aksi Utama --}}
                        <button id="pay-button"
                                type="button"
                                class="mt-4 flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#1a146b] px-6 py-4 text-base font-extrabold text-white shadow-md transition hover:bg-[#130d52]">
                            <span id="pay-button-text">Bayar Sekarang (Rp {{ number_format($nominalBayar, 0, ',', '.') }})</span>
                        </button>

                        <p class="mt-3 text-center text-xs leading-5 text-[#777682]">
                            Pembayaran aman via Midtrans Snap (QRIS, VA, E-Wallet, Kartu Kredit).
                        </p>
                    </div>
                </section>

                <div class="mt-4 flex items-center justify-center gap-2 text-xs text-[#777682]">
                    <svg class="w-3.5 h-3.5 text-[#777682] inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    Detail booking Lapangin
                </div>
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
    window.selectedPaymentMethod = 'qris';
    window.selectedPaymentLabel = 'QRIS Universal';
    window.currentModalCallback = null;

    // Toggle Accordion Group (Buka / Tutup dropdown per kategori)
    window.togglePaymentGroup = function(groupId) {
        const content = document.getElementById('content-' + groupId);
        const chevron = document.getElementById('chevron-' + groupId);
        if (!content) return;

        const isHidden = content.classList.contains('hidden');
        if (isHidden) {
            content.classList.remove('hidden');
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        } else {
            content.classList.add('hidden');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }
    };

    // Pilih spesifik Channel Pembayaran di dalam Grid/Accordion
    window.selectPaymentChannel = function(methodCode, methodLabel) {
        window.selectedPaymentMethod = methodCode;
        window.selectedPaymentLabel = methodLabel;

        // Reset styling semua sub-card channel
        document.querySelectorAll('.payment-channel-card').forEach(card => {
            card.classList.remove('border-2', 'border-[#1a146b]', 'bg-[#eff4ff]', 'shadow-xs', 'ring-1', 'ring-[#1a146b]');
            card.classList.add('border-[#e8edfa]', 'bg-white');
        });

        // Highlight kartu channel yang dipilih
        const activeCard = document.getElementById('channel-box-' + methodCode);
        if (activeCard) {
            activeCard.classList.remove('border-[#e8edfa]', 'bg-white');
            activeCard.classList.add('border-2', 'border-[#1a146b]', 'bg-[#eff4ff]', 'shadow-xs', 'ring-1', 'ring-[#1a146b]');
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
        if (sideTitle) sideTitle.textContent = isFull ? 'Total Pembayaran Lunas' : 'DP yang Harus Dibayar';

        const sideBadge = document.getElementById('sidebar-panel-badge');
        if (sideBadge) sideBadge.textContent = isFull ? '100% LUNAS' : '50% DP';

        const sideAmount = document.getElementById('sidebar-panel-amount');
        if (sideAmount) sideAmount.textContent = formatRupiah(nominal);

        const sideStatusLabel = document.getElementById('sidebar-status-label');
        if (sideStatusLabel) sideStatusLabel.textContent = isFull ? 'Status pelunasan' : 'Sisa pembayaran di kasir';

        const sideStatusVal = document.getElementById('sidebar-status-val');
        if (sideStatusVal) sideStatusVal.textContent = isFull ? 'Rp0 (Lunas Online)' : formatRupiah(sisa);

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

        // 6. Teks Tombol Bayar Sekarang
        const payText = document.getElementById('pay-button-text');
        if (payText) payText.textContent = `Bayar Sekarang (${formatRupiah(nominal)})`;
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

            alert("Batas waktu pembayaran 10 menit telah kedaluwarsa. Pemesanan slot ini otomatis dibatalkan dan dibuka kembali untuk pengguna lain.");
            window.location.href = "{{ route('katalog.show', $lapangan->id) }}";
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
