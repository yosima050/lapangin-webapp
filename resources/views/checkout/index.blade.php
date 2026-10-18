
@extends('layouts.app')

@section('title', 'Checkout & Pembayaran DP')

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
                    <span>Pembayaran DP</span>
                </div>

                <span class="h-0.5 w-8 bg-[#d3e4fe]"></span>

                <div class="flex items-center gap-2 text-gray-400">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#dce9ff]">3</span>
                    <span>E-Tiket Siap</span>
                </div>
            </div>

            <span class="rounded-full bg-[#dce9ff] px-4 py-2 text-xs font-bold text-[#1a146b]">
                LAPANGIN · CHECKOUT
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
                Checkout & Pembayaran DP
            </h1>

            <p class="mt-2 text-sm leading-6 text-[#474651]">
                Periksa detail booking sebelum melanjutkan proses pembayaran.
            </p>
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

                {{-- Metode Pembayaran --}}
                <section class="rounded-2xl border border-[#e8edfa] bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <h2 class="flex items-center gap-2 text-lg font-bold text-[#0b1c30]">
                            <span class="text-xl text-[#1a146b]">▣</span>
                            Metode Pembayaran DP
                        </h2>

                        <span class="rounded-full bg-[#acf847] px-3 py-1 text-xs font-bold text-[#304f00]">
                            DP {{ $dpPersen }}%
                        </span>
                    </div>

                    {{-- QRIS placeholder --}}
                    <div class="rounded-xl border border-[#e5eeff] p-4 sm:p-5">
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-[#91db2a]"></span>
                            <h3 class="font-bold text-[#1a146b]">QRIS</h3>
                        </div>

                        <p class="mt-2 text-sm leading-6 text-[#474651]">
                            Pembayaran QRIS belum tersedia karena integrasi sistem pembayaran
                            belum selesai.
                        </p>

                        <div class="mt-5 flex flex-col items-center justify-center rounded-xl bg-[#eff4ff] px-5 py-8 text-center">
                            <div class="flex h-24 w-24 items-center justify-center rounded-2xl bg-white text-4xl text-[#1a146b] shadow-sm">
                                ▦
                            </div>

                            <h3 class="mt-4 font-bold text-[#0b1c30]">
                                QRIS Lapangin
                            </h3>

                            <p class="mt-2 max-w-sm text-sm leading-6 text-[#474651]">
                                Kode QR pembayaran asli akan muncul setelah backend
                                pembayaran terhubung.
                            </p>

                            <span class="mt-4 rounded-full bg-[#dce9ff] px-4 py-2 text-xs font-bold text-[#1a146b]">
                                Menunggu Integrasi Pembayaran
                            </span>
                        </div>

                        <div class="mt-5 flex items-center justify-between gap-3 rounded-xl bg-[#eff4ff] p-4">
                            <div>
                                <p class="font-bold text-[#0b1c30]">Nominal DP</p>
                                <p class="mt-1 text-xs text-[#474651]">
                                    Pembayaran awal sebesar {{ $dpPersen }}% dari total tagihan
                                </p>
                            </div>

                            <span class="shrink-0 text-right font-extrabold text-[#1a146b]">
                                Rp{{ number_format($nominalDp, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    {{-- Metode lain belum aktif --}}
                    <div class="mt-4 space-y-3">
                        <div class="flex items-center justify-between gap-3 rounded-xl bg-[#eff4ff] p-4">
                            <div class="flex items-center gap-3">
                                <span class="text-xl text-[#1a146b]">▤</span>
                                <div>
                                    <p class="font-bold text-[#0b1c30]">Virtual Account</p>
                                    <p class="mt-1 text-xs text-[#474651]">
                                        Transfer melalui bank
                                    </p>
                                </div>
                            </div>
                            <span class="shrink-0 text-xs text-gray-500">Segera hadir</span>
                        </div>

                        <div class="flex items-center justify-between gap-3 rounded-xl bg-[#eff4ff] p-4">
                            <div class="flex items-center gap-3">
                                <span class="text-xl text-[#1a146b]">▰</span>
                                <div>
                                    <p class="font-bold text-[#0b1c30]">Kartu Kredit / Debit</p>
                                    <p class="mt-1 text-xs text-[#474651]">
                                        Pembayaran dengan kartu
                                    </p>
                                </div>
                            </div>
                            <span class="shrink-0 text-xs text-gray-500">Segera hadir</span>
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

                        {{-- Panel DP --}}
                        <div class="mt-5 rounded-xl bg-[#1a146b] p-5 text-white">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs font-bold uppercase tracking-wide text-[#e2dfff]">
                                    DP yang Harus Dibayar
                                </span>

                                <span class="rounded-md bg-[#416900] px-2 py-1 text-xs font-bold text-white">
                                    {{ $dpPersen }}%
                                </span>
                            </div>

                            <p class="mt-3 text-2xl font-extrabold">
                                Rp{{ number_format($nominalDp, 0, ',', '.') }}
                            </p>

                            <div class="mt-4 border-t border-white/20 pt-4">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm text-[#e2dfff]">
                                        Sisa pembayaran di kasir
                                    </span>

                                    <span class="text-right font-bold text-[#acf847]">
                                        Rp{{ number_format($sisaPembayaran, 0, ',', '.') }}
                                    </span>
                                </div>

                                <p class="mt-2 text-xs leading-5 text-[#e2dfff]">
                                    Sisa tagihan dibayarkan di kasir. Pastikan pembayaran DP
                                    sudah terverifikasi sebelum menggunakan e-tiket.
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

                        <button type="button"
                                disabled
                                class="mt-5 flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl bg-[#416900] px-5 py-4 font-bold text-white opacity-60"
                                title="Menunggu integrasi pembayaran">
                            Pembayaran Belum Tersedia
                            <span aria-hidden="true">🔒</span>
                        </button>

                        <p class="mt-3 text-center text-xs leading-5 text-[#777682]">
                            Tombol akan diaktifkan setelah proses pembayaran terintegrasi.
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
@endsection
