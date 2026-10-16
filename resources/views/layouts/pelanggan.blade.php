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
    <div class="min-h-screen flex flex-col justify-between">
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

        <footer class="bg-white border-t border-gray-100 py-6 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-gray-500">
                &copy; {{ date('Y') }} {{ config('app.name', 'LapangIn') }}. Seluruh hak cipta dilindungi undang-undang.
            </div>
        </footer>
    </div>
</body>
</html>
