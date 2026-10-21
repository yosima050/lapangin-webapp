<x-guest-layout>
    <div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8 flex flex-col justify-between">

        {{-- Top Navigation Bar --}}
        <div class="max-w-xl mx-auto w-full flex items-center justify-between mb-6">
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs sm:text-sm font-bold text-gray-600 hover:text-[#1a146b] hover:bg-white/80 transition shadow-xs">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                <span>Batal &amp; Kembali</span>
            </a>

            <div class="flex items-center gap-2">
                <x-application-logo />
            </div>

            <div class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#eaf7d8] text-[#416900] text-xs font-black uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-[#416900] animate-pulse"></span>
                <span>Langkah Terakhir</span>
            </div>
        </div>

        {{-- Main Content Card --}}
        <main class="max-w-xl mx-auto w-full my-auto">
            <div class="bg-white rounded-3xl p-7 sm:p-10 shadow-xl border border-[#e5eeff]">

                {{-- Header with Google Profile Badge --}}
                <div class="text-center flex flex-col items-center mb-6">
                    <div class="relative mb-5">
                        @if (!empty($googleUser['avatar']))
                            <img src="{{ $googleUser['avatar'] }}"
                                 alt="{{ $googleUser['nama'] }}"
                                 class="w-20 h-20 rounded-full border-4 border-white shadow-md object-cover">
                        @else
                            <div class="w-20 h-20 rounded-full bg-[#e83e8c] text-white border-4 border-white shadow-md flex items-center justify-center font-black text-2xl">
                                {{ strtoupper(substr($googleUser['nama'], 0, 1)) }}
                            </div>
                        @endif

                        {{-- Google Small Icon Badge --}}
                        <div class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-white shadow-sm border border-gray-100 flex items-center justify-center">
                            <svg class="w-4 h-4" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                            </svg>
                        </div>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-bold mb-3 border border-emerald-200">
                        <span class="material-symbols-outlined text-xs">verified</span>
                        <span>Akun Google Terverifikasi</span>
                    </div>

                    <h1 class="text-2xl font-headline font-black text-[#0b1c30] tracking-tight">
                        Lengkapi Nomor WhatsApp
                    </h1>

                    <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-sm">
                        Halo <strong>{{ $googleUser['nama'] }}</strong>, satu langkah lagi untuk mengaktifkan akun LapangIn Anda.
                    </p>
                </div>

                {{-- Form Pelengkap Profil --}}
                <form method="POST" action="{{ route('auth.google.complete.store') }}" class="space-y-4">
                    @csrf

                    {{-- 1. Nama Lengkap --}}
                    <div>
                        <label for="nama" class="text-[11px] font-bold uppercase tracking-wider text-gray-700 block mb-1.5">
                            NAMA LENGKAP
                        </label>
                        <div class="relative rounded-xl bg-[#eff4ff] border border-transparent focus-within:border-[#1a146b] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#1a146b]/15 transition">
                            <span class="material-symbols-outlined text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-lg pointer-events-none">
                                badge
                            </span>
                            <input
                                id="nama"
                                type="text"
                                name="nama"
                                value="{{ old('nama', $googleUser['nama']) }}"
                                required
                                class="w-full pl-11 pr-4 py-3 bg-transparent text-sm text-gray-900 font-medium placeholder-gray-400 outline-none border-none focus:ring-0"
                            >
                        </div>
                        @if ($errors->has('nama'))
                            <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">error</span>
                                <span>{{ $errors->first('nama') }}</span>
                            </p>
                        @endif
                    </div>

                    {{-- 2. Email Akun (Readonly) --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-gray-700">
                                EMAIL GOOGLE
                            </label>
                            <span class="text-[11px] font-bold text-emerald-600 inline-flex items-center gap-0.5">
                                <span class="material-symbols-outlined text-xs">check_circle</span>
                                Terhubung
                            </span>
                        </div>
                        <div class="relative rounded-xl bg-gray-50 border border-gray-200">
                            <span class="material-symbols-outlined text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-lg pointer-events-none">
                                mail
                            </span>
                            <input
                                type="email"
                                value="{{ $googleUser['email'] }}"
                                readonly
                                tabindex="-1"
                                class="w-full pl-11 pr-4 py-3 bg-transparent text-sm text-gray-600 font-medium outline-none border-none cursor-not-allowed select-none"
                            >
                        </div>
                    </div>

                    {{-- 3. Nomor WhatsApp / HP (Wajib Diisi) --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="no_hp" class="text-[11px] font-bold uppercase tracking-wider text-gray-700">
                                NOMOR WHATSAPP / HP AKTIF <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[11px] text-gray-400">Untuk E-Tiket &amp; Kode QR</span>
                        </div>

                        <div class="flex items-center rounded-xl bg-[#eff4ff] border border-transparent focus-within:border-[#1a146b] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#1a146b]/15 transition overflow-hidden">
                            <span class="inline-flex items-center gap-1 px-3.5 py-3 bg-[#e2edff] text-xs font-bold text-[#1a146b] shrink-0 border-r border-[#dce9ff]">
                                <span class="material-symbols-outlined text-sm">chat</span>
                                <span>+62</span>
                            </span>
                            <input
                                id="no_hp"
                                type="text"
                                name="no_hp"
                                value="{{ old('no_hp') }}"
                                required
                                autofocus
                                autocomplete="tel"
                                placeholder="812-3456-7890"
                                class="w-full px-3.5 py-3 bg-transparent text-sm text-gray-900 font-medium placeholder-gray-400 outline-none border-none focus:ring-0"
                            >
                        </div>

                        @if ($errors->has('no_hp'))
                            <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">error</span>
                                <span>{{ $errors->first('no_hp') }}</span>
                            </p>
                        @endif

                        <p class="text-[11px] text-gray-500 mt-1.5 flex items-start gap-1 leading-normal">
                            <span class="material-symbols-outlined text-xs text-[#416900] mt-0.5">info</span>
                            <span>Nomor ini penting untuk verifikasi tiket masuk dan notifikasi jadwal bertanding di arena.</span>
                        </p>
                    </div>

                    {{-- 4. Checkbox Syarat & Ketentuan --}}
                    <div class="pt-2">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input
                                type="checkbox"
                                required
                                class="mt-0.5 rounded border-gray-300 text-[#1a146b] focus:ring-[#acf847]"
                            >
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Saya menyetujui
                                <a href="{{ route('home') }}#syarat" target="_blank" class="font-bold underline text-[#1a146b] hover:text-[#312e81]">Syarat &amp; Ketentuan</a>
                                serta
                                <a href="{{ route('home') }}#kebijakan" target="_blank" class="font-bold underline text-[#1a146b] hover:text-[#312e81]">Kebijakan Privasi</a>
                                LapangIn
                            </span>
                        </label>
                    </div>

                    {{-- 5. Tombol Selesaikan & Masuk --}}
                    <button
                        type="submit"
                        class="w-full mt-6 py-3.5 px-6 rounded-xl bg-[#161358] hover:bg-[#25207c] text-white font-headline font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 group cursor-pointer"
                    >
                        <span>Selesaikan Pendaftaran &amp; Masuk</span>
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-[#acf847] text-[#102000] text-xs font-bold transition group-hover:translate-x-0.5">
                            →
                        </span>
                    </button>
                </form>

            </div>
        </main>

        {{-- Footer Help Note --}}
        <footer class="max-w-xl mx-auto w-full text-center py-6 mt-4">
            <p class="text-xs text-gray-500">
                Punya pertanyaan?
                <a href="https://wa.me/{{ config('app.support_whatsapp', '6281234567890') }}" target="_blank" rel="noopener noreferrer" class="font-bold text-[#1a146b] hover:underline">
                    Hubungi Layanan Pengguna LapangIn
                </a>
            </p>
        </footer>

    </div>
</x-guest-layout>

