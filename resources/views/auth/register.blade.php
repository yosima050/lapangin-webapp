<x-guest-layout>
    <div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8 flex flex-col justify-between"
         x-data="{ showPassword: false, showPasswordConfirm: false }">

        {{-- Top Navigation Bar (Image 2) --}}
        <div class="max-w-2xl mx-auto w-full flex items-center justify-between mb-4 sm:mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}"
                   class="w-10 h-10 rounded-full border border-gray-200 bg-white flex items-center justify-center text-gray-700 hover:text-[#1a146b] hover:bg-gray-50 shadow-xs transition"
                   title="Kembali ke Login">
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                </a>

                <div class="flex flex-col text-left leading-tight">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400">LAPANGIN</span>
                    <span class="text-sm font-extrabold text-[#0b1c30]">Register</span>
                </div>
            </div>

            <a href="{{ route('home') }}"
               class="text-xs font-bold text-gray-500 hover:text-[#1a146b] transition">
                Beranda
            </a>
        </div>

        {{-- Main Register Card Container --}}
        <main class="max-w-xl mx-auto w-full my-auto">
            <div class="bg-white rounded-3xl p-7 sm:p-10 shadow-xl border border-[#e5eeff]">

                {{-- Centered Header & Branding --}}
                <div class="text-center flex flex-col items-center">
                    <x-application-logo class="justify-center mb-4" />

                    {{-- Badge: SATU AKUN SELURUH LAPANGAN --}}
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#eaf7d8] text-[#416900] text-[11px] font-black uppercase tracking-wider mb-2">
                        <span class="w-2 h-2 rounded-full bg-[#416900]"></span>
                        <span>SATU AKUN SELURUH LAPANGAN</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-headline font-black text-[#0b1c30] tracking-tight">
                        Daftar Akun LapangIn
                    </h1>

                    <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-sm">
                        Buat akun untuk simpan riwayat booking dan klaim pesanan tamu Anda.
                    </p>
                </div>

                {{-- Segmented Tabs Control --}}
                <div class="bg-[#eff4ff] p-1.5 rounded-2xl flex items-center gap-1 my-6 border border-[#dce9ff]">
                    <a href="{{ route('login') }}"
                       class="flex-1 py-2.5 px-3 rounded-xl text-gray-500 hover:text-[#1a146b] font-headline font-bold text-xs sm:text-sm flex items-center justify-center gap-2 text-center transition">
                        <span class="material-symbols-outlined text-base">login</span>
                        <span>Masuk</span>
                    </a>

                    <div class="flex-1 py-2.5 px-3 rounded-xl bg-[#161358] text-white font-headline font-bold text-xs sm:text-sm shadow-xs flex items-center justify-center gap-2 text-center">
                        <span class="material-symbols-outlined text-base">person_add</span>
                        <span>Daftar Akun</span>
                    </div>
                </div>

                {{-- Registration Form --}}
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    {{-- 1. Nama Lengkap --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="name" class="text-[11px] font-bold uppercase tracking-wider text-gray-700">
                                NAMA LENGKAP
                            </label>
                            <span class="text-[11px] text-gray-400">Sesuai KTP/Identitas</span>
                        </div>

                        <div class="relative rounded-xl bg-[#eff4ff] border border-transparent focus-within:border-[#1a146b] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#1a146b]/15 transition">
                            <span class="material-symbols-outlined text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-lg pointer-events-none">
                                badge
                            </span>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                autofocus
                                autocomplete="name"
                                placeholder="contoh: Budi Santoso"
                                class="w-full pl-11 pr-4 py-3 bg-transparent text-sm text-gray-900 font-medium placeholder-gray-400 outline-none border-none focus:ring-0"
                            >
                        </div>

                        @if ($errors->has('name'))
                            <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">error</span>
                                <span>{{ $errors->first('name') }}</span>
                            </p>
                        @endif
                    </div>

                    {{-- 2. Email Akun --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="email" class="text-[11px] font-bold uppercase tracking-wider text-gray-700">
                                EMAIL AKUN
                            </label>
                            <span class="text-[11px] text-gray-400">Untuk E-Tiket &amp; Notifikasi</span>
                        </div>

                        <div class="relative rounded-xl bg-[#eff4ff] border border-transparent focus-within:border-[#1a146b] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#1a146b]/15 transition">
                            <span class="material-symbols-outlined text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-lg pointer-events-none">
                                mail
                            </span>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="username"
                                placeholder="contoh: budi@email.com"
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

                    {{-- 3. Nomor WhatsApp / HP --}}
                    <div>
                        <label for="no_hp" class="text-[11px] font-bold uppercase tracking-wider text-gray-700 block mb-1.5">
                            NOMOR WHATSAPP / HP
                        </label>

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
                    </div>

                    {{-- 4. Kata Sandi --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="text-[11px] font-bold uppercase tracking-wider text-gray-700">
                                KATA SANDI
                            </label>
                            <span class="text-[11px] text-gray-400">Min. 8 karakter</span>
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

                        @if ($errors->has('password'))
                            <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">error</span>
                                <span>{{ $errors->first('password') }}</span>
                            </p>
                        @endif
                    </div>

                    {{-- 5. Konfirmasi Kata Sandi --}}
                    <div>
                        <label for="password_confirmation" class="text-[11px] font-bold uppercase tracking-wider text-gray-700 block mb-1.5">
                            KONFIRMASI KATA SANDI
                        </label>

                        <div class="relative rounded-xl bg-[#eff4ff] border border-transparent focus-within:border-[#1a146b] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#1a146b]/15 transition">
                            <span class="material-symbols-outlined text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-lg pointer-events-none">
                                lock_reset
                            </span>
                            <input
                                id="password_confirmation"
                                :type="showPasswordConfirm ? 'text' : 'password'"
                                name="password_confirmation"
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

                        @if ($errors->has('password_confirmation'))
                            <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">error</span>
                                <span>{{ $errors->first('password_confirmation') }}</span>
                            </p>
                        @endif
                    </div>

                    {{-- 6. Checkbox Syarat & Ketentuan --}}
                    <div class="pt-2">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input
                                type="checkbox"
                                required
                                class="mt-0.5 rounded border-gray-300 text-[#1a146b] focus:ring-[#acf847]"
                            >
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Saya menyetujui
                                <a href="{{ route('home') }}#syarat" class="font-bold underline text-[#1a146b] hover:text-[#312e81]">Syarat &amp; Ketentuan</a>
                                serta
                                <a href="{{ route('home') }}#kebijakan" class="font-bold underline text-[#1a146b] hover:text-[#312e81]">Kebijakan Privasi</a>
                                LapangIn
                            </span>
                        </label>
                    </div>

                    {{-- 7. Tombol Submit (⚡ Daftar) --}}
                    <button
                        type="submit"
                        class="w-full mt-6 py-3.5 px-6 rounded-xl bg-[#161358] hover:bg-[#25207c] text-white font-headline font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-base text-[#acf847]">bolt</span>
                        <span>Daftar</span>
                    </button>
                </form>

                {{-- Card Footer Link --}}
                <div class="mt-8 pt-5 border-t border-gray-100 text-center">
                    <p class="text-xs text-gray-500">
                        Sudah memiliki akun?
                        <a href="{{ route('login') }}" class="font-extrabold text-[#1a146b] hover:underline ml-1">
                            Masuk di sini →
                        </a>
                    </p>
                </div>

            </div>
        </main>

        {{-- Footer Help Note --}}
        <footer class="max-w-2xl mx-auto w-full text-center py-6 mt-6">
            <p class="text-xs text-gray-500 inline-flex items-center justify-center gap-1.5 flex-wrap">
                <span class="material-symbols-outlined text-sm text-gray-400">verified_user</span>
                <span>Data pendaftaran Anda dienkripsi secara aman sesuai standar perlindungan privasi.</span>
            </p>
        </footer>

    </div>
</x-guest-layout>
