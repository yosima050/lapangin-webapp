@extends('layouts.app')

@section('title', 'Pusat Bantuan & FAQ')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="{ openFaq: 1 }">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6 font-medium">
        <a href="{{ route('home') }}" class="hover:text-[#1a146b] flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">home</span>
            <span>Beranda</span>
        </a>
        <span>/</span>
        <span class="text-gray-900 font-bold">Pusat Bantuan</span>
    </nav>

    {{-- Header --}}
    <div class="mb-10 text-center max-w-2xl mx-auto">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#eff4ff] text-[#1a146b] text-xs font-black uppercase tracking-wider mb-3">
            <span class="material-symbols-outlined text-sm">help</span>
            BANTUAN &amp; PERTANYAAN UMUM
        </span>
        <h1 class="text-3xl sm:text-4xl font-headline font-black text-[#0b1c30] tracking-tight">
            Bagaimana Kami Bisa Membantu Anda?
        </h1>
        <p class="text-sm text-gray-600 mt-2">
            Temukan jawaban cepat seputar reservasi, skema pembayaran DP, batas kunci slot, dan prosedur check-in di kasir venue.
        </p>
    </div>

    {{-- Accordion List --}}
    <div class="space-y-4">

        {{-- FAQ 1 --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden transition">
            <button type="button" @click="openFaq = (openFaq === 1 ? null : 1)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-headline font-bold text-sm text-[#0b1c30] hover:text-[#1a146b] transition cursor-pointer">
                <span class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-lg bg-[#eaf7d8] text-[#416900] flex items-center justify-center text-xs font-black shrink-0">1</span>
                    <span>Apakah harus mendaftar akun untuk memesan lapangan secara online?</span>
                </span>
                <span class="material-symbols-outlined transition-transform duration-200 text-gray-400" :class="openFaq === 1 ? 'rotate-180 text-[#1a146b]' : ''">expand_more</span>
            </button>
            <div x-show="openFaq === 1" x-collapse x-cloak class="px-5 pb-5 text-xs text-gray-600 leading-relaxed border-t border-gray-50 pt-3">
                <p>
                    <strong>Ya, benar.</strong> Pemesanan online di website LapangIn mewajibkan pelanggan memiliki akun terdaftar. Hal ini bertujuan agar tiket pertandingan, bukti QR Code, dan hak pengajuan reschedule tersimpan aman di akun Anda.
                </p>
                <p class="mt-2 text-gray-500">
                    Bila Anda belum memiliki akun saat memilih lapangan, sistem otomatis menyimpan jadwal pilihan Anda dan mengarahkan ke halaman pendaftaran singkat sebelum menyelesaikan checkout.
                </p>
            </div>
        </div>

        {{-- FAQ 2 --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden transition">
            <button type="button" @click="openFaq = (openFaq === 2 ? null : 2)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-headline font-bold text-sm text-[#0b1c30] hover:text-[#1a146b] transition cursor-pointer">
                <span class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-lg bg-[#eaf7d8] text-[#416900] flex items-center justify-center text-xs font-black shrink-0">2</span>
                    <span>Bagaimana cara kerja skema Bayar DP 50% vs Lunas 100%?</span>
                </span>
                <span class="material-symbols-outlined transition-transform duration-200 text-gray-400" :class="openFaq === 2 ? 'rotate-180 text-[#1a146b]' : ''">expand_more</span>
            </button>
            <div x-show="openFaq === 2" x-collapse x-cloak class="px-5 pb-5 text-xs text-gray-600 leading-relaxed border-t border-gray-50 pt-3 space-y-2">
                <p>
                    <strong class="text-[#416900]">Bayar DP 50% (Rekomendasi):</strong> Anda hanya membayar separuh tarif sewa secara online via Midtrans Snap (Virtual Account, QRIS, Kartu Kredit). Sisa tagihan 50% dibayar tunai atau QRIS saat tiba di meja kasir venue sebelum masuk lapangan.
                </p>
                <p>
                    <strong class="text-[#1a146b]">Bayar Lunas 100%:</strong> Anda membayar tagihan penuh saat checkout. Di hari pertandingan, Anda dapat langsung melakukan pemindaian QR turnstile tanpa urusan administrasi kasir.
                </p>
            </div>
        </div>

        {{-- FAQ 3 --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden transition">
            <button type="button" @click="openFaq = (openFaq === 3 ? null : 3)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-headline font-bold text-sm text-[#0b1c30] hover:text-[#1a146b] transition cursor-pointer">
                <span class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-lg bg-[#eaf7d8] text-[#416900] flex items-center justify-center text-xs font-black shrink-0">3</span>
                    <span>Berapa lama batas waktu pembayaran sebelum slot dibatalkan?</span>
                </span>
                <span class="material-symbols-outlined transition-transform duration-200 text-gray-400" :class="openFaq === 3 ? 'rotate-180 text-[#1a146b]' : ''">expand_more</span>
            </button>
            <div x-show="openFaq === 3" x-collapse x-cloak class="px-5 pb-5 text-xs text-gray-600 leading-relaxed border-t border-gray-50 pt-3">
                <p>
                    Slot terkunci selama <strong>10 menit</strong> sejak tombol checkout ditekan. Jika Anda tidak menyelesaikan pembayaran dalam kurun waktu 10 menit, sistem latar belakang (*auto-cancel job*) akan otomatis membatalkan pesanan dan mengembalikan slot tersebut ke status "Tersedia" agar dapat dipesan oleh pemain lain.
                </p>
            </div>
        </div>

        {{-- FAQ 4 --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden transition">
            <button type="button" @click="openFaq = (openFaq === 4 ? null : 4)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-headline font-bold text-sm text-[#0b1c30] hover:text-[#1a146b] transition cursor-pointer">
                <span class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-lg bg-[#eaf7d8] text-[#416900] flex items-center justify-center text-xs font-black shrink-0">4</span>
                    <span>Bagaimana cara melakukan check-in saat tiba di venue?</span>
                </span>
                <span class="material-symbols-outlined transition-transform duration-200 text-gray-400" :class="openFaq === 4 ? 'rotate-180 text-[#1a146b]' : ''">expand_more</span>
            </button>
            <div x-show="openFaq === 4" x-collapse x-cloak class="px-5 pb-5 text-xs text-gray-600 leading-relaxed border-t border-gray-50 pt-3 space-y-2">
                <p>
                    Anda cukup membuka menu <strong>Riwayat Booking</strong> &gt; <strong>Lihat E-Tiket</strong> di ponsel Anda, lalu:
                </p>
                <ol class="list-decimal list-inside space-y-1 ml-2 text-gray-500">
                    <li>Arahkan <strong>QR Code</strong> ke scanner kamera kasir atau scanner turnstile gerbang lapangan.</li>
                    <li>Atau sebutkan <strong>Token Manual 6-Karakter</strong> (contoh: <code>A1B2C3</code>) kepada petugas resepsionis kasir.</li>
                </ol>
            </div>
        </div>

        {{-- FAQ 5 --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden transition">
            <button type="button" @click="openFaq = (openFaq === 5 ? null : 5)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-headline font-bold text-sm text-[#0b1c30] hover:text-[#1a146b] transition cursor-pointer">
                <span class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-lg bg-[#eaf7d8] text-[#416900] flex items-center justify-center text-xs font-black shrink-0">5</span>
                    <span>Apakah jadwal pertandingan bisa di-reschedule atau dibatalkan?</span>
                </span>
                <span class="material-symbols-outlined transition-transform duration-200 text-gray-400" :class="openFaq === 5 ? 'rotate-180 text-[#1a146b]' : ''">expand_more</span>
            </button>
            <div x-show="openFaq === 5" x-collapse x-cloak class="px-5 pb-5 text-xs text-gray-600 leading-relaxed border-t border-gray-50 pt-3">
                <p>
                    <strong>Bisa.</strong> Pengajuan reschedule dapat dilakukan minimal <strong>24 jam sebelum waktu kickoff</strong> melalui tombol "Ajukan Reschedule" pada tiket Anda.
                </p>
                <p class="mt-2">
                    Untuk pembatalan: pembatalan lebih dari 24 jam sebelum kickoff dikenakan denda administrasi 10%. Pembatalan mepet kurang dari 24 jam dikenakan penalti DP hangus (50%). Silakan baca <a href="{{ route('kebijakan.reschedule') }}" class="font-bold text-[#1a146b] underline">Kebijakan Reschedule Lengkap</a>.
                </p>
            </div>
        </div>

    </div>

    {{-- Contact Banner --}}
    <div class="mt-12 bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-headline font-bold text-[#0b1c30]">Masih memiliki pertanyaan lain?</h3>
            <p class="text-xs text-gray-500 mt-0.5">Petugas kami siap melayani informasi ketersediaan jadwal dan booking korporat.</p>
        </div>
        <a href="{{ route('kontak') }}" class="py-3 px-5 rounded-2xl bg-[#1a146b] hover:bg-[#2a229c] text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5 shrink-0">
            <span class="material-symbols-outlined text-base">support_agent</span>
            <span>Hubungi Kami</span>
        </a>
    </div>

</div>
@endsection
