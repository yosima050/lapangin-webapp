<x-guest-layout>
    <div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8 flex flex-col justify-between">

        {{-- Top Navigation Bar --}}
        <div class="max-w-4xl mx-auto w-full flex items-center justify-between mb-6">
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold text-gray-600 hover:text-[#1a146b] hover:bg-white/80 transition shadow-xs">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                <span>Kembali ke Beranda</span>
            </a>

            <div>
                <x-application-logo />
            </div>

            <div class="hidden sm:flex items-center gap-2 text-xs font-semibold text-gray-500">
                <span class="w-2 h-2 rounded-full bg-[#416900] animate-pulse"></span>
                <span>LapangIn Security</span>
            </div>
        </div>

        {{-- Verification Card Container --}}
        <main class="max-w-lg mx-auto w-full flex-1 flex items-center justify-center my-6">
            <div class="w-full bg-white rounded-3xl border border-gray-100 shadow-xl p-6 sm:p-8 relative overflow-hidden">
                {{-- Decorative Top Color Stripe --}}
                <div class="absolute top-0 inset-x-0 h-2 bg-gradient-to-r from-[#1a146b] via-[#416900] to-[#acf847]"></div>

                {{-- Icon and Title --}}
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-2xl bg-[#eff4ff] text-[#1a146b] flex items-center justify-center mx-auto mb-4 shadow-xs">
                        <span class="material-symbols-outlined text-3xl">mark_email_unread</span>
                    </div>
                    <h1 class="text-2xl font-headline font-black text-[#0b1c30] tracking-tight">
                        Verifikasi Alamat Email
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">
                        Terima kasih telah bergabung di <strong class="text-[#1a146b]">LapangIn</strong>! Mohon verifikasi email akun Anda untuk memastikan keamanan akses dan pemesanan.
                    </p>
                </div>

                {{-- Logged-in User Account Card --}}
                @if (Auth::check())
                    <div class="mb-6 p-4 rounded-2xl bg-gray-50 border border-gray-200/80 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(substr(Auth::user()->nama ?? 'U', 0, 1)) }}
                            </div>
                            <div class="text-left">
                                <p class="text-xs text-gray-500 font-medium">Akun yang sedang login:</p>
                                <p class="text-sm font-bold text-[#0b1c30] truncate max-w-[200px] sm:max-w-xs">
                                    {{ Auth::user()->email }}
                                </p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase tracking-wider
                            {{ Auth::user()->role === 'admin' ? 'bg-purple-100 text-purple-700' : (Auth::user()->role === 'kasir' ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800') }}">
                            {{ Auth::user()->role }}
                        </span>
                    </div>
                @endif

                {{-- Status Alert (if link re-sent) --}}
                @if (session('status') == 'verification-link-sent')
                    <div class="mb-6 p-4 rounded-2xl bg-green-50 border border-green-200 flex items-start gap-3">
                        <span class="material-symbols-outlined text-green-600 text-xl flex-shrink-0 mt-0.5">check_circle</span>
                        <div class="text-xs sm:text-sm font-semibold text-green-800">
                            Tautan verifikasi baru telah berhasil dikirimkan ke alamat email Anda. Silakan periksa kotak masuk atau spam.
                        </div>
                    </div>
                @endif

                {{-- Instructions --}}
                <div class="p-3.5 bg-blue-50/70 border border-blue-100 rounded-2xl text-xs text-blue-900 leading-relaxed mb-6 flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-blue-700 text-base mt-0.5 flex-shrink-0">info</span>
                    <span>
                        Klik tombol di email yang Anda terima. Jika email belum masuk dalam 1–2 menit, klik tombol kirim ulang di bawah.
                    </span>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                    {{-- Resend Verification Form --}}
                    <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:flex-1">
                        @csrf
                        <button type="submit"
                                class="w-full py-3 px-4 rounded-xl bg-[#1a146b] hover:bg-[#130f52] text-white font-bold text-sm tracking-wide transition shadow-sm hover:shadow-md flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-lg">forward_to_inbox</span>
                            <span>Kirim Ulang Email</span>
                        </button>
                    </form>

                    {{-- Log Out Form (Prominent & Clear) --}}
                    <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit"
                                class="w-full sm:w-auto py-3 px-4 rounded-xl border border-red-200 bg-red-50/60 hover:bg-red-100/80 text-red-700 font-bold text-sm transition flex items-center justify-center gap-2"
                                title="Keluar dari akun ini">
                            <span class="material-symbols-outlined text-lg">logout</span>
                            <span>Keluar (Log Out)</span>
                        </button>
                    </form>
                </div>
            </div>
        </main>

        {{-- Footer --}}
        <footer class="max-w-7xl mx-auto w-full text-center text-xs text-gray-500 py-4">
            &copy; {{ date('Y') }} LapangIn Indonesia. Platform Reservasi &amp; POS Arena Olahraga.
        </footer>
    </div>
</x-guest-layout>
