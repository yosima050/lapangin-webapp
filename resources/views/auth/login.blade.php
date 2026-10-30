<x-guest-layout :title="'LapangIn'">
    @php
        $isRegister = ($initialTab ?? null) === 'register'
            || request()->routeIs('register')
            || request()->is('*register*')
            || (request()->filled('tab') && request('tab') === 'register')
            || request()->filled('no_hp')
            || old('name') !== null
            || old('no_hp') !== null
            || $errors->hasAny(['name', 'no_hp', 'password_confirmation']);
        $activeTab = $isRegister ? 'register' : 'login';
        $prefilledNoHp = old('no_hp', preg_replace('/^(\+62|62|0)/', '', request('no_hp', '')));
        $prefilledName = old('name', request('name', ''));
    @endphp

    <div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8 flex flex-col justify-between"
         x-data="{
             tab: '{{ $activeTab }}',
             showPassword: false,
             showPasswordConfirm: false,
             switchTab(target) {
                 this.tab = target;
                 const url = target === 'register' ? '{{ route('register') }}' : '{{ route('login') }}';
                 if (window.location.pathname !== new URL(url).pathname) {
                     window.history.pushState({ tab: target }, '', url);
                 }
             },
             init() {
                 window.addEventListener('popstate', (e) => {
                     if (window.location.pathname.includes('register')) {
                         this.tab = 'register';
                     } else {
                         this.tab = 'login';
                     }
                 });
             }
         }">

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

        {{-- Main Content Grid: 2 Columns on Desktop, Centered on Mobile --}}
        <main class="max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-14 items-center flex-1 my-auto">

            {{-- Left Column: Brand Hero & Arena Showcase --}}
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
                            <span class="w-7 h-7 rounded-full bg-[#eff4ff] border-2 border-white flex items-center justify-center text-[#1a146b] shadow-xs" title="Futsal & Sepakbola">
                                <svg class="w-3.5 h-3.5 stroke-current fill-none" viewBox="0 0 24 24" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="9" />
                                    <polygon points="12,7 16,10 14.5,15 9.5,15 8,10" fill="currentColor" fill-opacity="0.15" />
                                    <line x1="12" y1="7" x2="12" y2="3" />
                                    <line x1="16" y1="10" x2="20.5" y2="8.5" />
                                    <line x1="14.5" y1="15" x2="17.5" y2="19.5" />
                                    <line x1="9.5" y1="15" x2="6.5" y2="19.5" />
                                    <line x1="8" y1="10" x2="3.5" y2="8.5" />
                                </svg>
                            </span>
                            <span class="w-7 h-7 rounded-full bg-[#eff4ff] border-2 border-white flex items-center justify-center text-[#1a146b] shadow-xs" title="Badminton">
                                <svg class="w-3.5 h-3.5 stroke-current fill-none" viewBox="0 0 24 24" stroke-width="1.8">
                                    <circle cx="12" cy="9" r="6" />
                                    <line x1="12" y1="15" x2="12" y2="21" />
                                    <line x1="10" y1="18" x2="14" y2="18" />
                                    <path d="M8 9h8M12 5v8" stroke-dasharray="1 1" />
                                </svg>
                            </span>
                            <span class="w-7 h-7 rounded-full bg-[#acf847] border-2 border-white flex items-center justify-center text-[#102000] shadow-xs" title="Tenis">
                                <svg class="w-3.5 h-3.5 stroke-current fill-none" viewBox="0 0 24 24" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 3a9 9 0 010 18M12 3a9 9 0 000 18" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Interactive Auth Card (Single Unified Container) --}}
            <div class="lg:col-span-6 flex justify-center w-full">
                <div class="w-full max-w-lg bg-white rounded-3xl p-7 sm:p-10 shadow-xl border border-[#e5eeff] transition-all">

                    {{-- Top Tabs Segmented Control (Smooth Client-Side Switch with Native Link Fallback) --}}
                    <div class="bg-[#eff4ff] p-1.5 rounded-2xl flex items-center gap-1 mb-6 border border-[#dce9ff]">
                        <a
                            id="btn-tab-login"
                            href="{{ route('login') }}"
                            onclick="window.switchAuthTab && window.switchAuthTab('login', event)"
                            class="flex-1 py-2.5 px-3 rounded-xl font-headline text-xs sm:text-sm flex items-center justify-center gap-2 text-center transition cursor-pointer {{ $activeTab === 'login' ? 'bg-white text-[#1a146b] font-bold shadow-xs active-tab' : 'text-gray-500 hover:text-[#1a146b] font-semibold' }}"
                        >
                            <span id="dot-tab-login" class="w-2 h-2 rounded-full bg-[#416900] {{ $activeTab === 'login' ? '' : 'hidden' }}"></span>
                            <span class="material-symbols-outlined text-base">login</span>
                            <span>Masuk ke Akun</span>
                        </a>

                        <a
                            id="btn-tab-register"
                            href="{{ route('register') }}"
                            onclick="window.switchAuthTab && window.switchAuthTab('register', event)"
                            class="flex-1 py-2.5 px-3 rounded-xl font-headline text-xs sm:text-sm flex items-center justify-center gap-2 text-center transition cursor-pointer {{ $activeTab === 'register' ? 'bg-white text-[#1a146b] font-bold shadow-xs active-tab' : 'text-gray-500 hover:text-[#1a146b] font-semibold' }}"
                        >
                            <span id="dot-tab-register" class="w-2 h-2 rounded-full bg-[#416900] {{ $activeTab === 'register' ? '' : 'hidden' }}"></span>
                            <span class="material-symbols-outlined text-base">person_add</span>
                            <span>Daftar Akun Baru</span>
                        </a>
                    </div>

                    {{-- ======================================================== --}}
                    {{-- TAB 1: FORM LOGIN --}}
                    {{-- ======================================================== --}}
                    <div id="pane-tab-login"
                         class="{{ $activeTab === 'login' ? '' : 'hidden' }}"
                         @if($activeTab !== 'login') style="display: none;" @endif>

                        <div class="mb-5">
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

                        {{-- Google SSO Button --}}
                        <div class="mb-5">
                            <a href="{{ route('auth.google') }}"
                               class="w-full py-3 px-4 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 hover:border-gray-300 text-gray-700 font-headline font-bold text-xs sm:text-sm shadow-xs hover:shadow transition flex items-center justify-center gap-3 group">
                                <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                                </svg>
                                <span>Lanjutkan dengan Google</span>
                            </a>

                            <div class="relative flex items-center justify-center mt-5">
                                <div class="border-t border-gray-200 w-full"></div>
                                <span class="bg-white px-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider absolute">atau masuk dengan email</span>
                            </div>
                        </div>

                        {{-- Login Form --}}
                        <form method="POST" action="{{ route('login') }}" class="space-y-4">
                            @csrf

                            {{-- Email / WhatsApp Input --}}
                            <div>
                                <label for="login_email" class="text-[11px] font-bold uppercase tracking-wider text-gray-700 block mb-1.5">
                                    EMAIL ATAU NOMOR WHATSAPP
                                </label>

                                <div class="relative rounded-xl bg-[#eff4ff] border border-transparent focus-within:border-[#1a146b] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#1a146b]/15 transition">
                                    <span class="material-symbols-outlined text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-lg pointer-events-none">
                                        badge
                                    </span>
                                    <input
                                        id="login_email"
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

                                @if ($activeTab === 'login' && $errors->has('email'))
                                    <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">error</span>
                                        <span>{{ $errors->first('email') }}</span>
                                    </p>
                                @endif
                            </div>

                            {{-- Password Input --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="login_password" class="text-[11px] font-bold uppercase tracking-wider text-gray-700">
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
                                        id="login_password"
                                        type="password"
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

                                @if ($activeTab === 'login' && $errors->has('password'))
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

                        {{-- Switch Prompt to Register --}}
                        <div class="mt-6 pt-5 border-t border-gray-100 text-center">
                            <p class="text-xs text-gray-500">
                                Belum punya akun LapangIn?
                                <a
                                    href="{{ route('register') }}"
                                    onclick="window.switchAuthTab && window.switchAuthTab('register', event)"
                                    class="font-extrabold text-[#1a146b] hover:underline ml-1 cursor-pointer">
                                    Daftar Sekarang ↗
                                </a>
                            </p>
                        </div>
                    </div>

                    {{-- ======================================================== --}}
                    {{-- TAB 2: FORM REGISTER --}}
                    {{-- ======================================================== --}}
                    <div id="pane-tab-register"
                         class="{{ $activeTab === 'register' ? '' : 'hidden' }}"
                         @if($activeTab !== 'register') style="display: none;" @endif>

                        <div class="mb-5">
                            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-[#eaf7d8] text-[#416900] text-[10px] font-black uppercase tracking-wider mb-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#416900]"></span>
                                <span>SATU AKUN SELURUH LAPANGAN</span>
                            </div>
                            <h2 class="text-2xl font-headline font-black text-[#0b1c30] tracking-tight">
                                Daftar Akun Baru
                            </h2>
                            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                                Buat akun untuk simpan riwayat booking dan kemudahan klaim pesanan.
                            </p>
                        </div>

                        {{-- Session Status --}}
                        @if (session('status'))
                            <div class="mb-5 p-3.5 rounded-xl bg-indigo-50 border border-indigo-200 text-xs font-semibold text-[#1a146b] flex items-center gap-2">
                                <span class="material-symbols-outlined text-base">info</span>
                                <span>{{ session('status') }}</span>
                            </div>
                        @endif

                        <div class="mb-5">
                            <a href="{{ route('auth.google') }}"
                               class="w-full py-3 px-4 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 hover:border-gray-300 text-gray-700 font-headline font-bold text-xs sm:text-sm shadow-xs hover:shadow transition flex items-center justify-center gap-3 group">
                                <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                                </svg>
                                <span>Lanjutkan dengan Google</span>
                            </a>

                            <div class="relative flex items-center justify-center mt-5">
                                <div class="border-t border-gray-200 w-full"></div>
                                <span class="bg-white px-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider absolute">atau daftar manual</span>
                            </div>
                        </div>

                        {{-- Registration Form --}}
                        <form method="POST" action="{{ route('register') }}" class="space-y-4"
                              x-data="{
                                  submitting: false,
                                  regPassword: '',
                                  regPasswordConfirm: '',
                                  get passwordTooShort() {
                                      return this.regPassword.length > 0 && this.regPassword.length < 8;
                                  },
                                  get passwordMismatch() {
                                      return this.regPasswordConfirm.length > 0 && this.regPassword !== this.regPasswordConfirm;
                                  }
                              }"
                              @submit="if(passwordTooShort || passwordMismatch) { $event.preventDefault(); return false; } submitting = true">
                            @csrf

                            {{-- 1. Nama Lengkap --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="register_name" class="text-[11px] font-bold uppercase tracking-wider text-gray-700">
                                        NAMA LENGKAP
                                    </label>
                                    <span class="text-[11px] text-gray-400">Sesuai KTP/Identitas</span>
                                </div>

                                <div class="relative rounded-xl bg-[#eff4ff] border border-transparent focus-within:border-[#1a146b] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#1a146b]/15 transition">
                                    <span class="material-symbols-outlined text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-lg pointer-events-none">
                                        badge
                                    </span>
                                    <input
                                        id="register_name"
                                        type="text"
                                        name="name"
                                        value="{{ $prefilledName }}"
                                        required
                                        autocomplete="name"
                                        placeholder="contoh: Budi Santoso"
                                        class="w-full pl-11 pr-4 py-3 bg-transparent text-sm text-gray-900 font-medium placeholder-gray-400 outline-none border-none focus:ring-0"
                                    >
                                </div>

                                @if ($activeTab === 'register' && $errors->has('name'))
                                    <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">error</span>
                                        <span>{{ $errors->first('name') }}</span>
                                    </p>
                                @endif
                            </div>

                            {{-- 2. Email Akun --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="register_email" class="text-[11px] font-bold uppercase tracking-wider text-gray-700">
                                        EMAIL AKUN
                                    </label>
                                    <span class="text-[11px] text-gray-400">Untuk E-Tiket &amp; Notifikasi</span>
                                </div>

                                <div class="relative rounded-xl bg-[#eff4ff] border border-transparent focus-within:border-[#1a146b] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#1a146b]/15 transition">
                                    <span class="material-symbols-outlined text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-lg pointer-events-none">
                                        mail
                                    </span>
                                    <input
                                        id="register_email"
                                        type="email"
                                        name="email"
                                        value="{{ old('name') ? old('email') : '' }}"
                                        required
                                        autocomplete="username"
                                        placeholder="contoh: budi@email.com"
                                        class="w-full pl-11 pr-4 py-3 bg-transparent text-sm text-gray-900 font-medium placeholder-gray-400 outline-none border-none focus:ring-0"
                                    >
                                </div>

                                @if ($activeTab === 'register' && $errors->has('email'))
                                    <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">error</span>
                                        <span>{{ $errors->first('email') }}</span>
                                    </p>
                                @endif
                            </div>

                            {{-- 3. Nomor WhatsApp / HP --}}
                            <div>
                                <label for="register_no_hp" class="text-[11px] font-bold uppercase tracking-wider text-gray-700 block mb-1.5">
                                    NOMOR WHATSAPP / HP
                                </label>

                                <div class="flex items-center rounded-xl bg-[#eff4ff] border border-transparent focus-within:border-[#1a146b] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#1a146b]/15 transition overflow-hidden">
                                    <span class="inline-flex items-center gap-1 px-3.5 py-3 bg-[#e2edff] text-xs font-bold text-[#1a146b] shrink-0 border-r border-[#dce9ff]">
                                        <span class="material-symbols-outlined text-sm">chat</span>
                                        <span>+62</span>
                                    </span>
                                    <input
                                        id="register_no_hp"
                                        type="text"
                                        name="no_hp"
                                        value="{{ $prefilledNoHp }}"
                                        required
                                        autocomplete="tel"
                                        placeholder="812-3456-7890"
                                        class="w-full px-3.5 py-3 bg-transparent text-sm text-gray-900 font-medium placeholder-gray-400 outline-none border-none focus:ring-0"
                                    >
                                </div>

                                @if ($activeTab === 'register' && $errors->has('no_hp'))
                                    <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">error</span>
                                        <span>{{ $errors->first('no_hp') }}</span>
                                    </p>
                                @endif
                            </div>

                            {{-- 4. Kata Sandi --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="register_password" class="text-[11px] font-bold uppercase tracking-wider text-gray-700">
                                        KATA SANDI
                                    </label>
                                    <span class="text-[11px] text-gray-400">Min. 8 karakter</span>
                                </div>

                                <div class="relative rounded-xl bg-[#eff4ff] border border-transparent focus-within:border-[#1a146b] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#1a146b]/15 transition">
                                    <span class="material-symbols-outlined text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-lg pointer-events-none">
                                        lock
                                    </span>
                                    <input
                                        id="register_password"
                                        type="password"
                                        :type="showPassword ? 'text' : 'password'"
                                        name="password"
                                        x-model="regPassword"
                                        minlength="8"
                                        required
                                        autocomplete="new-password"
                                        placeholder="Minimal 8 karakter"
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

                                <template x-if="passwordTooShort">
                                    <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">error</span>
                                        <span>Kata sandi minimal harus 8 karakter.</span>
                                    </p>
                                </template>

                                @if ($activeTab === 'register' && $errors->has('password'))
                                    <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1" x-show="!passwordTooShort">
                                        <span class="material-symbols-outlined text-sm">error</span>
                                        <span>{{ $errors->first('password') }}</span>
                                    </p>
                                @endif
                            </div>

                            {{-- 5. Konfirmasi Kata Sandi --}}
                            <div>
                                <label for="register_password_confirmation" class="text-[11px] font-bold uppercase tracking-wider text-gray-700 block mb-1.5">
                                    KONFIRMASI KATA SANDI
                                </label>

                                <div class="relative rounded-xl bg-[#eff4ff] border border-transparent focus-within:border-[#1a146b] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#1a146b]/15 transition">
                                    <span class="material-symbols-outlined text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-lg pointer-events-none">
                                        lock_reset
                                    </span>
                                    <input
                                        id="register_password_confirmation"
                                        type="password"
                                        :type="showPasswordConfirm ? 'text' : 'password'"
                                        name="password_confirmation"
                                        x-model="regPasswordConfirm"
                                        minlength="8"
                                        required
                                        autocomplete="new-password"
                                        placeholder="Ulangi kata sandi"
                                        class="w-full pl-11 pr-11 py-3 bg-transparent text-sm text-gray-900 font-medium placeholder-gray-400 outline-none border-none focus:ring-0"
                                    >
                                    <button
                                        type="button"
                                        @click="showPasswordConfirm = !showPasswordConfirm"
                                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 transition"
                                        tabindex="-1"
                                    >
                                        <span class="material-symbols-outlined text-lg"
                                              x-text="showPasswordConfirm ? 'visibility_off' : 'visibility'">
                                        </span>
                                    </button>
                                </div>

                                <template x-if="passwordMismatch">
                                    <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">error</span>
                                        <span>Konfirmasi kata sandi tidak cocok.</span>
                                    </p>
                                </template>

                                @if ($activeTab === 'register' && $errors->has('password_confirmation'))
                                    <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1" x-show="!passwordMismatch">
                                        <span class="material-symbols-outlined text-sm">error</span>
                                        <span>{{ $errors->first('password_confirmation') }}</span>
                                    </p>
                                @endif
                            </div>

                            {{-- 6. Checkbox Syarat & Ketentuan --}}
                            <div class="pt-1">
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

                            {{-- 7. Tombol Submit (Daftar) --}}
                            <button
                                type="submit"
                                :disabled="submitting"
                                class="w-full mt-6 py-3.5 px-6 rounded-xl bg-[#161358] hover:bg-[#25207c] text-white font-headline font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed"
                            >
                                <template x-if="!submitting">
                                    <span class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-base text-[#acf847]">bolt</span>
                                        <span>Daftar Akun Sekarang</span>
                                    </span>
                                </template>
                                <template x-if="submitting">
                                    <span class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                        <span>Memproses &amp; Mengirim OTP...</span>
                                    </span>
                                </template>
                            </button>
                        </form>

                        {{-- Switch Prompt to Login --}}
                        <div class="mt-6 pt-5 border-t border-gray-100 text-center">
                            <p class="text-xs text-gray-500">
                                Sudah memiliki akun LapangIn?
                                <a
                                    href="{{ route('login') }}"
                                    onclick="window.switchAuthTab && window.switchAuthTab('login', event)"
                                    class="font-extrabold text-[#1a146b] hover:underline ml-1 cursor-pointer">
                                    Masuk Sekarang ↗
                                </a>
                            </p>
                        </div>
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

        <script>
            window.switchAuthTab = function(target, e) {
                if (e && e.preventDefault) e.preventDefault();
                const isLogin = target === 'login';
                const loginPane = document.getElementById('pane-tab-login');
                const registerPane = document.getElementById('pane-tab-register');
                const btnLogin = document.getElementById('btn-tab-login');
                const btnRegister = document.getElementById('btn-tab-register');
                const dotLogin = document.getElementById('dot-tab-login');
                const dotRegister = document.getElementById('dot-tab-register');

                if (loginPane) {
                    if (isLogin) {
                        loginPane.classList.remove('hidden');
                        loginPane.style.display = '';
                    } else {
                        loginPane.classList.add('hidden');
                        loginPane.style.display = 'none';
                    }
                }
                if (registerPane) {
                    if (isLogin) {
                        registerPane.classList.add('hidden');
                        registerPane.style.display = 'none';
                    } else {
                        registerPane.classList.remove('hidden');
                        registerPane.style.display = '';
                    }
                }

                if (btnLogin && btnRegister) {
                    if (isLogin) {
                        btnLogin.className = 'flex-1 py-2.5 px-3 rounded-xl font-headline text-xs sm:text-sm flex items-center justify-center gap-2 text-center transition cursor-pointer bg-white text-[#1a146b] font-bold shadow-xs active-tab';
                        btnRegister.className = 'flex-1 py-2.5 px-3 rounded-xl font-headline text-xs sm:text-sm flex items-center justify-center gap-2 text-center transition cursor-pointer text-gray-500 hover:text-[#1a146b] font-semibold';
                        if (dotLogin) dotLogin.classList.remove('hidden');
                        if (dotRegister) dotRegister.classList.add('hidden');
                    } else {
                        btnRegister.className = 'flex-1 py-2.5 px-3 rounded-xl font-headline text-xs sm:text-sm flex items-center justify-center gap-2 text-center transition cursor-pointer bg-white text-[#1a146b] font-bold shadow-xs active-tab';
                        btnLogin.className = 'flex-1 py-2.5 px-3 rounded-xl font-headline text-xs sm:text-sm flex items-center justify-center gap-2 text-center transition cursor-pointer text-gray-500 hover:text-[#1a146b] font-semibold';
                        if (dotRegister) dotRegister.classList.remove('hidden');
                        if (dotLogin) dotLogin.classList.add('hidden');
                    }
                }

                const url = isLogin ? '{{ route('login') }}' : '{{ route('register') }}';
                if (window.history && window.history.pushState) {
                    window.history.pushState({ tab: target }, '', url);
                }
            };

            window.addEventListener('popstate', function() {
                if (window.location.pathname.includes('register')) {
                    window.switchAuthTab('register');
                } else {
                    window.switchAuthTab('login');
                }
            });
        </script>

    </div>
</x-guest-layout>
