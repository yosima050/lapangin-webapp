@extends('layouts.app')

@section('title', 'Kebijakan Privasi')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6 font-medium">
        <a href="{{ route('home') }}" class="hover:text-[#1a146b] flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">home</span>
            <span>Beranda</span>
        </a>
        <span>/</span>
        <span class="text-gray-900 font-bold">Kebijakan Privasi</span>
    </nav>

    {{-- Article Container --}}
    <article class="bg-white rounded-3xl p-6 sm:p-10 shadow-xs border border-gray-100 flex flex-col gap-6">

        <header class="border-b border-gray-100 pb-5">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#eaf7d8] text-[#416900] text-xs font-black uppercase tracking-wider mb-2">
                <span class="material-symbols-outlined text-sm">security</span>
                PERLINDUNGAN DATA PRIBADI
            </div>
            <h1 class="text-2xl sm:text-3xl font-headline font-black text-[#0b1c30] tracking-tight">
                Kebijakan Privasi Pengguna LapangIn
            </h1>
            <p class="text-xs text-gray-400 mt-1">Terakhir diperbarui: 29 Oktober 2026</p>
        </header>

        <section class="space-y-4 text-xs sm:text-sm text-gray-600 leading-relaxed">
            <h2 class="text-base font-headline font-bold text-[#0b1c30]">1. Data yang Kami Kumpulkan</h2>
            <p>
                LapangIn mengumpulkan data pribadi yang Anda berikan secara sukarela saat registrasi akun atau pemesanan lapangan, termasuk:
            </p>
            <ul class="list-disc list-inside space-y-1 ml-2 text-gray-600">
                <li>Nama lengkap dan alamat email aktif.</li>
                <li>Nomor WhatsApp / telepon untuk pengiriman notifikasi E-Tiket dan konfirmasi kehadiran.</li>
                <li>Riwayat transaksi pemesanan lapangan dan sewa perlengkapan.</li>
            </ul>

            <h2 class="text-base font-headline font-bold text-[#0b1c30] pt-4 border-t border-gray-100">2. Penggunaan Data Pribadi</h2>
            <p>
                Informasi yang dikumpulkan digunakan semata-mata untuk:
            </p>
            <ul class="list-disc list-inside space-y-1 ml-2 text-gray-600">
                <li>Memproses reservasi lapangan dan menerbitkan E-Tiket QR Code resmi.</li>
                <li>Menghubungkan riwayat pemesanan *walk-in* lama ke akun resmi baru Anda secara otomatis (*auto-claim*).</li>
                <li>Verifikasi identitas pemesan saat check-in di kasir venue.</li>
                <li>Memfasilitasi pengembalian dana atau penjadwalan ulang (*reschedule*) jika terjadi pembatalan.</li>
            </ul>

            <h2 class="text-base font-headline font-bold text-[#0b1c30] pt-4 border-t border-gray-100">3. Keamanan Transaksi Finansial</h2>
            <p>
                Seluruh transaksi pembayaran digital (Virtual Account, QRIS, Kartu Kredit) diproses secara terenkripsi melalui gerbang pembayaran berlisensi Bank Indonesia (Midtrans). LapangIn <strong>tidak pernah menyimpan data sensitif</strong> seperti nomor kartu kredit atau PIN perbankan Anda di server kami.
            </p>

            <h2 class="text-base font-headline font-bold text-[#0b1c30] pt-4 border-t border-gray-100">4. Hak Pengguna</h2>
            <p>
                Pengguna berhak untuk memperbarui data profil, mengubah nomor kontak, atau meminta penghapusan akun sewaktu-waktu melalui halaman Profil Akun atau dengan menghubungi layanan bantuan pelanggan kami.
            </p>
        </section>

        <footer class="pt-6 border-t border-gray-100 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-xs font-bold text-[#1a146b] hover:underline flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">home</span>
                <span>Kembali ke Beranda</span>
            </a>
            <a href="{{ route('syarat.ketentuan') }}" class="text-xs font-bold text-gray-500 hover:text-gray-900">
                Baca Syarat &amp; Ketentuan &rarr;
            </a>
        </footer>

    </article>

</div>
@endsection
