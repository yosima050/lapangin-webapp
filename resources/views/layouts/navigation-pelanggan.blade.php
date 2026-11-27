{{-- Unified Responsive Navigation Bar untuk Pelanggan LapangIn --}}
<div x-data="{ mobileMenuOpen: false }"
     @keydown.escape.window="mobileMenuOpen = false"
     @pagehide.window="mobileMenuOpen = false">
    {{-- Backdrop Redup Halus saat Mobile Menu Terbuka (mulai di bawah header agar warna putih header & drawer menyatu 100%) --}}
    <div
        x-show="mobileMenuOpen"
        x-cloak
        @click="mobileMenuOpen = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="lg:hidden fixed inset-x-0 top-20 bottom-0 z-40 bg-slate-900/25 backdrop-blur-[2px]"
        aria-hidden="true"
    ></div>

<header
    class="fixed top-0 left-0 right-0 z-50 backdrop-blur-md border-b transition-colors duration-200"
    :class="mobileMenuOpen ? 'bg-white border-transparent shadow-none lg:bg-white/95 lg:border-gray-100 lg:shadow-xs' : 'bg-white/95 border-gray-100 shadow-xs'"
>
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="h-20 flex items-center justify-between">

            {{-- Logo Brand --}}
            <a
                href="{{ route('home') }}"
                @click="mobileMenuOpen = false"
                class="flex items-center shrink-0 hover:opacity-90 transition"
                aria-label="LapangIn"
            >
                <x-application-logo />
            </a>

            {{-- Navigasi Desktop (Posisi Tengah Seimbang & Proporsional ke Kanan) --}}
            <div class="hidden lg:flex items-center gap-8 xl:gap-11 mx-auto">

                <a
                    href="{{ route('home') }}"
                    class="py-2 text-sm font-semibold transition-colors whitespace-nowrap {{ request()->routeIs('home') ? 'text-[#1a146b] font-bold border-b-2 border-[#acf847]' : 'text-gray-600 hover:text-[#1a146b]' }}"
                >
                    Beranda
                </a>

                <a
                    href="{{ route('katalog.index') }}"
                    class="py-2 text-sm font-semibold transition-colors whitespace-nowrap {{ request()->routeIs('katalog.*') && !request()->routeIs('pelanggan.*') ? 'text-[#1a146b] font-bold border-b-2 border-[#acf847]' : 'text-gray-600 hover:text-[#1a146b]' }}"
                >
                    Jelajahi Lapangan
                </a>

                <a
                    href="{{ route('jadwal.index') }}"
                    class="py-2 text-sm font-semibold transition-colors whitespace-nowrap {{ request()->routeIs('jadwal.*') ? 'text-[#1a146b] font-bold border-b-2 border-[#acf847]' : 'text-gray-600 hover:text-[#1a146b]' }}"
                >
                    Cek Jadwal
                </a>

                @auth
                    @if (!in_array(Auth::user()->role, ['admin', 'kasir']))
                        <a
                            href="{{ route('pelanggan.booking.index') }}"
                            class="py-2 text-sm font-semibold transition-colors whitespace-nowrap {{ request()->routeIs('pelanggan.booking.*') || request()->routeIs('pelanggan.riwayat') ? 'text-[#1a146b] font-bold border-b-2 border-[#acf847]' : 'text-gray-600 hover:text-[#1a146b]' }}"
                        >
                            Riwayat Booking
                        </a>
                    @endif
                @endauth

                <a
                    href="{{ route('bantuan') }}"
                    class="py-2 text-sm font-semibold transition-colors whitespace-nowrap {{ request()->routeIs('bantuan') ? 'text-[#1a146b] font-bold border-b-2 border-[#acf847]' : 'text-gray-600 hover:text-[#1a146b]' }}"
                >
                    Pusat Bantuan
                </a>

            </div>

            {{-- Navigasi Kanan (Akun) --}}
            <div class="hidden lg:flex items-center gap-4 ml-auto">

                @guest
                    <div class="flex items-center gap-2.5">
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-[#1a146b] hover:bg-gray-50 rounded-xl transition">
                            <x-lucide-log-in class="w-4 h-4 shrink-0" />
                            <span>Masuk</span>
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#1a146b] hover:bg-[#130f52] text-white text-xs font-bold shadow-xs hover:shadow-md transition">
                            <x-lucide-user-plus class="w-4 h-4 shrink-0" />
                            <span>Daftar</span>
                        </a>
                    </div>
                @endguest

                @auth
                    {{-- Profile Dropdown --}}
                    <div class="relative" x-data="{ profileOpen: false }" @click.outside="profileOpen = false">
                        <button
                            type="button"
                            @click="profileOpen = !profileOpen"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-gray-200/80 bg-white hover:bg-gray-50 transition cursor-pointer shadow-2xs"
                        >
                            <span class="text-xs font-bold text-gray-800 max-w-[140px] truncate">
                                {{ Auth::user()->nama ?? Auth::user()->name ?? 'Pengguna' }}
                            </span>

                            <x-lucide-chevron-down class="text-gray-400 w-4 h-4 shrink-0 transition-transform duration-200" x-bind:class="profileOpen ? 'rotate-180' : ''" />
                        </button>

                        <div
                            x-show="profileOpen"
                            x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-64 rounded-2xl bg-white border border-gray-100 shadow-xl py-2 z-50 text-xs"
                        >
                            <div class="px-4 py-2.5 border-b border-gray-100">
                                <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Akun Masuk</p>
                                <p class="text-xs font-bold text-[#0b1c30] truncate">{{ Auth::user()->nama ?? Auth::user()->name }}</p>
                                <p class="text-[11px] text-gray-500 truncate">{{ Auth::user()->email }}</p>
                            </div>

                            @if (Auth::user()->role === 'admin')
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#1a146b]">
                                    <x-lucide-layout-dashboard class="w-4 h-4 text-[#1a146b] shrink-0" />
                                    <span>Dashboard Admin</span>
                                </a>
                            @elseif (Auth::user()->role === 'kasir')
                                <a href="{{ route('kasir.index') }}" class="flex items-center gap-2.5 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#1a146b]">
                                    <x-lucide-calculator class="w-4 h-4 text-[#1a146b] shrink-0" />
                                    <span>Portal Kasir POS</span>
                                </a>
                            @else
                                <a href="{{ route('pelanggan.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#1a146b] {{ request()->routeIs('pelanggan.dashboard') ? 'bg-[#eff4ff] text-[#1a146b] font-bold' : '' }}">
                                    <x-lucide-circle-user class="w-4 h-4 text-[#1a146b] shrink-0" />
                                    <span>Profil &amp; Pengaturan Akun</span>
                                </a>
                                <a href="{{ route('pelanggan.booking.index') }}" class="flex items-center gap-2.5 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#1a146b] {{ request()->routeIs('pelanggan.booking.*') || request()->routeIs('pelanggan.riwayat') ? 'bg-[#eff4ff] text-[#1a146b] font-bold' : '' }}">
                                    <x-lucide-receipt-text class="w-4 h-4 text-[#1a146b] shrink-0" />
                                    <span>Riwayat Booking &amp; Jadwal</span>
                                </a>
                                <a href="{{ route('katalog.index') }}" class="flex items-center gap-2.5 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#1a146b]">
                                    <x-lucide-dribbble class="w-4 h-4 text-[#1a146b] shrink-0" />
                                    <span>Cari &amp; Pesan Lapangan</span>
                                </a>
                            @endif

                            <div class="border-t border-gray-100 my-1"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2 font-bold text-red-600 hover:bg-red-50 transition cursor-pointer">
                                    <x-lucide-log-out class="w-4 h-4 shrink-0" />
                                    <span>Keluar (Log Out)</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth

            </div>

            {{-- Tombol Mobile Menu (@animated-burgers/burger-squeeze) --}}
            <button
                type="button"
                @click="mobileMenuOpen = !mobileMenuOpen"
                class="lg:hidden relative inline-flex h-10 w-10 items-center justify-center rounded-xl transition-colors duration-200 cursor-pointer"
                :class="mobileMenuOpen ? 'bg-[#eff4ff]' : 'hover:bg-gray-100'"
                :aria-expanded="mobileMenuOpen"
                :aria-label="mobileMenuOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi'"
            >
                <span class="sr-only">Toggle navigasi</span>
                <div class="burger burger-squeeze lapangin-burger" :class="{ 'open': mobileMenuOpen }">
                    <div class="burger-lines"></div>
                </div>
            </button>

        </div>
    </nav>
</header>

    {{-- Clean Sporty Right Slide-Over Mobile Drawer (Selaras Tema Terang LapangIn) --}}
    <aside
        x-show="mobileMenuOpen"
        x-cloak
        @click="if ($event.target.closest('a, button[type=submit]')) mobileMenuOpen = false"
        x-transition:enter="transition ease-[cubic-bezier(0.22,1,0.36,1)] duration-250"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="lg:hidden fixed top-20 right-0 bottom-0 z-50 w-[82%] max-w-[320px] bg-white text-[#0b1c30] shadow-[-12px_0_32px_rgba(11,28,48,0.10)] border-l border-gray-200/80 flex flex-col justify-between overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
        aria-label="Menu navigasi mobile"
    >
        <div class="p-4 space-y-5">
            {{-- Kartu Akun di Atas (Bisa diklik langsung ke Profil) --}}
            @auth
                @php
                    $profileRoute = Auth::user()->role === 'admin'
                        ? route('admin.dashboard')
                        : (Auth::user()->role === 'kasir' ? route('kasir.index') : route('pelanggan.dashboard'));
                @endphp
                <a
                    href="{{ $profileRoute }}"
                    class="flex items-center justify-between gap-3 rounded-2xl bg-[#f4f7ff] hover:bg-[#eaf0ff] border border-[#dce9ff] p-3.5 transition active:scale-[0.98]"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <x-lucide-user class="w-5 h-5 text-[#1a146b] shrink-0" />
                        <div class="min-w-0">
                            <p class="text-xs font-extrabold text-[#0b1c30] truncate">{{ Auth::user()->nama ?? Auth::user()->name }}</p>
                            <p class="text-[11px] text-gray-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    <x-lucide-chevron-right class="w-4 h-4 text-gray-400 shrink-0" />
                </a>
            @endauth

            {{-- Daftar Navigasi Utama --}}
            <div>
                <p class="px-3 mb-2 text-[10px] font-extrabold uppercase tracking-[0.16em] text-gray-400">
                    Menu Utama
                </p>
                <div class="flex flex-col gap-1.5">
                    <a
                        href="{{ route('home') }}"
                        class="flex items-center justify-between gap-3 px-3.5 py-3 rounded-xl text-sm transition-all duration-150 active:scale-[0.98] {{ request()->routeIs('home') ? 'bg-[#1a146b] text-white font-bold shadow-sm' : 'text-gray-700 hover:bg-[#f4f7ff] hover:text-[#1a146b] font-semibold' }}"
                    >
                        <span class="flex items-center gap-3">
                            <x-lucide-house class="w-4 h-4 shrink-0 {{ request()->routeIs('home') ? 'text-[#acf847]' : 'text-gray-400' }}" />
                            <span>Beranda</span>
                        </span>
                        @if (request()->routeIs('home'))
                            <span class="h-1.5 w-1.5 rounded-full bg-[#acf847]"></span>
                        @endif
                    </a>

                    <a
                        href="{{ route('katalog.index') }}"
                        class="flex items-center justify-between gap-3 px-3.5 py-3 rounded-xl text-sm transition-all duration-150 active:scale-[0.98] {{ request()->routeIs('katalog.*') && !request()->routeIs('pelanggan.*') ? 'bg-[#1a146b] text-white font-bold shadow-sm' : 'text-gray-700 hover:bg-[#f4f7ff] hover:text-[#1a146b] font-semibold' }}"
                    >
                        <span class="flex items-center gap-3">
                            <x-lucide-dribbble class="w-4 h-4 shrink-0 {{ request()->routeIs('katalog.*') && !request()->routeIs('pelanggan.*') ? 'text-[#acf847]' : 'text-gray-400' }}" />
                            <span>Jelajahi Lapangan</span>
                        </span>
                        @if (request()->routeIs('katalog.*') && !request()->routeIs('pelanggan.*'))
                            <span class="h-1.5 w-1.5 rounded-full bg-[#acf847]"></span>
                        @endif
                    </a>

                    <a
                        href="{{ route('jadwal.index') }}"
                        class="flex items-center justify-between gap-3 px-3.5 py-3 rounded-xl text-sm transition-all duration-150 active:scale-[0.98] {{ request()->routeIs('jadwal.*') ? 'bg-[#1a146b] text-white font-bold shadow-sm' : 'text-gray-700 hover:bg-[#f4f7ff] hover:text-[#1a146b] font-semibold' }}"
                    >
                        <span class="flex items-center gap-3">
                            <x-lucide-calendar-days class="w-4 h-4 shrink-0 {{ request()->routeIs('jadwal.*') ? 'text-[#acf847]' : 'text-gray-400' }}" />
                            <span>Cek Jadwal</span>
                        </span>
                        @if (request()->routeIs('jadwal.*'))
                            <span class="h-1.5 w-1.5 rounded-full bg-[#acf847]"></span>
                        @endif
                    </a>

                    @auth
                        @if (!in_array(Auth::user()->role, ['admin', 'kasir']))
                            <a
                                href="{{ route('pelanggan.booking.index') }}"
                                class="flex items-center justify-between gap-3 px-3.5 py-3 rounded-xl text-sm transition-all duration-150 active:scale-[0.98] {{ request()->routeIs('pelanggan.booking.*') || request()->routeIs('pelanggan.riwayat') ? 'bg-[#1a146b] text-white font-bold shadow-sm' : 'text-gray-700 hover:bg-[#f4f7ff] hover:text-[#1a146b] font-semibold' }}"
                            >
                                <span class="flex items-center gap-3">
                                    <x-lucide-receipt-text class="w-4 h-4 shrink-0 {{ request()->routeIs('pelanggan.booking.*') || request()->routeIs('pelanggan.riwayat') ? 'text-[#acf847]' : 'text-gray-400' }}" />
                                    <span>Riwayat Booking</span>
                                </span>
                                @if (request()->routeIs('pelanggan.booking.*') || request()->routeIs('pelanggan.riwayat'))
                                    <span class="h-1.5 w-1.5 rounded-full bg-[#acf847]"></span>
                                @endif
                            </a>
                        @endif
                    @endauth

                    <a
                        href="{{ route('bantuan') }}"
                        class="flex items-center justify-between gap-3 px-3.5 py-3 rounded-xl text-sm transition-all duration-150 active:scale-[0.98] {{ request()->routeIs('bantuan') ? 'bg-[#1a146b] text-white font-bold shadow-sm' : 'text-gray-700 hover:bg-[#f4f7ff] hover:text-[#1a146b] font-semibold' }}"
                    >
                        <span class="flex items-center gap-3">
                            <x-lucide-circle-help class="w-4 h-4 shrink-0 {{ request()->routeIs('bantuan') ? 'text-[#acf847]' : 'text-gray-400' }}" />
                            <span>Pusat Bantuan</span>
                        </span>
                        @if (request()->routeIs('bantuan'))
                            <span class="h-1.5 w-1.5 rounded-full bg-[#acf847]"></span>
                        @endif
                    </a>
                </div>
            </div>
        </div>

        {{-- Footer Dock Bawah --}}
        <div class="p-4 border-t border-gray-100 bg-[#f8faff]">
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-red-200 bg-white hover:bg-red-50 text-xs font-bold text-red-600 transition active:scale-[0.98] cursor-pointer shadow-2xs"
                    >
                        <x-lucide-log-out class="w-4 h-4 shrink-0" />
                        <span>Keluar (Log Out)</span>
                    </button>
                </form>
            @else
                <div class="grid grid-cols-2 gap-2">
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl border border-gray-200 bg-white text-xs font-bold text-[#1a146b] hover:bg-gray-50 transition active:scale-[0.98]"
                    >
                        <x-lucide-log-in class="w-4 h-4 shrink-0" />
                        <span>Masuk</span>
                    </a>
                    <a
                        href="{{ route('register') }}"
                        class="inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-[#1a146b] hover:bg-[#130f52] text-white text-xs font-extrabold shadow-sm transition active:scale-[0.98]"
                    >
                        <x-lucide-user-plus class="w-4 h-4 shrink-0" />
                        <span>Daftar</span>
                    </a>
                </div>
            @endauth
        </div>
    </aside>
</div>
