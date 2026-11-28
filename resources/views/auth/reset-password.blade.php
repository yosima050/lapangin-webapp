<x-guest-layout>
    <div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8 flex flex-col justify-between"
         x-data="{ showPassword: false, showPasswordConfirm: false }">

        {{-- Top Navigation --}}
        <div class="max-w-md mx-auto w-full flex items-center justify-between mb-6">
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs sm:text-sm font-bold text-gray-600 hover:text-[#1a146b] hover:bg-white/80 transition shadow-xs">
                <x-lucide-arrow-left class="w-4 h-4 shrink-0" />
                <span>Kembali ke Login</span>
            </a>

            <x-application-logo />
        </div>

        {{-- Main Card --}}
        <main class="max-w-md mx-auto w-full my-auto">
            <div class="bg-white rounded-3xl p-7 sm:p-10 shadow-xl border border-[#e5eeff]">
                <div class="w-12 h-12 rounded-2xl bg-[#eff4ff] text-[#1a146b] flex items-center justify-center mb-4">
                    <x-lucide-key-round class="w-6 h-6 shrink-0" />
                </div>

                <h1 class="text-2xl font-headline font-black text-[#0b1c30] tracking-tight">
                    Setel Ulang Kata Sandi
                </h1>

                <p class="text-xs sm:text-sm text-gray-500 mt-1 leading-relaxed">
                    Silakan tentukan kata sandi baru untuk akun LapangIn Anda.
                </p>

                <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div>
                        <label for="email" class="text-[11px] font-bold uppercase tracking-wider text-gray-700 block mb-1.5">
                            EMAIL AKUN
                        </label>

                        <div class="relative rounded-xl bg-[#eff4ff] border border-transparent focus-within:border-[#1a146b] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#1a146b]/15 transition">
                            <x-lucide-mail class="text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 pointer-events-none shrink-0" />
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email', $request->email) }}"
                                required
                                autofocus
                                autocomplete="username"
                                class="w-full pl-11 pr-4 py-3 bg-transparent text-sm text-gray-900 font-medium placeholder-gray-400 outline-none border-none focus:ring-0"
                            >
                        </div>

                        @if ($errors->has('email'))
                            <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                <x-lucide-circle-alert class="w-4 h-4 shrink-0" />
                                <span>{{ $errors->first('email') }}</span>
                            </p>
                        @endif
                    </div>

                    <div>
                        <label for="password" class="text-[11px] font-bold uppercase tracking-wider text-gray-700 block mb-1.5">
                            KATA SANDI BARU
                        </label>

                        <div class="relative rounded-xl bg-[#eff4ff] border border-transparent focus-within:border-[#1a146b] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#1a146b]/15 transition">
                            <x-lucide-lock class="text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 pointer-events-none shrink-0" />
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
                                <x-lucide-eye x-show="!showPassword" class="w-5 h-5 shrink-0" />
                                <x-lucide-eye-off x-show="showPassword" style="display: none;" class="w-5 h-5 shrink-0" />
                            </button>
                        </div>

                        @if ($errors->has('password'))
                            <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                <x-lucide-circle-alert class="w-4 h-4 shrink-0" />
                                <span>{{ $errors->first('password') }}</span>
                            </p>
                        @endif
                    </div>

                    <div>
                        <label for="password_confirmation" class="text-[11px] font-bold uppercase tracking-wider text-gray-700 block mb-1.5">
                            KONFIRMASI KATA SANDI BARU
                        </label>

                        <div class="relative rounded-xl bg-[#eff4ff] border border-transparent focus-within:border-[#1a146b] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#1a146b]/15 transition">
                            <x-lucide-key-round class="text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 pointer-events-none shrink-0" />
                            <input
                                id="password_confirmation"
                                :type="showPasswordConfirm ? 'text' : 'password'"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Ulangi kata sandi baru"
                                class="w-full pl-11 pr-11 py-3 bg-transparent text-sm text-gray-900 font-medium placeholder-gray-400 outline-none border-none focus:ring-0"
                            >
                            <button
                                type="button"
                                @click="showPasswordConfirm = !showPasswordConfirm"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 transition"
                                tabindex="-1"
                            >
                                <x-lucide-eye x-show="!showPasswordConfirm" class="w-5 h-5 shrink-0" />
                                <x-lucide-eye-off x-show="showPasswordConfirm" style="display: none;" class="w-5 h-5 shrink-0" />
                            </button>
                        </div>

                        @if ($errors->has('password_confirmation'))
                            <p class="text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1">
                                <x-lucide-circle-alert class="w-4 h-4 shrink-0" />
                                <span>{{ $errors->first('password_confirmation') }}</span>
                            </p>
                        @endif
                    </div>

                    <button
                        type="submit"
                        class="w-full mt-6 py-3.5 px-6 rounded-xl bg-[#161358] hover:bg-[#25207c] text-white font-headline font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <span>Simpan Kata Sandi Baru</span>
                        <x-lucide-save class="w-4 h-4 shrink-0" />
                    </button>
                </form>
            </div>
        </main>

        {{-- Footer --}}
        <footer class="max-w-md mx-auto w-full text-center py-6 mt-6">
            <p class="text-xs text-gray-400">
                © {{ date('Y') }} LapangIn Indonesia.
            </p>
        </footer>

    </div>
</x-guest-layout>
