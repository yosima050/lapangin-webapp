@extends('layouts.pelanggan')

@section('content')
<div x-data="{ showTicketModal: false, activeTicket: null }" class="min-h-screen bg-gray-50 px-4 py-8 sm:py-10">

        {{-- Header Riwayat Booking --}}
        <div class="mb-8 overflow-hidden rounded-2xl bg-[#1a146b] text-white shadow-lg sm:rounded-3xl">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between px-5 py-7 sm:px-8 sm:py-9 lg:px-10 gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-[#acf847]">
                        <span class="h-2 w-2 rounded-full bg-[#acf847]"></span>
                        Manajemen Reservasi Aktif
                    </div>

                    <h1 class="mt-4 text-2xl font-bold tracking-tight sm:text-3xl lg:text-4xl">
                        Riwayat Booking &amp; Jadwal Saya
                    </h1>

                    <p class="mt-3 max-w-xl text-sm leading-6 text-indigo-100 sm:text-base">
                        Pantau tiket akses gerbang, unduh bukti pembayaran lunas, atau kelola permohonan penjadwalan ulang lapangan secara mandiri.
                    </p>
                </div>

                {{-- Statistik Ringkasan ala Figma --}}
                <div class="grid grid-cols-3 gap-3 rounded-2xl bg-white/10 p-4 backdrop-blur-md border border-white/10 shrink-0">
                    <div class="text-center px-3 border-r border-white/10 last:border-none">
                        <p class="text-xl sm:text-2xl font-extrabold text-white">
                            {{ isset($jadwals) ? count($jadwals) : 0 }}
                        </p>
                        <p class="mt-0.5 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-indigo-200">
                            Jadwal Aktif
                        </p>
                    </div>
                    <div class="text-center px-3 border-r border-white/10 last:border-none">
                        <p class="text-xl sm:text-2xl font-extrabold text-white">0</p>
                        <p class="mt-0.5 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-indigo-200">
                            Selesai
                        </p>
                    </div>
                    <div class="text-center px-3">
                        <p class="text-xl sm:text-2xl font-extrabold text-[#acf847]">100%</p>
                        <p class="mt-0.5 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-indigo-200">
                            Validasi QR
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notifikasi --}}
        @if (session('success'))
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">
                <span class="font-bold">✓</span>
                <p class="text-sm">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Filter & Pencarian ala Figma --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            {{-- Tab Filter Status --}}
            <div class="flex flex-wrap items-center gap-2 rounded-2xl bg-gray-200/60 p-1.5 text-xs font-semibold text-gray-600">
                <button type="button" class="rounded-xl px-4 py-2 transition text-gray-600 hover:text-gray-900">
                    Semua ({{ isset($jadwals) ? count($jadwals) : 0 }})
                </button>
                <button type="button" class="inline-flex items-center gap-1.5 rounded-xl bg-[#1a146b] px-4 py-2 font-bold text-white shadow-sm">
                    Aktif &amp; Mendatang
                    <span class="flex h-4 w-4 items-center justify-center rounded-full bg-[#acf847] text-[10px] font-extrabold text-[#1a146b]">
                        {{ isset($jadwals) ? count($jadwals) : 0 }}
                    </span>
                </button>
                <button type="button" class="rounded-xl px-4 py-2 transition text-gray-600 hover:text-gray-900">
                    Selesai (0)
                </button>
                <button type="button" class="rounded-xl px-4 py-2 transition text-gray-600 hover:text-gray-900">
                    Dibatalkan (0)
                </button>
            </div>

            {{-- Input Pencarian --}}
            <div class="relative w-full sm:w-72">
                <input type="text"
                       placeholder="Cari Kode Booking / Lapangan..."
                       class="w-full rounded-2xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-xs shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Sub-header Judul Bagian --}}
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-gray-800">
                    Jadwal Pertandingan Terdekat
                </h2>
            </div>
            <p class="text-[11px] text-gray-400">
                Update sinkronisasi: Baru saja
            </p>
        </div>

        {{-- Daftar Kartu Booking Aktif --}}
        @forelse ($jadwals as $jadwal)
            @php
                $lunas = in_array($jadwal->status_pembayaran, [
                    'lunas_online',
                    'lunas_kasir'
                ]);

                $statusLabel = ucfirst(str_replace(
                    '_',
                    ' ',
                    $jadwal->status_pembayaran ?? 'belum_bayar'
                ));

                // Format kode booking pendek
                $rawKode = $jadwal->kode_booking ?? $jadwal->id;
                $displayKode = strlen($rawKode) > 12 ? '#BK-' . strtoupper(substr($rawKode, 0, 8)) : '#BK-' . $rawKode;

                // Menghitung durasi jam
                $mulaiMenit = \Carbon\Carbon::parse($jadwal->jam_mulai)->hour * 60 + \Carbon\Carbon::parse($jadwal->jam_mulai)->minute;
                $selesaiMenit = \Carbon\Carbon::parse($jadwal->jam_selesai)->hour * 60 + \Carbon\Carbon::parse($jadwal->jam_selesai)->minute;
                $durasiJam = max(1, round(($selesaiMenit - $mulaiMenit) / 60));
            @endphp

            <article x-data="{ showModal: false }" class="mb-6 overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-sm">

                {{-- Baris Atas: Kode Booking & Badge Status --}}
                <div class="flex flex-wrap items-center justify-between border-b border-gray-100 bg-indigo-50/30 px-5 py-3 gap-2">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="font-mono font-bold text-[#1a146b] bg-indigo-100/70 px-2.5 py-1 rounded-md">
                            {{ $displayKode }}
                        </span>
                        <span class="text-gray-300">•</span>
                        <span class="text-gray-500">
                            Dipesan: {{ \Carbon\Carbon::parse($jadwal->created_at)->translatedFormat('d M Y, H:i') }} WIB
                        </span>
                    </div>

                    {{-- Badge Status Utama --}}
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold
                        {{ $lunas
                            ? 'bg-green-100 text-green-800'
                            : 'bg-[#acf847]/40 text-emerald-950 border border-[#acf847]' }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $lunas ? 'bg-green-600' : 'bg-emerald-600' }}"></span>
                        {{ $statusLabel }} {{ !$lunas ? '(Siap Main)' : '' }}
                    </span>
                </div>

                {{-- Konten Utama 3 Kolom ala Figma --}}
                <div class="p-5">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-center">
                        
                        {{-- Kolom 1: Thumbnail Venue & Nama Lapangan --}}
                        <div class="lg:col-span-4 flex items-stretch gap-4">
                        {{-- Gambar Full Height ala Katalog --}}
                        <div class="relative w-28 sm:w-32 shrink-0 overflow-hidden rounded-xl bg-gray-900 border border-gray-100 self-stretch flex items-center justify-center">
                            @php
                                $namaLapangan = $jadwal->lapangan?->nama ?? 'Lapangan';
                                $fotoUrl = $jadwal->lapangan?->foto_url ?? asset('storage/' . $jadwal->lapangan?->foto);
                                $fallbackAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($namaLapangan) . '&background=1a146b&color=acf847&size=512';
                            @endphp

                            <img src="{{ $jadwal->lapangan?->foto ? $fotoUrl : $fallbackAvatar }}"
                                alt="{{ $namaLapangan }}"
                                class="h-full w-full object-cover object-center transition duration-300 hover:scale-105"
                                onerror="this.onerror=null; this.src='{{ $fallbackAvatar }}';">

                            <span class="absolute top-2 left-2 rounded-md bg-[#1a146b]/90 backdrop-blur-md px-2 py-0.5 text-[8px] font-bold text-[#acf847] uppercase tracking-wider shadow-xs">
                                {{ $jadwal->lapangan?->kategori ?? 'VENUE' }}
                            </span>
                        </div>

                        {{-- Detail Nama & Spesifikasi --}}
                        <div class="min-w-0 flex-1 py-1">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">GELANGGANG UTAMA</p>
                            <h3 class="text-base font-bold text-gray-900 leading-snug truncate">
                                {{ $jadwal->lapangan?->nama ?? 'Lapangan Tidak Tersedia' }}
                            </h3>
                            <p class="mt-1.5 text-xs text-gray-500 flex items-center gap-1">
                                <span>🛡️</span> {{ $jadwal->lapangan?->tipe_lantai ?? 'Standard FIFA Flooring' }}
                            </p>
                            <p class="mt-0.5 text-xs text-gray-500 flex items-center gap-1">
                                <span>⚙️</span> Rompi &amp; Bola Termasuk
                            </p>
                        </div>
                    </div>

                        {{-- Kolom 2: Detail Jadwal & Pembayaran --}}
                        <div class="lg:col-span-5 rounded-2xl bg-indigo-50/40 border border-indigo-50 p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Jadwal Kickoff</p>
                                    <div class="mt-1 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#1a146b]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                                        </svg>
                                        <span class="text-sm font-bold text-gray-900">
                                            {{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('l, d F Y') }}
                                        </span>
                                    </div>
                                    <div class="mt-1 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#1a146b]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-sm font-extrabold text-[#1a146b]">
                                            {{ substr((string) $jadwal->jam_mulai, 0, 5) }} – {{ substr((string) $jadwal->jam_selesai, 0, 5) }} <span class="text-xs font-normal text-gray-500">WIB</span>
                                        </span>
                                    </div>
                                </div>
                                <span class="rounded-lg bg-indigo-100 px-2.5 py-1 text-[11px] font-bold text-indigo-900">
                                    {{ $durasiJam }} Jam Sesi
                                </span>
                            </div>

                            <div class="mt-3 border-t border-indigo-100/60 pt-2 flex justify-between text-xs">
                                <span class="text-gray-500">Total Harga:</span>
                                <span class="font-bold text-gray-900">Rp{{ number_format((float) $jadwal->harga_disepakati, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Kolom 3: Tombol Aksi Kanan --}}
                        <div class="lg:col-span-3 flex flex-col gap-2">
                            @if ($lunas)
                                <button type="button" @click="showModal = true" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#1a146b] px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-900 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2a1 1 0 001-1v-5a1 1 0 00-1-1h-2M6 8H4a1 1 0 00-1 1v5a1 1 0 001 1h2m13-8V4a1 1 0 00-1-1H6a1 1 0 00-1 1v4m16 0e" />
                                    </svg>
                                    Lihat E-Tiket Masuk &amp; QR
                                </button>
                            @else
                                <button type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gray-200 px-4 py-2.5 text-xs font-bold text-gray-600 cursor-not-allowed">
                                    E-Tiket Belum Tersedia
                                </button>
                            @endif

                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" class="inline-flex items-center justify-center gap-1 rounded-xl border border-gray-200 bg-white px-2.5 py-2 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 transition">
                                    🔄 Reschedule
                                </button>
                                <button type="button" class="inline-flex items-center justify-center gap-1 rounded-xl border border-red-100 bg-red-50/50 px-2.5 py-2 text-[11px] font-semibold text-red-600 hover:bg-red-50 transition">
                                    🚫 Batalkan
                                </button>
                            </div>

                            <p class="text-[10px] text-center text-gray-400">
                                Tunjukkan kode QR ke gerbang turnstile
                            </p>
                        </div>

                    </div>
                </div>
{{-- POP-UP MODAL E-TICKET & QR CODE FIGMA --}}
@if (isset($lunas) && $lunas)
    <div x-show="showModal" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6" 
         style="display: none;">
        
        <div @click.away="showModal = false" 
             class="relative w-full max-w-4xl rounded-3xl bg-white shadow-2xl overflow-hidden p-6 sm:p-8 space-y-6 max-h-[92vh] overflow-y-auto text-left border border-gray-100">
            
            {{-- Tombol Close X --}}
            <button @click="showModal = false" 
                    type="button" 
                    class="absolute top-5 right-5 w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 font-bold transition z-10">
                ✕
            </button>

            {{-- Header Status --}}
            <div class="text-center space-y-2 pt-2">
                <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-[#acf847] text-[#1a146b] shadow-xs">
                    <span class="text-xl font-black">✓</span>
                </div>
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-extrabold uppercase tracking-wider">
                        ● Akses Lapangan Aktif
                    </span>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-[#1a146b] mt-1">
                        Reservasi Berhasil Dikonfirmasi!
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Kode Booking <span class="font-mono font-bold text-gray-800">#{{ $jadwal->kode_booking ?? ($jadwal->kode_qr ?? $displayKode) }}</span> telah aktif dan tersinkronisasi otomatis di sistem kasir venue.
                    </p>
                </div>
            </div>

            {{-- Boarding Pass Card (Grid 2 Kolom) --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12">
                
                {{-- Kiri: Detail Match Pass --}}
                <div class="lg:col-span-8 p-6 space-y-5">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <span class="px-3 py-1 rounded-full bg-[#1a146b] text-white text-[10px] font-extrabold uppercase tracking-wider">
                            OFFICIAL MATCH PASS
                        </span>
                        <span class="text-[10px] font-mono text-gray-400 font-bold uppercase">
                            LAPANGIN SECURE ID: {{ $jadwal->kode_booking ?? ($jadwal->kode_qr ?? $displayKode) }}
                        </span>
                    </div>

                    <div>
                        <h3 class="text-lg sm:text-xl font-extrabold text-[#1a146b]">
                            {{ $jadwal->lapangan?->nama }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                            ⚽ <span>{{ $jadwal->lapangan?->kategori }}</span> 
                            @if($jadwal->lapangan?->tipe_lantai)
                                • <span>{{ $jadwal->lapangan?->tipe_lantai }}</span>
                            @endif
                        </p>
                    </div>

                    {{-- Pemesan & Tim --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-[#f8f9ff] p-3.5 rounded-2xl border border-indigo-50">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-[#1a146b] text-white flex items-center justify-center font-bold text-xs shrink-0 uppercase">
                                {{ strtoupper(substr(Auth::user()->nama ?? Auth::user()->name ?? 'P', 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">PEMESAN RESMI</p>
                                <p class="text-xs font-bold text-gray-800 truncate">{{ Auth::user()->nama ?? Auth::user()->name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-gray-100">
                            <span class="text-sm">👥</span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-800 truncate">{{ $jadwal->nama_tim ?? 'Pemain Umum' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Schedule Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                        <div class="bg-[#f8f9ff] p-3 rounded-2xl border border-indigo-50">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">TANGGAL MAIN</p>
                            <p class="font-bold text-gray-800 mt-1">{{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('l, d F Y') }}</p>
                        </div>
                        <div class="bg-[#f8f9ff] p-3 rounded-2xl border border-indigo-50">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">WAKTU KICK-OFF</p>
                            <p class="font-bold text-[#1a146b] mt-1">{{ substr((string) $jadwal->jam_mulai, 0, 5) }} – {{ substr((string) $jadwal->jam_selesai, 0, 5) }} WIB</p>
                        </div>
                        <div class="bg-[#f8f9ff] p-3 rounded-2xl border border-indigo-50">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">STATUS PEMBAYARAN</p>
                            <p class="font-bold text-emerald-600 mt-1">
                                {{ strtolower($jadwal->status_pembayaran ?? '') === 'dp_terverifikasi' ? 'DP 50% Lunas' : 'Lunas 100%' }}
                            </p>
                        </div>
                    </div>

                    {{-- Total & Pelunasan --}}
                    @php
                        $totalHarga = (float) ($jadwal->harga_disepakati ?? 0)
                            + (float) ($jadwal->biaya_layanan ?? 0);

                        $isDP = $jadwal->status_pembayaran === 'dp_terverifikasi';

                        $onlineTerbayar = $isDP ? ($totalHarga / 2) : $totalHarga;
                        $sisaBayar = $isDP ? ($totalHarga / 2) : 0;
                    @endphp
                    <div class="flex items-center justify-between bg-indigo-50/50 p-3.5 rounded-2xl border border-indigo-100">
                        <div>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">RINCIAN PELUNASAN</p>
                            <p class="text-xs font-semibold text-gray-600">
                                Online Terbayar: <span class="font-bold text-gray-800">Rp {{ number_format($onlineTerbayar, 0, ',', '.') }}</span>
                            </p>
                        </div>
                        <div class="text-right">
                            @if($sisaBayar > 0)
                                <p class="text-[9px] text-gray-400 font-bold uppercase">Sisa Bayar di Kasir</p>
                                <p class="text-sm font-extrabold text-[#1a146b]">Rp {{ number_format($sisaBayar, 0, ',', '.') }}</p>
                                <p class="text-[8px] text-gray-400">(Tunai / QRIS saat check-in)</p>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                    ✓ Lunas Tanpa Sisa
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Kanan: Boarding Pass Gate (QR Code) --}}
                <div class="lg:col-span-4 bg-[#f4f7ff] p-6 flex flex-col items-center justify-center text-center border-t lg:border-t-0 lg:border-l border-gray-100">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400 mb-3">BOARDING PASS GATE</span>
                    
                    {{-- QR Code Image / SimpleSoftwareIO --}}
                    <div class="bg-white p-4 rounded-2xl shadow-xs border border-gray-100 mb-3">
                        @if (!empty($jadwal->kode_qr))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)->margin(1)->generate($jadwal->kode_qr) !!}
                        @else
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $jadwal->kode_booking ?? $displayKode }}" 
                                 alt="QR Code Tiket" 
                                 class="w-36 h-36 object-contain">
                        @endif
                    </div>

                    <p class="text-[10px] text-gray-500 font-medium">Scan di Meja Resepsionis</p>
                    
                    {{-- Token Manual --}}
                    <div class="mt-3 w-full bg-white py-2 px-3 rounded-xl border border-gray-200">
                        <p class="text-[8px] font-bold text-gray-400 uppercase">TOKEN MANUAL</p>
                        <p class="font-mono text-sm font-extrabold text-[#1a146b] tracking-wider">
                            {{ $jadwal->kode_qr ?? ($jadwal->kode_booking ?? $displayKode) }}
                        </p>
                    </div>
                </div>

            </div>

            {{-- Tombol Action --}}
            <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                <a href="https://wa.me/?text={{ urlencode('E-Tiket LapangIn untuk ' . $jadwal->lapangan?->nama . ' - Kode Booking: ' . ($jadwal->kode_booking ?? $displayKode)) }}" 
                   target="_blank"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-[#acf847] hover:bg-[#9be338] text-[#1a146b] font-extrabold text-xs transition shadow-xs">
                    💬 Bagikan Tiket ke WhatsApp Tim
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-2xl bg-white border border-gray-200 px-5 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50 transition shadow-xs">
                    📥 Unduh Tiket PDF
                </button>
            </div>

            {{-- Panduan & Protokol --}}
            <div class="pt-4 border-t border-gray-100">
                <h4 class="text-xs font-extrabold text-[#1a146b] flex items-center gap-1.5 mb-3">
                    🛡️ Panduan &amp; Protokol Hari Pertandingan
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-left">
                    <div class="bg-gray-50 p-3.5 rounded-2xl border border-gray-100 space-y-1">
                        <p class="text-[9px] font-extrabold uppercase text-[#416900]">ATURAN 01</p>
                        <h5 class="text-xs font-extrabold text-gray-900">Hadir 15 Menit Awal</h5>
                        <p class="text-gray-500 leading-relaxed text-[10px]">Pemanasan dan registrasi pengambilan rompi tim dapat dilakukan langsung di meja kasir sebelum kick-off.</p>
                    </div>

                    <div class="bg-gray-50 p-3.5 rounded-2xl border border-gray-100 space-y-1">
                        <p class="text-[9px] font-extrabold uppercase text-[#416900]">ATURAN 02</p>
                        <h5 class="text-xs font-extrabold text-gray-900">Wajib Sepatu Sol Karet (IC)</h5>
                        <p class="text-gray-500 leading-relaxed text-[10px]">Standar lantai mewajibkan sol indoor. Dilarang keras memakai stud/pull rumput outdoor.</p>
                    </div>

                    <div class="bg-gray-50 p-3.5 rounded-2xl border border-gray-100 space-y-1">
                        <p class="text-[9px] font-extrabold uppercase text-[#416900]">ATURAN 03</p>
                        <h5 class="text-xs font-extrabold text-gray-900">Area Parkir Luas &amp; Gratis</h5>
                        <p class="text-gray-500 leading-relaxed text-[10px]">Tersedia slot aman untuk mobil dan motor tim. Cukup tunjukkan E-Tiket kepada petugas keamanan.</p>
                    </div>
                </div>
            </div>

            {{-- Petunjuk Arah Arena --}}
            @if($jadwal->lapangan?->alamat || $jadwal->lapangan?->lokasi)
                <div class="bg-indigo-50/40 p-4 rounded-2xl border border-indigo-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-indigo-100 text-[#1a146b] flex items-center justify-center font-bold text-sm shrink-0">
                            📍
                        </div>
                        <div>
                            <h5 class="text-xs font-extrabold text-gray-900">Petunjuk Arah Menuju Arena</h5>
                            <p class="text-[11px] text-gray-500">{{ $jadwal->lapangan?->alamat ?? $jadwal->lapangan?->lokasi }}</p>
                        </div>
                    </div>
                    <a href="https://maps.google.com/?q={{ urlencode(($jadwal->lapangan?->nama ?? 'Lapangan') . ' ' . ($jadwal->lapangan?->alamat ?? '')) }}" 
                       target="_blank" 
                       class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-[#1a146b] font-bold text-xs hover:bg-gray-50 transition shrink-0">
                        Buka Google Maps ↗
                    </a>
                </div>
            @endif

        </div>
    </div>
@endif
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
                <h2 class="text-xl font-bold text-gray-900">Belum Ada Pesanan</h2>
                <p class="mt-2 text-sm text-gray-600">Booking lapangan Anda akan muncul di sini setelah pesanan tersedia.</p>
            </div>
        @endforelse

        {{-- Bagian Tabel Riwayat Transaksi Lalu ala Figma --}}
        <div class="mt-12 pt-6 border-t border-gray-200">
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-sm font-extrabold uppercase tracking-wider text-gray-900">
                        RIWAYAT TRANSAKSI LALU
                    </h2>
                    <p class="text-xs text-gray-500">
                        Catatan pemakaian lapangan dan arsip tanda terima digital resmi.
                    </p>
                </div>

                <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 transition w-fit">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Ekspor Laporan (PDF)
                </button>
            </div>

            {{-- Container Tabel Responsive --}}
            <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-xs">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-gray-100 bg-gray-50/80 font-bold uppercase text-gray-500">
                        <tr>
                            <th scope="col" class="px-5 py-3.5">KODE BOOKING</th>
                            <th scope="col" class="px-5 py-3.5">LAPANGAN &amp; FASILITAS</th>
                            <th scope="col" class="px-5 py-3.5">WAKTU MAIN</th>
                            <th scope="col" class="px-5 py-3.5">TOTAL BIAYA</th>
                            <th scope="col" class="px-5 py-3.5">STATUS</th>
                            <th scope="col" class="px-5 py-3.5 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                       
@php
    $riwayatTransaksi = $jadwals->getCollection()
        ->flatMap(function ($jadwal) {
            return $jadwal->transaksis->map(function ($transaksi) use ($jadwal) {
                return [
                    'jadwal' => $jadwal,
                    'transaksi' => $transaksi,
                ];
            });
        })
        ->sortByDesc(function ($item) {
            return $item['transaksi']->dibayar_pada;
        });
@endphp

@forelse ($riwayatTransaksi as $item)
    @php
        $jadwal = $item['jadwal'];
        $transaksi = $item['transaksi'];

        $statusClass = match ($transaksi->status) {
            'berhasil' => 'bg-green-50 text-green-700 border-green-200/60',
            'gagal' => 'bg-red-50 text-red-700 border-red-200/60',
            default => 'bg-yellow-50 text-yellow-700 border-yellow-200/60',
        };
    @endphp

    <tr class="hover:bg-gray-50/60 transition">
        <td class="px-5 py-4 font-mono font-bold text-gray-900">
            #{{ strtoupper(substr((string) $jadwal->id, 0, 8)) }}
        </td>

        <td class="px-5 py-4">
            <p class="font-bold text-gray-900">
                {{ $jadwal->lapangan->nama ?? 'Lapangan' }}
            </p>
            <p class="text-[11px] text-gray-400">
                {{ ucfirst($transaksi->jenis) }}
            </p>
        </td>

        <td class="px-5 py-4">
            <p class="font-medium text-gray-900">
                {{ $jadwal->tanggal?->format('d M Y') ?? '-' }}
            </p>
            <p class="text-[11px] text-gray-400">
                {{ $transaksi->dibayar_pada?->format('H:i') ?? 'Waktu belum tersedia' }}
            </p>
        </td>

        <td class="px-5 py-4">
            <p class="font-bold text-gray-900">
                Rp {{ number_format((float) $transaksi->jumlah, 0, ',', '.') }}
            </p>
            <p class="text-[11px] font-semibold text-gray-500">
                {{ ucfirst($transaksi->metode) }}
            </p>
        </td>

        <td class="px-5 py-4">
            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $statusClass }}">
                {{ ucfirst($transaksi->status) }}
            </span>
        </td>

        <td class="px-5 py-4 text-right text-xs text-gray-500">
            {{ $transaksi->dibayar_pada?->format('d/m/Y') ?? '-' }}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="px-5 py-10 text-center">
            <p class="font-semibold text-gray-700">Belum ada transaksi</p>
            <p class="mt-1 text-sm text-gray-500">
                Riwayat pembayaran akan muncul di sini setelah tersedia.
            </p>
        </td>
    </tr>
@endforelse
                    </tbody>
                </table>
            </div>


{{-- Script Alpine.js sederhana jika belum ada di layout --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

{{-- Pengaturan Cetak --}}
<style>
    @media print {
        body {
            background: white !important;
        }

        nav, header, footer {
            print-color-adjust: exact;
        }

        .print\:hidden {
            display: none !important;
        }

        article {
            break-inside: avoid;
            box-shadow: none !important;
        }
    }
</style>
@endsection