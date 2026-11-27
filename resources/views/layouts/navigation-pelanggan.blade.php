{{-- Unified Responsive Navigation Bar untuk Pelanggan LapangIn --}}
<div x-data="{ mobileMenuOpen: false }" @keydown.escape.window="mobileMenuOpen = false">
    {{-- Backdrop Redup Halus saat Mobile Menu Terbuka --}}
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
        class="lg:hidden fixed inset-0 z-40 bg-slate-900/25 backdrop-blur-[2px]"
        aria-hidden="true"
    ></div>

<header class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-xs">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="h-20 flex items-center justify-between">

            {{-- Logo Brand --}}
            <a
                href="{{ route('home') }}"
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
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-bold text-[#1a146b] hover:bg-gray-50 rounded-xl transition">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl bg-[#1a146b] hover:bg-[#130f52] text-white text-xs font-bold shadow-xs hover:shadow-md transition">
                            Daftar
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

            {{-- Tombol Mobile Menu Hamburger (Animasi Morph 3 Garis -> Silang X) --}}
            <button
                type="button"
                @click="mobileMenuOpen = !mobileMenuOpen"
                class="lg:hidden relative inline-flex h-10 w-10 items-center justify-center rounded-xl text-gray-700 hover:bg-gray-100 active:scale-95 transition-all duration-200 cursor-pointer"
                :class="mobileMenuOpen ? 'bg-gray-100 text-[#1a146b]' : ''"
                :aria-expanded="mobileMenuOpen"
                :aria-label="mobileMenuOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi'"
            >
                <span class="sr-only">Toggle navigasi</span>
                <span class="relative flex h-4 w-5 flex-col items-center justify-center">
                    <span
                        class="absolute h-[2px] w-5 rounded-full bg-current transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
                        :class="mobileMenuOpen ? 'translate-y-0 rotate-45' : '-translate-y-[6px] rotate-0'"
                    ></span>
                    <span
                        class="absolute h-[2px] w-5 rounded-full bg-current transition-all duration-200 ease-out"
                        :class="mobileMenuOpen ? 'opacity-0 scale-x-0' : 'opacity-100 scale-x-100'"
                    ></span>
                    <span
                        class="absolute h-[2px] w-5 rounded-full bg-current transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
                        :class="mobileMenuOpen ? 'translate-y-0 -rotate-45' : 'translate-y-[6px] rotate-0'"
                    ></span>
                </span>
            </button>

        </div>

        {{-- Mobile Menu Drawer --}}
        <div
            x-show="mobileMenuOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200 origin-top"
            x-transition:enter-start="opacity-0 -translate-y-2 scale-[0.99]"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150 origin-top"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-2 scale-[0.99]"
            class="lg:hidden border-t border-gray-100 py-4 bg-white"
        >
            <div class="flex flex-col gap-1.5">

                <a
                    href="{{ route('home') }}"
                    class="px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-150 active:scale-[0.98] {{ request()->routeIs('home') ? 'font-bold text-[#1a146b] bg-[#eff4ff]' : 'text-gray-700 hover:bg-gray-50' }}"
                >
                    Beranda
                </a>

                <a
                    href="{{ route('katalog.index') }}"
                    class="px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-150 active:scale-[0.98] {{ request()->routeIs('katalog.*') && !request()->routeIs('pelanggan.*') ? 'font-bold text-[#1a146b] bg-[#eff4ff]' : 'text-gray-700 hover:bg-gray-50' }}"
                >
                    Jelajahi Lapangan
                </a>

                <a
                    href="{{ route('jadwal.index') }}"
                    class="px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-150 active:scale-[0.98] {{ request()->routeIs('jadwal.*') ? 'font-bold text-[#1a146b] bg-[#eff4ff]' : 'text-gray-700 hover:bg-gray-50' }}"
                >
                    Cek Jadwal
                </a>

                @auth
                    @if (!in_array(Auth::user()->role, ['admin', 'kasir']))
                        <a
                            href="{{ route('pelanggan.booking.index') }}"
                            class="px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-150 active:scale-[0.98] {{ request()->routeIs('pelanggan.booking.*') || request()->routeIs('pelanggan.riwayat') ? 'font-bold text-[#1a146b] bg-[#eff4ff]' : 'text-gray-700 hover:bg-gray-50' }}"
                        >
                            Riwayat Booking
                        </a>
                    @endif

                    <a
                        href="{{ route('bantuan') }}"
                        class="px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-150 active:scale-[0.98] {{ request()->routeIs('bantuan') ? 'font-bold text-[#1a146b] bg-[#eff4ff]' : 'text-gray-700 hover:bg-gray-50' }}"
                    >
                        Pusat Bantuan
                    </a>

                    <div class="pt-2 border-t border-gray-100 mt-2">
                        <div class="px-3 py-2 bg-gray-50 rounded-xl mb-2">
                            <p class="text-xs font-bold text-gray-800">{{ Auth::user()->nama ?? Auth::user()->name }}</p>
                            <p class="text-[11px] text-gray-500">{{ Auth::user()->email }}</p>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2.5 rounded-lg text-sm font-bold text-red-600 hover:bg-red-50 cursor-pointer">
                                Keluar (Log Out)
                            </button>
                        </form>
                    </div>
                @endauth

                @guest
                    <a
                        href="{{ route('bantuan') }}"
                        class="px-3 py-2.5 rounded-lg text-sm font-semibold {{ request()->routeIs('bantuan') ? 'font-bold text-[#1a146b] bg-[#eff4ff]' : 'text-gray-700 hover:bg-gray-50' }}"
                    >
                        Pusat Bantuan
                    </a>

                    <div class="pt-3 border-t border-gray-100 flex flex-col gap-2">
                        <a
                            href="{{ route('login') }}"
                            class="w-full text-center py-2.5 rounded-xl border border-gray-200 text-sm font-bold text-[#1a146b] hover:bg-gray-50"
                        >
                            Masuk
                        </a>
                        <a
                            href="{{ route('register') }}"
                            class="w-full text-center py-2.5 rounded-xl bg-[#1a146b] text-white text-sm font-bold shadow-xs"
                        >
                            Daftar Akun
                        </a>
                    </div>
                @endguest

            </div>
        </div>

    </nav>
</header>
</div>
