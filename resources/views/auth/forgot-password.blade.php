<x-guest-layout>
    <div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8 flex flex-col justify-between">

        {{-- Top Navigation --}}
        <div class="max-w-md mx-auto w-full flex items-center justify-between mb-6">
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs sm:text-sm font-bold text-gray-600 hover:text-[#1a146b] hover:bg-white/80 transition shadow-xs">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                <span>Kembali ke Login</span>
            </a>

            <x-application-logo />
        </div>

        {{-- Main Card --}}
        <main class="max-w-md mx-auto w-full my-auto">
            <div class="bg-white rounded-3xl p-7 sm:p-10 shadow-xl border border-[#e5eeff]">
                <div class="w-12 h-12 rounded-2xl bg-[#eff4ff] text-[#1a146b] flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-2xl">lock_reset</span>
                </div>

                <h1 class="text-2xl font-headline font-black text-[#0b1c30] tracking-tight">
                    Lupa Kata Sandi?
                </h1>

                <p class="text-xs sm:text-sm text-gray-500 mt-1 leading-relaxed">
                    Masukkan alamat email akun Anda. Kami akan mengirimkan tautan pemulihan kata sandi.
                </p>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="mt-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">check_circle</span>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="text-[11px] font-bold uppercase tracking-wider text-gray-700 block mb-1.5">
                            EMAIL TERDAFTAR
                        </label>

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
                                autofocus
                                placeholder="nama@email.com"
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

                    <button
                        type="submit"
                        class="w-full mt-4 py-3.5 px-6 rounded-xl bg-[#161358] hover:bg-[#25207c] text-white font-headline font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <span>Kirim Tautan Pemulihan</span>
                        <span class="material-symbols-outlined text-sm">send</span>
                    </button>
                </form>

                <div class="mt-6 pt-5 border-t border-gray-100 text-center">
                    <a href="{{ route('login') }}" class="text-xs font-bold text-[#1a146b] hover:underline">
                        ← Ingat kata sandi? Masuk sekarang
                    </a>
                </div>
            </div>
        </main>

        {{-- Footer --}}
        <footer class="max-w-md mx-auto w-full text-center py-6 mt-6">
            <p class="text-xs text-gray-400">
                © {{ date('Y') }} LapangIn Indonesia. Portal Pemesanan &amp; Reservasi Olahraga.
            </p>
        </footer>

    </div>
</x-guest-layout>
