{{-- Unified Responsive Navigation Bar untuk Pelanggan LapangIn --}}
<header class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-xs">
    <nav
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
        x-data="{ mobileMenuOpen: false }"
    >
        <div class="h-20 flex items-center justify-between">

            {{-- Logo Brand --}}
            <a
                href="{{ route('home') }}"
                class="flex items-center shrink-0 hover:opacity-90 transition"
                aria-label="LapangIn"
            >
                <x-application-logo />
            </a>

            {{-- Navigasi Desktop --}}
            <div class="hidden lg:flex items-center gap-7 ml-10">

                <a
                    href="{{ route('katalog.index') }}"
                    class="py-2 text-sm font-semibold transition-colors {{ (request()->routeIs('katalog.*') || request()->routeIs('home') || request()->is('/')) && !request()->routeIs('pelanggan.*') ? 'text-[#1a146b] font-bold border-b-2 border-[#acf847]' : 'text-gray-600 hover:text-[#1a146b]' }}"
                >
                    Jelajahi Lapangan
                </a>

                <a
                    href="{{ route('jadwal.index') }}"
                    class="py-2 text-sm font-semibold transition-colors {{ request()->routeIs('jadwal.*') ? 'text-[#1a146b] font-bold border-b-2 border-[#acf847]' : 'text-gray-600 hover:text-[#1a146b]' }}"
                >
                    Cek Jadwal
                </a>

                @auth
                    @if (!in_array(Auth::user()->role, ['admin', 'kasir']))
                        <a
                            href="{{ route('pelanggan.booking.index') }}"
                            class="py-2 text-sm font-semibold transition-colors {{ request()->routeIs('pelanggan.booking.*') || request()->routeIs('pelanggan.riwayat') ? 'text-[#1a146b] font-bold border-b-2 border-[#acf847]' : 'text-gray-600 hover:text-[#1a146b]' }}"
                        >
                            Riwayat Booking
                        </a>

                        <a
                            href="{{ route('pelanggan.dashboard') }}"
                            class="py-2 text-sm font-semibold transition-colors {{ request()->routeIs('pelanggan.dashboard') || request()->routeIs('profile.*') ? 'text-[#1a146b] font-bold border-b-2 border-[#acf847]' : 'text-gray-600 hover:text-[#1a146b]' }}"
                        >
                            Profil Saya
                        </a>
                    @endif
                @endauth

                <a
                    href="{{ route('home') }}#kontak"
                    class="py-2 text-sm font-semibold text-gray-600 hover:text-[#1a146b] transition-colors"
                >
                    Pusat Bantuan
                </a>

            </div>

            {{-- Navigasi Kanan (Lokasi & Akun) --}}
            <div class="hidden lg:flex items-center gap-4 ml-auto">

                {{-- Location Badge --}}
                <div class="flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 border border-gray-100 rounded-xl text-xs font-semibold text-gray-700">
                    <svg class="w-3.5 h-3.5 text-[#416900]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    <span>Malang</span>
                </div>

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
                            class="flex items-center gap-2.5 p-1 rounded-xl hover:bg-gray-50 transition cursor-pointer"
                        >
                            <span class="w-9 h-9 rounded-full bg-[#1a146b] border-2 border-[#acf847] flex items-center justify-center text-sm font-bold text-white shadow-xs">
                                {{ strtoupper(substr(Auth::user()->nama ?? Auth::user()->name ?? 'P', 0, 1)) }}
                            </span>

                            <span class="text-xs font-bold text-gray-800 max-w-[120px] truncate">
                                {{ Auth::user()->nama ?? Auth::user()->name ?? 'Pengguna' }}
                            </span>

                            <span class="material-symbols-outlined text-gray-400 text-sm">expand_more</span>
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
                            style="display: none;"
                        >
                            <div class="px-4 py-2.5 border-b border-gray-100">
                                <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Akun Masuk</p>
                                <p class="text-xs font-bold text-[#0b1c30] truncate">{{ Auth::user()->nama ?? Auth::user()->name }}</p>
                                <p class="text-[11px] text-gray-500 truncate">{{ Auth::user()->email }}</p>
                            </div>

                            @if (Auth::user()->role === 'admin')
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#1a146b]">
                                    <span class="material-symbols-outlined text-base text-[#1a146b]">space_dashboard</span>
                                    <span>Dashboard Admin</span>
                                </a>
                            @elseif (Auth::user()->role === 'kasir')
                                <a href="{{ route('kasir.index') }}" class="flex items-center gap-2.5 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#1a146b]">
                                    <span class="material-symbols-outlined text-base text-[#1a146b]">point_of_sale</span>
                                    <span>Portal Kasir POS</span>
                                </a>
                            @else
                                <a href="{{ route('pelanggan.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#1a146b] {{ request()->routeIs('pelanggan.dashboard') ? 'bg-[#eff4ff] text-[#1a146b] font-bold' : '' }}">
                                    <span class="material-symbols-outlined text-base text-[#1a146b]">account_circle</span>
                                    <span>Profil &amp; Pengaturan Akun</span>
                                </a>
                                <a href="{{ route('pelanggan.booking.index') }}" class="flex items-center gap-2.5 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#1a146b] {{ request()->routeIs('pelanggan.booking.*') || request()->routeIs('pelanggan.riwayat') ? 'bg-[#eff4ff] text-[#1a146b] font-bold' : '' }}">
                                    <span class="material-symbols-outlined text-base text-[#1a146b]">receipt_long</span>
                                    <span>Riwayat Booking &amp; Jadwal</span>
                                </a>
                                <a href="{{ route('katalog.index') }}" class="flex items-center gap-2.5 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#1a146b]">
                                    <span class="material-symbols-outlined text-base text-[#1a146b]">sports_soccer</span>
                                    <span>Cari &amp; Pesan Lapangan</span>
                                </a>
                            @endif

                            <div class="border-t border-gray-100 my-1"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2 font-bold text-red-600 hover:bg-red-50 transition cursor-pointer">
                                    <span class="material-symbols-outlined text-base">logout</span>
                                    <span>Keluar (Log Out)</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth

            </div>

            {{-- Tombol Mobile Menu Hamburger --}}
            <button
                type="button"
                @click="mobileMenuOpen = !mobileMenuOpen"
                class="lg:hidden p-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
                :aria-expanded="mobileMenuOpen"
                aria-label="Buka menu navigasi"
            >
                <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

        </div>

        {{-- Mobile Menu Drawer --}}
        <div
            x-show="mobileMenuOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="lg:hidden border-t border-gray-100 py-4 bg-white"
        >
            <div class="flex flex-col gap-1.5">

                <a
                    href="{{ route('katalog.index') }}"
                    class="px-3 py-2.5 rounded-lg text-sm font-semibold {{ (request()->routeIs('katalog.*') || request()->routeIs('home') || request()->is('/')) && !request()->routeIs('pelanggan.*') ? 'font-bold text-[#1a146b] bg-[#eff4ff]' : 'text-gray-700 hover:bg-gray-50' }}"
                >
                    Jelajahi Lapangan
                </a>

                <a
                    href="{{ route('jadwal.index') }}"
                    class="px-3 py-2.5 rounded-lg text-sm font-semibold {{ request()->routeIs('jadwal.*') ? 'font-bold text-[#1a146b] bg-[#eff4ff]' : 'text-gray-700 hover:bg-gray-50' }}"
                >
                    Cek Jadwal
                </a>

                @auth
                    @if (!in_array(Auth::user()->role, ['admin', 'kasir']))
                        <a
                            href="{{ route('pelanggan.booking.index') }}"
                            class="px-3 py-2.5 rounded-lg text-sm font-semibold {{ request()->routeIs('pelanggan.booking.*') || request()->routeIs('pelanggan.riwayat') ? 'font-bold text-[#1a146b] bg-[#eff4ff]' : 'text-gray-700 hover:bg-gray-50' }}"
                        >
                            Riwayat Booking
                        </a>

                        <a
                            href="{{ route('pelanggan.dashboard') }}"
                            class="px-3 py-2.5 rounded-lg text-sm font-semibold {{ request()->routeIs('pelanggan.dashboard') || request()->routeIs('profile.*') ? 'font-bold text-[#1a146b] bg-[#eff4ff]' : 'text-gray-700 hover:bg-gray-50' }}"
                        >
                            Profil Saya
                        </a>
                    @endif

                    <a
                        href="{{ route('home') }}#kontak"
                        class="px-3 py-2.5 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50"
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
                        href="{{ route('home') }}#kontak"
                        class="px-3 py-2.5 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50"
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
