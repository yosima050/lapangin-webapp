<x-guest-layout :title="'LapangIn'">
    <div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8 flex flex-col justify-between">

        {{-- Top Navigation --}}
        <div class="max-w-7xl mx-auto w-full flex items-center justify-between mb-6 sm:mb-10">
            <a href="{{ route('register') }}"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs sm:text-sm font-bold text-gray-600 hover:text-[#1a146b] hover:bg-white/80 transition shadow-xs">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                <span>Ubah Data Registrasi</span>
            </a>

            <div class="lg:hidden">
                <x-application-logo />
            </div>
        </div>

        <main class="max-w-md mx-auto w-full flex-1 flex flex-col justify-center">
            <div class="bg-white/95 backdrop-blur-md rounded-3xl p-6 sm:p-8 shadow-xl border border-gray-100"
                 x-data="{
                     remaining: Math.floor({{ $remainingSeconds }}),
                     timerText: '05:00',
                     isExpired: {{ $expired ? 'true' : 'false' }},
                     canResend: {{ $remainingSeconds <= 240 ? 'true' : 'false' }},
                     otpDigits: ['', '', '', '', '', ''],
                     updateTimer() {
                         if (this.remaining <= 0) {
                             this.isExpired = true;
                             this.timerText = '00:00';
                             return;
                         }
                         const m = Math.floor(this.remaining / 60).toString().padStart(2, '0');
                         const s = Math.floor(this.remaining % 60).toString().padStart(2, '0');
                         this.timerText = `${m}:${s}`;
                         this.remaining--;
                     },
                     init() {
                         this.updateTimer();
                         setInterval(() => {
                             this.updateTimer();
                         }, 1000);
                         this.$nextTick(() => {
                             if (this.$refs.digit0) this.$refs.digit0.focus();
                         });
                     },
                     handleInput(index, event) {
                         const val = event.target.value.replace(/[^0-9]/g, '');
                         this.otpDigits[index] = val ? val.slice(-1) : '';
                         event.target.value = this.otpDigits[index];

                         if (this.otpDigits[index] && index < 5) {
                             this.$refs['digit' + (index + 1)].focus();
                         }
                         this.syncOtp();
                     },
                     handleKeyDown(index, event) {
                         if (event.key === 'Backspace' && !this.otpDigits[index] && index > 0) {
                             this.$refs['digit' + (index - 1)].focus();
                         }
                     },
                     handlePaste(event) {
                         event.preventDefault();
                         const paste = (event.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
                         if (paste.length > 0) {
                             for (let i = 0; i < 6; i++) {
                                 this.otpDigits[i] = paste[i] || '';
                             }
                             this.syncOtp();
                             const focusIndex = Math.min(paste.length, 5);
                             this.$refs['digit' + focusIndex].focus();
                         }
                     },
                     syncOtp() {
                         this.$refs.hiddenOtp.value = this.otpDigits.join('');
                     }
                 }">
                {{-- Header Icon --}}
                <div class="text-center mb-6">
                    <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-[#eff4ff] flex items-center justify-center text-[#1a146b]">
                        <span class="material-symbols-outlined text-3xl">mark_email_read</span>
                    </div>
                    <h2 class="text-2xl font-black text-[#0b1c30]">Verifikasi Email Anda</h2>
                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                        Kami telah mengirimkan 6 digit kode OTP ke alamat email:
                    </p>
                    <p class="font-bold text-gray-800 text-sm mt-1 bg-gray-50 py-1.5 px-3 rounded-lg inline-block border border-gray-200">
                        {{ $email }}
                    </p>
                </div>

                {{-- Alert Status --}}
                @if (session('status'))
                    <div class="mb-5 p-3 rounded-xl bg-green-50 border border-green-200 text-xs text-green-700 flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-green-600">check_circle</span>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                {{-- Alert Error --}}
                @if ($errors->has('otp'))
                    <div class="mb-5 p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700 flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-red-600">error</span>
                        <span>{{ $errors->first('otp') }}</span>
                    </div>
                @endif

                {{-- Form OTP --}}
                <form method="POST" action="{{ route('register.otp.verify') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="otp" x-ref="hiddenOtp" required>

                    <div>
                        <label class="block text-center text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">
                            Masukkan 6 Digit Kode OTP
                        </label>
                        <div class="flex justify-center gap-2 sm:gap-3" @paste="handlePaste($event)">
                            <template x-for="(digit, idx) in 6" :key="idx">
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    maxlength="1"
                                    :x-ref="'digit' + idx"
                                    class="w-11 h-14 sm:w-12 sm:h-14 text-center text-xl font-black text-[#1a146b] bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#1a146b] focus:ring-2 focus:ring-[#1a146b]/20 outline-none transition shadow-xs"
                                    @input="handleInput(idx, $event)"
                                    @keydown="handleKeyDown(idx, $event)"
                                >
                            </template>
                        </div>
                    </div>

                    {{-- Countdown Timer --}}
                    <div class="text-center">
                        <template x-if="!isExpired">
                            <p class="text-xs text-gray-500 font-medium">
                                Kode kedaluwarsa dalam:
                                <span class="font-bold text-[#1a146b]" x-text="timerText"></span>
                            </p>
                        </template>
                        <template x-if="isExpired">
                            <p class="text-xs text-red-600 font-bold">
                                Kode OTP telah kedaluwarsa. Silakan minta kode baru di bawah ini.
                            </p>
                        </template>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-3.5 px-4 rounded-xl bg-[#1a146b] hover:bg-[#120d52] active:scale-[0.99] text-white font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <span>Verifikasi &amp; Lanjutkan</span>
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </button>
                </form>

                {{-- Resend OTP Section --}}
                <div class="mt-6 pt-6 border-t border-gray-100 text-center">
                    <p class="text-xs text-gray-500 mb-3">Tidak menerima email atau kode tidak muncul?</p>
                    <form method="POST" action="{{ route('register.otp.resend') }}" x-data="{ sending: false }" @submit="sending = true">
                        @csrf
                        <button
                            type="submit"
                            :disabled="sending"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-[#1a146b] hover:text-[#0b1c30] hover:underline transition cursor-pointer disabled:opacity-50"
                        >
                            <span class="material-symbols-outlined text-base" :class="sending ? 'animate-spin' : ''">refresh</span>
                            <span x-text="sending ? 'Mengirim ulang...' : 'Kirim Ulang Kode OTP'"></span>
                        </button>
                    </form>
                </div>
            </div>
        </main>

        {{-- Footer Simple --}}
        <div class="text-center text-xs text-gray-400 py-4">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</x-guest-layout>

