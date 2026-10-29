@extends('layouts.app')

@section('title', 'Kontak & Lokasi Venue')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6 font-medium">
        <a href="{{ route('home') }}" class="hover:text-[#1a146b] flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">home</span>
            <span>Beranda</span>
        </a>
        <span>/</span>
        <span class="text-gray-900 font-bold">Kontak Kami</span>
    </nav>

    {{-- Header --}}
    <div class="mb-10 text-center max-w-2xl mx-auto">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#eaf7d8] text-[#416900] text-xs font-black uppercase tracking-wider mb-3">
            <span class="w-2 h-2 rounded-full bg-[#416900] animate-pulse"></span>
            Layanan Pelanggan 24/7
        </span>
        <h1 class="text-3xl sm:text-4xl font-headline font-black text-[#0b1c30] tracking-tight">
            Hubungi Lapang<span class="text-[#416900]">In</span>
        </h1>
        <p class="text-sm text-gray-600 mt-2">
            Punya pertanyaan mengenai reservasi, sewa turnamen, atau kendala check-in? Tim kami siap membantu Anda setiap hari.
        </p>
    </div>

    {{-- 2 Columns Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        {{-- Left: Informasi Venue & Channel Kontak --}}
        <div class="lg:col-span-5 flex flex-col gap-6">

            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-gray-100 flex flex-col gap-6">
                <h2 class="text-lg font-headline font-bold text-[#0b1c30] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#1a146b]">location_on</span>
                    <span>Lokasi &amp; Jam Operasional</span>
                </h2>

                <div class="space-y-4 text-xs text-gray-600">
                    <div>
                        <strong class="text-gray-900 block font-bold text-sm mb-1">Gelanggang Utama LapangIn</strong>
                        <p class="leading-relaxed">
                            Jl. TB Simatupang No. 18, Cilandak, Jakarta Selatan 12430 (Kompleks Olahraga Utama LapangIn Arena)
                        </p>
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                        <div>
                            <span class="text-gray-400 block font-medium">Jam Operasional:</span>
                            <strong class="text-[#0b1c30] text-sm">07:00 – 24:00 WIB</strong>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[11px] border border-emerald-200">
                            Buka Setiap Hari
                        </span>
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                        <div>
                            <span class="text-gray-400 block font-medium">Email Bantuan:</span>
                            <a href="mailto:support@lapangin.biz.id" class="text-[#1a146b] font-bold text-sm hover:underline">
                                support@lapangin.biz.id
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Direct WhatsApp Button --}}
                <a href="https://wa.me/{{ config('app.support_whatsapp', '6281234567890') }}?text=Halo%20Admin%20LapangIn%2C%20saya%20ingin%20bertanya%20mengenai%20sewa%20lapangan"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="w-full py-3.5 px-4 rounded-2xl bg-[#416900] hover:bg-[#315000] text-white font-headline font-bold text-xs sm:text-sm flex items-center justify-center gap-2 shadow-md transition active:scale-[0.99]">
                    <span class="material-symbols-outlined text-lg">chat</span>
                    <span>Chat WhatsApp CS LapangIn</span>
                </a>
            </div>

            {{-- Google Maps Embed Frame --}}
            <div class="bg-white rounded-3xl p-4 shadow-xs border border-gray-100 overflow-hidden">
                <div class="w-full h-64 rounded-2xl overflow-hidden bg-gray-100 border border-gray-200">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126906.90483861217!2d106.75043813962635!3d-6.284752838507851!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f1ec2422b1b3%3A0x6b80f1e847c2b3e5!2sCilandak%2C%20South%20Jakarta%20City%2C%20Jakarta!5e0!3m2!1sen!2sid!4v1700000000000!5m2!1sen!2sid"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

        </div>

        {{-- Right: Form Kontak / Kirim Pesan --}}
        <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-10 shadow-xs border border-gray-100">
            <h2 class="text-xl font-headline font-black text-[#0b1c30] tracking-tight mb-1">
                Kirimkan Pertanyaan atau Saran
            </h2>
            <p class="text-xs text-gray-500 mb-6">
                Silakan isi formulir di bawah ini. Tim kami akan menghubungi Anda kembali dalam kurun waktu 1x24 jam.
            </p>

            <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Terima kasih! Pesan Anda telah terkirim ke customer care LapangIn.'); this.reset();" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                        <input type="text" required placeholder="Contoh: Budi Santoso" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-[#1a146b] focus:border-[#1a146b] transition outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nomor WhatsApp</label>
                        <input type="tel" required placeholder="0812xxxxxxxx" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-[#1a146b] focus:border-[#1a146b] transition outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Alamat Email</label>
                    <input type="email" required placeholder="nama@email.com" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-[#1a146b] focus:border-[#1a146b] transition outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Topik Pesan</label>
                    <select class="w-full px-4 py-3 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-[#1a146b] focus:border-[#1a146b] transition outline-none bg-white">
                        <option value="booking">Pertanyaan Reservasi &amp; Ketersediaan</option>
                        <option value="pembayaran">Kendala Pembayaran DP / Pelunasan</option>
                        <option value="turnamen">Kerjasama Turnamen &amp; Sewa Harian Penuh</option>
                        <option value="lainnya">Kritik, Saran, &amp; Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Pesan Anda</label>
                    <textarea rows="4" required placeholder="Tuliskan pesan atau detail pertanyaan Anda di sini..." class="w-full px-4 py-3 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-[#1a146b] focus:border-[#1a146b] transition outline-none"></textarea>
                </div>

                <button type="submit" class="w-full py-3.5 px-6 rounded-2xl bg-[#1a146b] hover:bg-[#282094] text-white font-headline font-bold text-sm shadow-md transition active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer">
                    <span class="material-symbols-outlined text-base">send</span>
                    <span>Kirim Pesan Sekarang</span>
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
