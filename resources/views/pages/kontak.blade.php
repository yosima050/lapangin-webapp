@extends('layouts.app')

@section('title', 'Kontak & Lokasi Venue')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6 font-medium">
        <a href="{{ route('home') }}" class="hover:text-[#1a146b] flex items-center gap-1">
            <x-lucide-house class="w-4 h-4 shrink-0" />
            <span>Beranda</span>
        </a>
        <span>/</span>
        <span class="text-gray-900 font-bold">Kontak Kami</span>
    </nav>

    {{-- Header --}}
    <div class="mb-10 text-center max-w-2xl mx-auto">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#eaf7d8] text-[#416900] text-xs font-black uppercase tracking-wider mb-3">
            <span class="w-2 h-2 rounded-full bg-[#416900]"></span>
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
                    <x-lucide-map-pin class="w-5 h-5 text-[#1a146b] shrink-0" />
                    <span>Lokasi &amp; Jam Operasional</span>
                </h2>

                <div class="space-y-4 text-xs text-gray-600">
                    <div>
                        <strong class="text-gray-900 block font-bold text-sm mb-1">Lapangan Utama LapangIn</strong>
                        <p class="leading-relaxed">
                            Jl. Ijen No. 25, Klojen, Kota Malang, Jawa Timur 65119 (Kompleks Olahraga Utama LapangIn Arena)
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
                    <x-lucide-message-circle class="w-5 h-5 shrink-0" />
                    <span>Chat WhatsApp CS LapangIn</span>
                </a>
            </div>

            {{-- Google Maps Embed Frame: Malang, Jawa Timur --}}
            <div class="bg-white rounded-3xl p-4 shadow-xs border border-gray-100 overflow-hidden">
                <div class="w-full h-64 rounded-2xl overflow-hidden bg-gray-100 border border-gray-200">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63219.04368541944!2d112.5898863!3d-7.9784695!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd6282206340f29%3A0x4027a76e35293d0!2sKota%20Malang%2C%20Jawa%20Timur!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid"
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

            <form action="#" method="POST" onsubmit="event.preventDefault(); window.lapanginNotify('Terima kasih! Pesan Anda telah terkirim ke customer care LapangIn.'); this.reset();" class="space-y-4">
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

                {{-- Custom Select Topik Pesan (Alpine.js Style LapangIn) --}}
                <div x-data="{
                    open: false,
                    selectedVal: 'booking',
                    selectedText: 'Pertanyaan Reservasi & Ketersediaan',
                    options: [
                        { val: 'booking', label: 'Pertanyaan Reservasi & Ketersediaan' },
                        { val: 'pembayaran', label: 'Kendala Pembayaran DP / Pelunasan' },
                        { val: 'turnamen', label: 'Kerjasama Turnamen & Sewa Harian Penuh' },
                        { val: 'lainnya', label: 'Kritik, Saran, & Lainnya' }
                    ]
                }" class="relative">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Topik Pesan</label>
                    <input type="hidden" name="topik" :value="selectedVal">
                    
                    {{-- Trigger Button --}}
                    <button
                        type="button"
                        @click="open = !open"
                        @click.outside="open = false"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-xs text-left font-medium text-gray-800 flex items-center justify-between transition focus:outline-none focus:ring-2 focus:ring-[#1a146b] focus:border-[#1a146b] hover:border-gray-300 shadow-2xs cursor-pointer"
                        :class="open ? 'ring-2 ring-[#1a146b] border-[#1a146b]' : ''"
                    >
                        <span x-text="selectedText"></span>
                        <x-lucide-chevron-down class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" x-bind:class="w-5 h-5 open ? 'rotate-180 text-[#1a146b]' : '' shrink-0" />
                    </button>

                    {{-- Custom Dropdown Menu --}}
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                        class="absolute left-0 right-0 z-30 mt-1.5 p-1.5 bg-white rounded-2xl shadow-xl border border-gray-150 overflow-hidden"
                        style="display: none;"
                    >
                        <template x-for="item in options" :key="item.val">
                            <button
                                type="button"
                                @click="selectedVal = item.val; selectedText = item.label; open = false"
                                class="w-full px-3.5 py-2.5 rounded-xl text-xs text-left font-medium transition flex items-center justify-between cursor-pointer"
                                :class="selectedVal === item.val ? 'bg-[#eff4ff] text-[#1a146b] font-bold' : 'text-gray-700 hover:bg-[#f8f9ff] hover:text-[#1a146b]'"
                            >
                                <span x-text="item.label"></span>
                                <x-lucide-check x-show="selectedVal === item.val" class="w-4 h-4 text-[#416900] font-bold shrink-0" />
                            </button>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Pesan Anda</label>
                    <textarea rows="4" required placeholder="Tuliskan pesan atau detail pertanyaan Anda di sini..." class="w-full px-4 py-3 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-[#1a146b] focus:border-[#1a146b] transition outline-none"></textarea>
                </div>

                <button type="submit" class="w-full py-3.5 px-6 rounded-2xl bg-[#1a146b] hover:bg-[#282094] text-white font-headline font-bold text-sm shadow-md transition active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer">
                    <x-lucide-send class="w-4 h-4 shrink-0" />
                    <span>Kirim Pesan Sekarang</span>
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
