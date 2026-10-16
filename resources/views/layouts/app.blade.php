<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', config('app.name', 'LapangIn'))</title>

    <!-- Google Fonts & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f8f9ff] text-[#0b1c30] min-h-screen flex flex-col font-sans antialiased">

    {{-- Navbar --}}
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-xs">
        <nav
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
            x-data="{ open: false }"
        >
            <div class="h-20 flex items-center justify-between">

                {{-- Logo --}}
                <a
                    href="{{ route('home') }}"
                    class="flex items-center shrink-0 hover:opacity-90 transition"
                    aria-label="LapangIn"
                >
                    <x-application-logo />
                </a>

                {{-- Desktop Navigation --}}
                <div class="hidden lg:flex items-center gap-8 ml-10">

                    <a
                        href="{{ route('katalog.index') }}"
                        class="py-2 text-sm font-bold transition {{ request()->routeIs('katalog.*') || request()->routeIs('home') ? 'text-[#1a146b] border-b-2 border-[#acf847]' : 'text-gray-600 hover:text-[#1a146b]' }}"
                    >
                        Jelajahi Lapangan
                    </a>

                    <a
                        href="{{ route('jadwal.index') }}"
                        class="py-2 text-sm font-semibold text-gray-600 hover:text-[#1a146b] transition-colors"
                    >
                        Cek Jadwal
                    </a>

                    @auth
                        @if (!in_array(Auth::user()->role, ['admin', 'kasir']))
                            <a
                                href="{{ route('pelanggan.booking.index') }}"
                                class="py-2 text-sm font-semibold transition-colors {{ request()->routeIs('pelanggan.booking.*') || request()->routeIs('pelanggan.riwayat') ? 'text-[#1a146b] font-bold' : 'text-gray-600 hover:text-[#1a146b]' }}"
                            >
                                Riwayat Booking
                            </a>
                        @endif
                    @endauth

                    <a
                        href="{{ url('/#katalog') }}"
                        class="py-2 text-sm font-semibold text-gray-600 hover:text-[#1a146b] transition-colors"
                    >
                        Pusat Bantuan
                    </a>

                </div>

                {{-- Right Navigation --}}
                <div class="hidden lg:flex items-center gap-4 ml-auto">

                    {{-- Location Badge --}}
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-50 border border-gray-100 rounded-xl text-xs font-semibold text-gray-700">
                        <span class="text-[#416900]">📍</span>
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
                                class="flex items-center gap-2.5 p-1 rounded-xl hover:bg-gray-50 transition"
                            >
                                <span class="w-9 h-9 rounded-full bg-[#1a146b] border-2 border-[#acf847] flex items-center justify-center text-sm font-bold text-white shadow-xs">
                                    {{ strtoupper(substr(Auth::user()->nama, 0, 1)) }}
                                </span>

                                <span class="text-xs font-bold text-gray-800 max-w-[120px] truncate">
                                    {{ Auth::user()->nama }}
                                </span>

                                <span class="material-symbols-outlined text-gray-400 text-sm">expand_more</span>
                            </button>

                            <div
                                x-show="profileOpen"
                                x-transition
                                class="absolute right-0 mt-2 w-56 rounded-2xl bg-white border border-gray-100 shadow-xl py-2 z-50 text-xs"
                                style="display: none;"
                            >
                                <div class="px-4 py-2.5 border-b border-gray-100">
                                    <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Akun Masuk</p>
                                    <p class="text-xs font-bold text-[#0b1c30] truncate">{{ Auth::user()->nama }}</p>
                                    <p class="text-[11px] text-gray-500 truncate">{{ Auth::user()->email }}</p>
                                </div>

                                @if (Auth::user()->role === 'admin')
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#1a146b]">
                                        <span class="material-symbols-outlined text-base">space_dashboard</span>
                                        <span>Dashboard Admin</span>
                                    </a>
                                @elseif (Auth::user()->role === 'kasir')
                                    <a href="{{ route('kasir.index') }}" class="flex items-center gap-2.5 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#1a146b]">
                                        <span class="material-symbols-outlined text-base">point_of_sale</span>
                                        <span>Portal Kasir POS</span>
                                    </a>
                                @else
                                    <a href="{{ route('pelanggan.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#1a146b]">
                                        <span class="material-symbols-outlined text-base">account_circle</span>
                                        <span>Dashboard Pelanggan</span>
                                    </a>
                                    <a href="{{ route('pelanggan.booking.index') }}" class="flex items-center gap-2.5 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#1a146b]">
                                        <span class="material-symbols-outlined text-base">receipt_long</span>
                                        <span>Riwayat Booking</span>
                                    </a>
                                @endif

                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#1a146b]">
                                    <span class="material-symbols-outlined text-base">manage_accounts</span>
                                    <span>Pengaturan Akun</span>
                                </a>

                                <div class="border-t border-gray-100 my-1"></div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2 font-bold text-red-600 hover:bg-red-50 transition">
                                        <span class="material-symbols-outlined text-base">logout</span>
                                        <span>Keluar (Log Out)</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endauth

                </div>

                {{-- Mobile Menu Button --}}
                <button
                    type="button"
                    @click="open = !open"
                    class="lg:hidden p-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                    :aria-expanded="open"
                    aria-label="Buka menu"
                >
                    <span
                        x-show="!open"
                        class="text-2xl"
                    >
                        ☰
                    </span>

                    <span
                        x-show="open"
                        class="text-2xl"
                    >
                        ✕
                    </span>
                </button>

            </div>

            {{-- Mobile Menu --}}
            <div
                x-show="open"
                x-transition
                class="lg:hidden border-t border-gray-100 py-4"
                style="display: none;"
            >
                <div class="flex flex-col gap-1.5">

                    <a
                        href="{{ route('katalog.index') }}"
                        class="px-3 py-2.5 rounded-lg text-sm font-bold text-[#1a146b] bg-[#eff4ff]"
                    >
                        Jelajahi Lapangan
                    </a>

                    <a
                        href="{{ route('jadwal.index') }}"
                        class="px-3 py-2.5 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Cek Jadwal
                    </a>

                    @auth
                        @if (Auth::user()->role === 'admin')
                            <a
                                href="{{ route('admin.dashboard') }}"
                                class="px-3 py-2.5 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Dashboard Admin
                            </a>
                        @elseif (Auth::user()->role === 'kasir')
                            <a
                                href="{{ route('kasir.index') }}"
                                class="px-3 py-2.5 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Portal Kasir POS
                            </a>
                        @else
                            <a
                                href="{{ route('pelanggan.booking.index') }}"
                                class="px-3 py-2.5 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Riwayat Booking
                            </a>
                            <a
                                href="{{ route('pelanggan.dashboard') }}"
                                class="px-3 py-2.5 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Dashboard Pelanggan
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="pt-2">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2.5 rounded-lg text-sm font-bold text-red-600 hover:bg-red-50">
                                Keluar ({{ Auth::user()->nama }})
                            </button>
                        </form>
                    @endauth

                    @guest
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


    {{-- Main Content --}}
    <main class="flex-1 pt-20">
        @yield('content')
    </main>


    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-100 mt-auto">

        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10">

            <div class="flex flex-col md:flex-row items-center justify-between gap-6">

                {{-- Footer Brand --}}
                <div class="flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
                    <x-application-logo />

                    <span class="text-sm text-gray-500">
                        &copy; {{ date('Y') }} {{ config('app.name', 'LapangIn') }}.
                        Seluruh hak cipta dilindungi undang-undang.
                    </span>

                </div>

                {{-- Footer Links --}}
                <div class="flex flex-wrap justify-center items-center gap-6 text-sm font-semibold text-gray-500">

                    <a
                        href="{{ route('katalog.index') }}"
                        class="hover:text-gray-900 transition-colors"
                    >
                        Katalog Lapangan
                    </a>

                    <a
                        href="{{ route('jadwal.index') }}"
                        class="hover:text-gray-900 transition-colors"
                    >
                        Jadwal Arena
                    </a>

                    <a
                        href="{{ route('home') }}#syarat"
                        class="hover:text-gray-900 transition-colors"
                    >
                        Syarat &amp; Ketentuan
                    </a>

                    <a
                        href="{{ route('home') }}#kontak"
                        class="hover:text-gray-900 transition-colors"
                    >
                        Hubungi CS
                    </a>

                </div>

            </div>

        </div>

    </footer>

</body>
</html>
