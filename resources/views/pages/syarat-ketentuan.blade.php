@extends('layouts.app')

@section('title', 'Ketentuan Layanan')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6 font-medium">
        <a href="{{ route('home') }}" class="hover:text-[#1a146b] flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">home</span>
            <span>Beranda</span>
        </a>
        <span>/</span>
        <span class="text-gray-900 font-bold">Ketentuan Layanan</span>
    </nav>

    {{-- Article Container --}}
    <article class="bg-white rounded-3xl p-6 sm:p-10 shadow-xs border border-gray-100 flex flex-col gap-6">

        <header class="border-b border-gray-100 pb-5">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#eff4ff] text-[#1a146b] text-xs font-black uppercase tracking-wider mb-2">
                <span class="material-symbols-outlined text-sm">gavel</span>
                SYARAT &amp; KETENTUAN PENGGUNA
            </div>
            <h1 class="text-2xl sm:text-3xl font-headline font-black text-[#0b1c30] tracking-tight">
                Ketentuan Layanan &amp; Tata Tertib LapangIn
            </h1>
            <p class="text-xs text-gray-400 mt-1">Terakhir diperbarui: 29 Oktober 2026</p>
        </header>

        <section class="space-y-4 text-xs sm:text-sm text-gray-600 leading-relaxed">
            <h2 class="text-base font-headline font-bold text-[#0b1c30]">1. Ketentuan Umum Penggunaan Platform</h2>
            <p>
                Dengan mengakses website dan layanan reservasi LapangIn, pengguna menyatakan setuju untuk terikat oleh seluruh syarat dan ketentuan yang berlaku. Seluruh data pemesanan yang dibuat melalui akun Anda adalah tanggung jawab penuh pemegang akun.
            </p>

            <h2 class="text-base font-headline font-bold text-[#0b1c30] pt-4 border-t border-gray-100">2. Standar Penggunaan Lapangan &amp; Alas Kaki</h2>
            <p>
                Untuk menjaga mutu lantai turnamen berstandar AFC Flooring dan karpet Yonex, seluruh pemain wajib mematuhi ketentuan alas kaki berikut:
            </p>
            <ul class="list-disc list-inside space-y-1 ml-2 text-gray-600">
                <li><strong>Lapangan Futsal Vinyl &amp; Interlock:</strong> Wajib menggunakan sepatu futsal dengan sol karet non-marking (tanpa stud / pul tajam). Dilarang keras menggunakan sepatu sepak bola outdoor bersol stud/spikes.</li>
                <li><strong>Badminton Court:</strong> Wajib menggunakan sepatu badminton sol karet mentah (*gum sole*) yang bersih dan tidak meninggalkan jejak hitam.</li>
                <li><strong>Mini Soccer Turf:</strong> Menggunakan sepatu sol TF (turf) atau karet lembut.</li>
            </ul>

            <h2 class="text-base font-headline font-bold text-[#0b1c30] pt-4 border-t border-gray-100">3. Ketertiban &amp; Keamanan Gelanggang</h2>
            <ul class="list-disc list-inside space-y-1 ml-2 text-gray-600">
                <li>Pemain wajib tiba minimal 15 menit sebelum waktu kickoff untuk melakukan verifikasi check-in di kasir atau pemindai turnstile.</li>
                <li>Dilarang merokok, membawa senjata, atau minuman keras beralkohol di dalam seluruh area gelanggang olahraga.</li>
                <li>Penyewa bertanggung jawab penuh atas keselamatan barang bawaan masing-masing di area loker dan tribun. Pihak venue tidak bertanggung jawab atas kehilangan barang berharga.</li>
            </ul>

            <h2 class="text-base font-headline font-bold text-[#0b1c30] pt-4 border-t border-gray-100">4. Pembayaran &amp; Validasi Tiket</h2>
            <p>
                E-Tiket resmi dengan QR Code dan Token Manual 6-Karakter yang sah hanya diterbitkan melalui sistem LapangIn. Tiket yang telah digunakan untuk check-in tidak dapat digunakan ulang atau dipindahtangankan saat pertandingan sedang berjalan.
            </p>
        </section>

        <footer class="pt-6 border-t border-gray-100 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-xs font-bold text-[#1a146b] hover:underline flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">home</span>
                <span>Kembali ke Beranda</span>
            </a>
            <a href="{{ route('kebijakan.privasi') }}" class="text-xs font-bold text-gray-500 hover:text-gray-900">
                Baca Kebijakan Privasi &rarr;
            </a>
        </footer>

    </article>

</div>
@endsection
