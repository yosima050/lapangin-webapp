@extends('layouts.app')

@section('title', 'Kebijakan Reschedule & Pembatalan')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6 font-medium">
        <a href="{{ route('home') }}" class="hover:text-[#1a146b] flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">home</span>
            <span>Beranda</span>
        </a>
        <span>/</span>
        <a href="{{ route('bantuan') }}" class="hover:text-[#1a146b]">Pusat Bantuan</a>
        <span>/</span>
        <span class="text-gray-900 font-bold">Kebijakan Reschedule</span>
    </nav>

    {{-- Article Container --}}
    <article class="bg-white rounded-3xl p-6 sm:p-10 shadow-xs border border-gray-100 flex flex-col gap-6">

        <header class="border-b border-gray-100 pb-5">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#eaf7d8] text-[#416900] text-xs font-black uppercase tracking-wider mb-2">
                <span class="material-symbols-outlined text-sm">update</span>
                ATURAN RESCHEDULE &amp; REFUND
            </div>
            <h1 class="text-2xl sm:text-3xl font-headline font-black text-[#0b1c30] tracking-tight">
                Kebijakan Penjadwalan Ulang (Reschedule) &amp; Pembatalan
            </h1>
            <p class="text-xs text-gray-400 mt-1">Terakhir diperbarui: 29 Oktober 2026</p>
        </header>

        <section class="space-y-4 text-xs sm:text-sm text-gray-600 leading-relaxed">
            <h2 class="text-base font-headline font-bold text-[#0b1c30]">1. Ketentuan Batas Waktu Reschedule (Aturan H-24 Jam)</h2>
            <p>
                Pelanggan yang telah memiliki reservasi berhak mengajukan penjadwalan ulang (*reschedule*) ke tanggal dan jam lain dengan ketentuan sebagai berikut:
            </p>
            <ul class="list-disc list-inside space-y-1 ml-2 text-gray-600">
                <li>Permohonan reschedule <strong>wajib diajukan paling lambat 24 jam</strong> sebelum jam kickoff pertandingan yang tertera pada E-Tiket.</li>
                <li>Jika sisa waktu menuju kickoff kurang dari 24 jam (&lt; 24 jam), sistem secara otomatis mengunci tombol reschedule dan menandainya sebagai <em>"Mepet (&lt;24 Jam) - Tidak dapat diajukan"</em>.</li>
                <li>Slot jadwal baru yang dipilih harus memiliki status kosong (*Tersedia*) pada arena yang sama atau setara.</li>
                <li>Pengajuan reschedule akan masuk ke antrean persetujuan meja kasir venue (*Operator Approval*).</li>
            </ul>

            <h2 class="text-base font-headline font-bold text-[#0b1c30] pt-4 border-t border-gray-100">2. Struktur Denda Penalti Pembatalan</h2>
            <p>
                Apabila pemesan membatalkan jadwal bermain secara sepihak, berlaku ketentuan potongan biaya administrasi sebagai berikut:
            </p>

            <div class="overflow-x-auto my-3">
                <table class="w-full text-left text-xs border border-gray-200 rounded-xl overflow-hidden">
                    <thead class="bg-gray-50 text-gray-700 font-bold uppercase text-[11px]">
                        <tr>
                            <th class="p-3 border-b border-gray-200">Kondisi Waktu Pembatalan</th>
                            <th class="p-3 border-b border-gray-200">Besaran Denda</th>
                            <th class="p-3 border-b border-gray-200">Status Dana DP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="p-3 font-semibold text-gray-900">Lebih dari 24 Jam sebelum kickoff (H &gt; 24 Jam)</td>
                            <td class="p-3 text-amber-700 font-bold">10% dari Total Biaya</td>
                            <td class="p-3 text-emerald-700 font-medium">Sisa 90% dikembalikan atau dialihkan</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-semibold text-gray-900">Kurang dari 24 Jam sebelum kickoff (H &lt; 24 Jam)</td>
                            <td class="p-3 text-red-600 font-bold">50% (DP Penuh Hangus)</td>
                            <td class="p-3 text-red-600 font-medium">Uang DP 50% tidak dapat dikembalikan</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h2 class="text-base font-headline font-bold text-[#0b1c30] pt-4 border-t border-gray-100">3. Kondisi Khusus (Force Majeure)</h2>
            <p>
                Dalam kondisi darurat seperti bencana alam, pemadaman listrik total komplek olahraga, atau kerusakan teknis lapangan turnamen, pihak pengelola LapangIn berhak menjadwalkan ulang atau mengembalikan dana penuh 100% tanpa dikenakan penalti biaya apapun.
            </p>
        </section>

        <footer class="pt-6 border-t border-gray-100 flex items-center justify-between">
            <a href="{{ route('bantuan') }}" class="text-xs font-bold text-[#1a146b] hover:underline flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Kembali ke Pusat Bantuan</span>
            </a>
            <a href="{{ route('kontak') }}" class="text-xs font-bold text-[#416900] hover:underline">
                Butuh Bantuan Operator? Hubungi CS &rarr;
            </a>
        </footer>

    </article>

</div>
@endsection
