<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Pelanggan - ' . config('app.name', 'LapangIn'))</title>

    <!-- Google Fonts & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f8f9ff] text-[#0b1c30] min-h-screen flex flex-col font-sans antialiased">
    <div class="min-h-screen flex flex-col">
        <header class="bg-white/90 backdrop-blur-md border-b border-[#e5eeff] sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <div class="flex items-center gap-8">
                    <a href="{{ route('pelanggan.dashboard') }}" class="flex items-center shrink-0">
                        <x-application-logo />
                    </a>

                    <nav class="hidden md:flex items-center gap-6 text-xs font-bold">
                        <a href="{{ route('pelanggan.dashboard') }}"
                           class="py-1 transition {{ request()->routeIs('pelanggan.dashboard') ? 'text-[#1a146b] border-b-2 border-[#acf847]' : 'text-gray-600 hover:text-[#1a146b]' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('katalog.index') }}"
                           class="py-1 text-gray-600 hover:text-[#1a146b] transition">
                            Katalog Lapangan
                        </a>
                        <a href="{{ route('pelanggan.booking.index') }}"
                           class="py-1 transition {{ request()->routeIs('pelanggan.booking.*') || request()->routeIs('pelanggan.riwayat') ? 'text-[#1a146b] border-b-2 border-[#acf847]' : 'text-gray-600 hover:text-[#1a146b]' }}">
                            Riwayat Booking
                        </a>
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-[#1a146b] border-2 border-[#acf847] flex items-center justify-center text-xs font-bold text-white shadow-xs">
                            {{ strtoupper(substr(Auth::user()->nama ?? 'P', 0, 1)) }}
                        </div>
                        <span class="text-xs font-bold text-[#0b1c30] hidden sm:inline max-w-[150px] truncate">
                            {{ Auth::user()->nama }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 text-xs font-bold transition"
                                title="Keluar dari akun">
                            <span class="material-symbols-outlined text-base">logout</span>
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full">
            @yield('content')
        </main>

        <footer class="mt-16 border-t border-gray-200 bg-white pt-12 pb-8 text-xs text-gray-600">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-12">
                    
                    {{-- Kolom 1: Branding & Deskripsi --}}
                    <div class="lg:col-span-4">
                        <div class="flex items-center gap-2">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#1a146b] text-white font-bold">
                                ⚽
                            </div>
                            <div>
                                <span class="text-base font-extrabold text-[#1a146b]">LapangIn</span>
                                <p class="text-[9px] font-bold uppercase tracking-wider text-gray-400">Booking Lapangan Olahraga</p>
                            </div>
                        </div>
                        <p class="mt-4 max-w-xs text-xs leading-relaxed text-gray-500">
                            Platform pemesanan dan jadwal lapangan olahraga modern berstandar turnamen. Pesan lapangan padel, badminton, futsal, dan basket tanpa repot antre.
                        </p>
                    </div>

                    {{-- Kolom 2: Navigasi --}}
                    <div class="lg:col-span-2">
                        <h4 class="font-extrabold uppercase tracking-wider text-gray-900">Navigasi</h4>
                        <ul class="mt-3 space-y-2 text-gray-500">
                            <li><a href="{{ route('katalog.index') }}" class="hover:text-[#1a146b] transition">Katalog Lapangan</a></li>
                            <li><a href="{{ route('pelanggan.booking.index') }}" class="hover:text-[#1a146b] transition">Jadwal &amp; Reservasi</a></li>
                            <li><a href="{{ route('pelanggan.booking.index') }}" class="hover:text-[#1a146b] transition">Riwayat Transaksi</a></li>
                            <li><a href="#" class="hover:text-[#1a146b] transition">Pengaturan Akun</a></li>
                        </ul>
                    </div>

                    {{-- Kolom 3: Bantuan & Kebijakan --}}
                    <div class="lg:col-span-3">
                        <h4 class="font-extrabold uppercase tracking-wider text-gray-900">Bantuan &amp; Kebijakan</h4>
                        <ul class="mt-3 space-y-2 text-gray-500">
                            <li><a href="#" class="hover:text-[#1a146b] transition">Pusat Bantuan / FAQ</a></li>
                            <li><a href="#" class="hover:text-[#1a146b] transition">Kebijakan Reschedule</a></li>
                            <li><a href="#" class="hover:text-[#1a146b] transition">Ketentuan Layanan</a></li>
                            <li><a href="#" class="hover:text-[#1a146b] transition">Kebijakan Privasi</a></li>
                        </ul>
                    </div>

                    {{-- Kolom 4: Kerjasama Venue --}}
                    <div class="lg:col-span-3">
                        <h4 class="font-extrabold uppercase tracking-wider text-gray-900">Kerjasama Venue</h4>
                        <p class="mt-3 leading-relaxed text-gray-500">
                            Punya gelanggang atau lapangan olahraga? Kelola jadwal dan tiket reservasi otomatis dengan ekosistem LapangIn Partner.
                        </p>
                        <a href="#" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#acf847] px-4 py-2.5 font-bold text-[#1a146b] shadow-xs hover:bg-[#9be338] transition">
                            Masuk Portal Venue Owner &rarr;
                        </a>
                    </div>

                </div>

                {{-- Hak Cipta --}}
                <div class="mt-12 border-t border-gray-100 pt-6 text-gray-400">
                    <p>&copy; {{ date('Y') }} {{ config('app.name', 'LapangIn') }} Digital Systems. Hak cipta dilindungi.</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>