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
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#416900] text-white">✓</span>
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
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white text-xl shadow-xs animate-pulse">
                    ⏱️
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
                            <span class="text-xl text-[#1a146b]">♙</span>
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
                            <p class="mt-2 break-words font-bold text-[#0b1c30]">
                                {{ $pelanggan?->nama ?? $pelanggan?->name ?? 'Belum tersedia' }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-[#eff4ff] p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-[#474651]">
                                Nomor WhatsApp
                            </p>
                            <p class="mt-2 break-words font-bold text-[#0b1c30]">
                                {{ $pelanggan?->no_hp ?? $pelanggan?->no_telp ?? 'Belum tersedia' }}
                            </p>
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

                @php
                    $currentParams = request()->all();
                    $dpUrl = route('checkout.index', array_merge($currentParams, ['tipe_bayar' => 'dp']));
                    $fullUrl = route('checkout.index', array_merge($currentParams, ['tipe_bayar' => 'full']));
                @endphp

                {{-- Metode Pembayaran & Pilihan Skema --}}
                <section class="rounded-2xl border border-[#e8edfa] bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <h2 class="flex items-center gap-2 text-lg font-bold text-[#0b1c30]">
                            <span class="text-xl text-[#1a146b]">▣</span>
                            Pilih Skema &amp; Metode Pembayaran
                        </h2>

                        <span id="section-badge-tipe" class="rounded-full bg-[#acf847] px-3 py-1 text-xs font-bold text-[#304f00]">
                            {{ $tipeBayar === 'full' ? 'Lunas 100%' : 'DP 50%' }}
                        </span>
                    </div>

                    {{-- Selector Tipe Pembayaran (Radio Card Interaktif Tanpa Reload) --}}
                    <div class="mb-5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#474651] mb-2.5">
                            Pilih Tipe Pembayaran
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            {{-- Opsi A: DP 50% --}}
                            <button type="button"
                                    id="card-tipe-dp"
                                    onclick="setTipeBayar('dp')"
                                    class="text-left w-full relative flex flex-col p-4 rounded-xl border-2 transition-all cursor-pointer {{ $tipeBayar === 'dp' ? 'border-[#1a146b] bg-[#eff4ff] shadow-sm ring-1 ring-[#1a146b]' : 'border-[#e8edfa] bg-white hover:border-[#cbd5e1]' }}">
                                <div class="flex items-center justify-between">
                                    <span id="label-tipe-dp" class="text-xs font-bold uppercase tracking-wide {{ $tipeBayar === 'dp' ? 'text-[#1a146b]' : 'text-[#474651]' }}">
                                        Bayar DP (50%)
                                    </span>
                                    <span id="radio-tipe-dp" class="h-4 w-4 rounded-full border-2 flex items-center justify-center {{ $tipeBayar === 'dp' ? 'border-[#1a146b] bg-[#1a146b]' : 'border-gray-300' }}">
                                        @if($tipeBayar === 'dp')
                                            <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                                        @endif
                                    </span>
                                </div>
                                <p class="mt-2 text-lg font-extrabold text-[#0b1c30]">
                                    Rp{{ number_format($nominalDp, 0, ',', '.') }}
                                </p>
                                <p class="mt-1 text-xs leading-5 text-[#474651]">
                                    Bayar uang muka Rp{{ number_format($nominalDp, 0, ',', '.') }}, sisa dilunasi di meja kasir saat datang.
                                </p>
                            </button>

                            {{-- Opsi B: Bayar Lunas 100% --}}
                            <button type="button"
                                    id="card-tipe-full"
                                    onclick="setTipeBayar('full')"
                                    class="text-left w-full relative flex flex-col p-4 rounded-xl border-2 transition-all cursor-pointer {{ $tipeBayar === 'full' ? 'border-[#1a146b] bg-[#eff4ff] shadow-sm ring-1 ring-[#1a146b]' : 'border-[#e8edfa] bg-white hover:border-[#cbd5e1]' }}">
                                <div class="flex items-center justify-between">
                                    <span id="label-tipe-full" class="text-xs font-bold uppercase tracking-wide {{ $tipeBayar === 'full' ? 'text-[#1a146b]' : 'text-[#474651]' }}">
                                        Bayar Lunas (100%)
                                    </span>
                                    <span id="radio-tipe-full" class="h-4 w-4 rounded-full border-2 flex items-center justify-center {{ $tipeBayar === 'full' ? 'border-[#1a146b] bg-[#1a146b]' : 'border-gray-300' }}">
                                        @if($tipeBayar === 'full')
                                            <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                                        @endif
                                    </span>
                                </div>
                                <p class="mt-2 text-lg font-extrabold text-[#0b1c30]">
                                    Rp{{ number_format($totalHarga, 0, ',', '.') }}
                                </p>
                                <p class="mt-1 text-xs leading-5 text-[#416900] font-semibold">
                                    Bayar lunas Rp{{ number_format($totalHarga, 0, ',', '.') }}, langsung terverifikasi tanpa antre kasir.
                                </p>
                            </button>
                        </div>
                    </div>

                    {{-- Card Informasi Midtrans Snap --}}
                    <div class="rounded-xl border border-[#dce9ff] bg-[#f8faff] p-5">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#1a146b] text-xl text-[#acf847] shadow-xs">
                                💳
                            </span>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-bold text-[#1a146b] text-base">
                                        Metode Pembayaran Tersedia via Midtrans Snap
                                    </h3>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-[#dcfce7] px-2.5 py-0.5 text-xs font-semibold text-[#166534]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-[#22c55e]"></span>
                                        Aktif &amp; Terverifikasi
                                    </span>
                                </div>
                                <p class="mt-1 text-xs leading-5 text-[#474651]">
                                    (QRIS GoPay/ShopeePay, Transfer Virtual Account BCA/BRI/BNI/Mandiri, Kartu Kredit)
                                </p>
                            </div>
                        </div>

                        <p class="mt-4 text-sm leading-6 text-[#474651]">
                            Saat Anda menekan tombol <strong class="text-[#1a146b]">Bayar Sekarang</strong>, modal pop-up resmi Midtrans Snap akan muncul di layar. Anda dapat memilih salah satu kanal pembayaran instan berikut:
                        </p>

                        {{-- Channel Pembayaran --}}
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div class="flex items-center gap-3 rounded-xl border border-[#e5eeff] bg-white p-3.5 shadow-2xs">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#eff4ff] text-base text-[#1a146b] font-bold">
                                    ▦
                                </span>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-[#0b1c30]">QRIS &amp; E-Wallet</p>
                                    <p class="truncate text-[11px] text-[#777682]">GoPay, ShopeePay, OVO, Dana</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 rounded-xl border border-[#e5eeff] bg-white p-3.5 shadow-2xs">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#eff4ff] text-base text-[#1a146b] font-bold">
                                    🏦
                                </span>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-[#0b1c30]">Virtual Account / VA</p>
                                    <p class="truncate text-[11px] text-[#777682]">BCA, BRI, BNI, Mandiri, Permata</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 rounded-xl border border-[#e5eeff] bg-white p-3.5 shadow-2xs">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#eff4ff] text-base text-[#1a146b] font-bold">
                                    💳
                                </span>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-[#0b1c30]">Kartu Kredit / Debit</p>
                                    <p class="truncate text-[11px] text-[#777682]">Visa, Mastercard, JCB</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 rounded-xl border border-[#e5eeff] bg-white p-3.5 shadow-2xs">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#eff4ff] text-base text-[#1a146b] font-bold">
                                    ⚡
                                </span>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-[#0b1c30]">Verifikasi Otomatis</p>
                                    <p class="truncate text-[11px] text-[#777682]">E-Tiket &amp; Kode QR terbit seketika</p>
                                </div>
                            </div>
                        </div>

                        {{-- Rincian Nominal yang Dibayar dalam Card --}}
                        <div class="mt-4 flex items-center justify-between gap-3 rounded-xl bg-white p-4 border border-[#e5eeff]">
                            <div>
                                <p class="text-xs font-bold text-[#0b1c30]">
                                    Nominal yang Harus Dibayar (<span id="snap-nominal-label">{{ $tipeBayar === 'full' ? '100% Lunas' : '50% DP' }}</span>)
                                </p>
                                <p id="snap-nominal-sub" class="mt-0.5 text-[11px] text-[#777682]">
                                    {{ $tipeBayar === 'full' ? 'Pembayaran lunas online penuh via Midtrans Snap' : 'Pembayaran awal uang muka via Midtrans Snap' }}
                                </p>
                            </div>

                            <span id="snap-nominal-val" class="shrink-0 text-right font-extrabold text-base text-[#1a146b]">
                                Rp{{ number_format($nominalBayar, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </section>
            </div>

            {{-- KOLOM KANAN: RINGKASAN --}}
            <aside class="lg:sticky lg:top-6 lg:col-span-5">
                <section class="overflow-hidden rounded-2xl border border-[#e8edfa] bg-white shadow-md">

                    <div class="px-5 py-5 sm:px-6">
                        <h2 class="flex items-center gap-2 text-sm font-extrabold uppercase tracking-wider text-[#416900]">
                            <span class="h-2 w-2 rounded-full bg-[#416900]"></span>
                            Ringkasan Booking
                        </h2>

                        <div class="mt-5 rounded-xl bg-[#eff4ff] p-4">
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
                                <span class="text-base animate-pulse">⏱️</span>
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
                    <span aria-hidden="true">🔒</span>
                    Detail booking Lapangin
                </div>
            </aside>
        </div>
    </main>
</div>

{{-- Integrasi Script Midtrans Snap SDK & Timer Countdown 10 Menit --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
    // Konfigurasi dinamis Tipe Pembayaran (DP vs Full) tanpa refresh halaman
    const totalHarga = {{ (float) $totalHarga }};
    const nominalDp = {{ (float) $nominalDp }};
    window.currentTipeBayar = "{{ $tipeBayar }}";

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

        // 5. Teks Tombol Bayar Sekarang
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
                    jadwal_id: "{{ $jadwalId ?? '' }}",
                    booking_id: "{{ $bookingId ?? ($jadwalId ?? '') }}"
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
                    alert("Midtrans Snap SDK tidak berhasil dimuat. Pastikan koneksi internet aktif.");
                    payButton.disabled = false;
                    payButton.classList.remove('opacity-70', 'cursor-not-allowed');
                    buttonText.innerText = originalText;
                    return;
                }

                window.snap.pay(snapToken, {
                    onSuccess: function(result) {
                        window.location.href = "{{ route('pelanggan.booking.index') }}?status=success";
                    },
                    onPending: function(result) {
                        window.location.href = "{{ route('pelanggan.booking.index') }}?status=pending";
                    },
                    onError: function(result) {
                        alert("Pembayaran gagal, silakan coba kembali.");
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
                alert(error.message || 'Terjadi kesalahan sistem, silakan coba kembali.');
                payButton.disabled = false;
                payButton.classList.remove('opacity-70', 'cursor-not-allowed');
                buttonText.innerText = originalText;
            });
        });
    });
</script>
@endsection
