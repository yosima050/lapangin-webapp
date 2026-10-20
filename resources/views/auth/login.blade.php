<x-guest-layout>
    <div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8 flex flex-col justify-between"
         x-data="{ showPassword: false }">

        {{-- Top Minimalist Navigation Bar --}}
        <div class="max-w-7xl mx-auto w-full flex items-center justify-between mb-6 sm:mb-10">
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs sm:text-sm font-bold text-gray-600 hover:text-[#1a146b] hover:bg-white/80 transition shadow-xs">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                <span>Kembali ke Beranda</span>
            </a>

            <div class="lg:hidden">
                <x-application-logo />
            </div>

            <div class="hidden sm:flex items-center gap-2 text-xs font-semibold text-gray-500">
                <span class="w-2 h-2 rounded-full bg-[#416900] animate-pulse"></span>
                <span>Sistem Reservasi Aktif 24/7</span>
            </div>
        </div>

        {{-- Main Content Grid: 2 Columns on Desktop (Image 1), Centered on Mobile (Image 3) --}}
        <main class="max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-14 items-center flex-1 my-auto">

            {{-- Left Column: Brand Hero & Arena Showcase (Desktop only - Image 1) --}}
            <div class="hidden lg:flex lg:col-span-6 flex-col justify-center pr-4">
                <div class="mb-6">
                    <x-application-logo />
                </div>

                <h1 class="text-4xl xl:text-5xl font-headline font-black tracking-tight text-[#0b1c30] leading-[1.18]">
                    Akses Lapangan &amp; Kendali Jadwal Tanpa Batas.
                </h1>

                <p class="mt-4 text-base text-gray-600 leading-relaxed max-w-lg">
                    Satu portal untuk reservasi mini soccer, padel, badminton, dan tenis di puluhan arena terverifikasi.
                </p>

                {{-- Showcase Media Card --}}
                <div class="mt-8 rounded-3xl overflow-hidden bg-white border border-gray-100 shadow-md group">
                    <div class="relative h-64 overflow-hidden bg-gray-900">
                        <img
                            src="https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1200&q=80"
                            alt="Arena Olahraga Terkoneksi LapangIn"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    </div>

                    <div class="p-4 bg-white flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#416900] animate-pulse"></span>
                            <span class="text-xs font-black uppercase tracking-wider text-[#416900]">
                                ARENA TERKONEKSI
                            </span>
                        </div>

                        {{-- Social Proof / Venue Stack Circles --}}
                        <div class="flex items-center -space-x-2">
                            <span class="w-7 h-7 rounded-full bg-[#eff4ff] border-2 border-white flex items-center justify-center text-[10px] font-bold text-[#1a146b] shadow-xs">⚽</span>
                            <span class="w-7 h-7 rounded-full bg-[#eff4ff] border-2 border-white flex items-center justify-center text-[10px] font-bold text-[#1a146b] shadow-xs">🏸</span>
                            <span class="w-7 h-7 rounded-full bg-[#acf847] border-2 border-white flex items-center justify-center text-[10px] font-bold text-[#102000] shadow-xs">🎾</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Interactive Login Card (Image 1 Desktop & Image 3 Mobile) --}}
            <div class="lg:col-span-6 flex justify-center w-full">
                <div class="w-full max-w-md bg-white rounded-3xl p-7 sm:p-10 shadow-xl border border-[#e5eeff] transition-all">

                    {{-- Top Tabs Segmented Control --}}
                    <div class="bg-[#eff4ff] p-1.5 rounded-2xl flex items-center gap-1 mb-8 border border-[#dce9ff]">
                        <div class="flex-1 py-2.5 px-3 rounded-xl bg-white text-[#1a146b] font-headline font-bold text-xs sm:text-sm shadow-xs flex items-center justify-center gap-2 text-center">
                            <span class="w-2 h-2 rounded-full bg-[#416900]"></span>
                            <span class="material-symbols-outlined text-base">login</span>
                            <span>Masuk ke Akun</span>
                        </div>

                        <a href="{{ route('register') }}"
                           class="flex-1 py-2.5 px-3 rounded-xl text-gray-500 hover:text-[#1a146b] font-headline font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 text-center transition">
                            <span class="material-symbols-outlined text-base">person_add</span>
                            <span>Daftar Akun Baru</span>
                        </a>
                    </div>

                    {{-- Card Heading --}}
                    <div class="mb-6">
                        <h2 class="text-2xl font-headline font-black text-[#0b1c30] tracking-tight">
                            Selamat Datang Kembali
                        </h2>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">
                            Masuk untuk mengelola jadwal bertanding dan tim Anda.
                        </p>
                    </div>

                    {{-- Session Status --}}
                    @if (session('status'))
                        <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800 flex items-center gap-2">
                            <span class="material-symbols-outlined text-base">check_circle</span>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    {{-- Login Form --}}
                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        {{-- Email Input --}}
                        <div>
                            <label for="email" class="text-[11px] font-bold uppercase tracking-wider text-gray-700 block mb-1.5">
                                EMAIL ATAU NOMOR WHATSAPP
                            </label>

                            <div class="relative rounded-xl bg-[#eff4ff] border border-transparent focus-within:border-[#1a146b] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#1a146b]/15 transition">
                                <span class="material-symbols-outlined text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-lg pointer-events-none">
                                    badge
                                </span>
                                <input
                                    id="email"
                                    type="text"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="nama@email.com atau 0812xxxx"
                                    class="w-full pl-11 pr-4 py-3 bg-transparent text-sm text-gray-900 font-medium placeholder-gray-400 outline-none border-none focus:ring-0"
                                >
                            </div>

                            @if ($errors->has('email'))
                                <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">error</span>
                                    <span>{{ $errors->first('email') }}</span>
                                </p>
                            @endif
                        </div>

                        {{-- Password Input --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="text-[11px] font-bold uppercase tracking-wider text-gray-700">
                                    KATA SANDI
                                </label>

                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}"
                                       class="text-xs font-bold text-[#1a146b] hover:text-[#312e81] hover:underline transition">
                                        Lupa Kata Sandi?
                                    </a>
                                @endif
                            </div>

                            <div class="relative rounded-xl bg-[#eff4ff] border border-transparent focus-within:border-[#1a146b] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#1a146b]/15 transition">
                                <span class="material-symbols-outlined text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-lg pointer-events-none">
                                    lock
                                </span>
                                <input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="••••••••••••"
                                    class="w-full pl-11 pr-11 py-3 bg-transparent text-sm text-gray-900 font-medium placeholder-gray-400 outline-none border-none focus:ring-0"
                                >
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 transition"
                                    tabindex="-1"
                                >
                                    <span class="material-symbols-outlined text-lg"
                                          x-text="showPassword ? 'visibility_off' : 'visibility'">
                                    </span>
                                </button>
                            </div>

                            @if ($errors->has('password'))
                                <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">error</span>
                                    <span>{{ $errors->first('password') }}</span>
                                </p>
                            @endif
                        </div>

                        {{-- Remember Me & Security Badge --}}
                        <div class="flex items-center justify-between pt-1">
                            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                                <input
                                    id="remember_me"
                                    type="checkbox"
                                    name="remember"
                                    class="rounded border-gray-300 text-[#1a146b] shadow-xs focus:ring-[#acf847]"
                                >
                                <span class="text-xs font-semibold text-gray-600">
                                    Ingat Saya di Perangkat Ini
                                </span>
                            </label>

                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#416900]">
                                <span class="material-symbols-outlined text-xs">lock</span>
                                <span>Sesi Aman</span>
                            </span>
                        </div>

                        {{-- Submit Button --}}
                        <button
                            type="submit"
                            class="w-full mt-6 py-3.5 px-6 rounded-xl bg-[#161358] hover:bg-[#25207c] text-white font-headline font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 group cursor-pointer"
                        >
                            <span>Masuk Sekarang</span>
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-[#acf847] text-[#102000] text-xs font-bold transition group-hover:translate-x-0.5">
                                →
                            </span>
                        </button>
                    </form>

                    {{-- Mobile Prompt Link --}}
                    <div class="mt-6 pt-5 border-t border-gray-100 text-center">
                        <p class="text-xs text-gray-500">
                            Belum punya akun LapangIn?
                            <a href="{{ route('register') }}" class="font-extrabold text-[#1a146b] hover:underline ml-1">
                                Daftar Sekarang ↗
                            </a>
                        </p>
                    </div>
                </div>
            </div>

        </main>

        {{-- Footer Help Note --}}
        <footer class="max-w-7xl mx-auto w-full text-center py-6 mt-6">
            <p class="text-xs text-gray-500 inline-flex items-center justify-center gap-1.5 flex-wrap">
                <span class="material-symbols-outlined text-sm text-gray-400">help</span>
                <span>Butuh bantuan kendala akun?</span>
                <a href="https://wa.me/{{ config('app.support_whatsapp', '6281234567890') }}" target="_blank" rel="noopener noreferrer" class="font-bold text-[#1a146b] hover:underline">
                    Hubungi Layanan Pengguna 24/7
                </a>
            </p>
        </footer>

    </div>
</x-guest-layout>
