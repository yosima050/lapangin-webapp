<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', config('app.name', 'Lapangin'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">

    {{-- Navbar --}}
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-sm">
        <nav
            class="max-w-7xl mx-auto px-6 lg:px-8"
            x-data="{ open: false }"
        >
            <div class="h-20 flex items-center justify-between">

                {{-- Logo --}}
                <a
                    href="/"
                    class="flex items-center shrink-0"
                    aria-label="Lapangin"
                >
                    <span class="text-2xl font-extrabold tracking-tight text-indigo-900">
                        Lapangin
                    </span>
                </a>

                {{-- Desktop Navigation --}}
                <div class="hidden lg:flex items-center gap-8 ml-10">

                    <a
                        href="#"
                        class="py-2 text-sm font-bold text-indigo-900 border-b-2 border-lime-500"
                    >
                        Jelajahi Lapangan
                    </a>

                    <a
                        href="#"
                        class="py-2 text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors"
                    >
                        Cek Jadwal
                    </a>

                    <a
                        href="#"
                        class="py-2 text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors"
                    >
                        Riwayat Booking
                    </a>

                    <a
                        href="#"
                        class="py-2 text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors"
                    >
                        Pusat Bantuan
                    </a>

                </div>

                {{-- Right Navigation --}}
                <div class="hidden lg:flex items-center gap-4 ml-auto">

                    {{-- Location --}}
                    <button
                        type="button"
                        class="flex items-center gap-2 px-4 py-2 bg-gray-50 rounded-xl text-sm font-semibold text-gray-800 hover:bg-gray-100 transition-colors"
                    >
                        <span class="text-lime-600">📍</span>
                        <span>Malang</span>
                        <span class="text-gray-400">⌄</span>
                    </button>

                    {{-- Notification --}}
                    <button
                        type="button"
                        class="relative p-2.5 text-gray-500 hover:text-gray-900 hover:bg-gray-50 rounded-xl transition-colors"
                        aria-label="Notifikasi"
                    >
                        <span class="text-xl">🔔</span>

                        <span class="absolute top-2 right-2 w-2 h-2 bg-lime-500 rounded-full"></span>
                    </button>

                    {{-- Profile --}}
                    <button
                        type="button"
                        class="flex items-center gap-2 pl-2"
                    >
                        <span class="w-9 h-9 rounded-full bg-indigo-100 border-2 border-lime-300 flex items-center justify-center text-sm font-bold text-indigo-900">
                            P
                        </span>

                        <span class="text-sm font-semibold text-gray-800">
                            Pengguna
                        </span>
                    </button>

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
            >
                <div class="flex flex-col gap-1">

                    <a
                        href="#"
                        class="px-3 py-3 rounded-lg text-sm font-bold text-indigo-900 bg-indigo-50"
                    >
                        Jelajahi Lapangan
                    </a>

                    <a
                        href="#"
                        class="px-3 py-3 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Cek Jadwal
                    </a>

                    <a
                        href="#"
                        class="px-3 py-3 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Riwayat Booking
                    </a>

                    <a
                        href="#"
                        class="px-3 py-3 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Pusat Bantuan
                    </a>

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

                    <span class="text-xl font-extrabold tracking-tight text-indigo-900">
                        Lapangin
                    </span>

                    <span class="text-sm text-gray-500">
                        © {{ date('Y') }} Lapangin Indonesia.
                        Seluruh hak cipta dilindungi undang-undang.
                    </span>

                </div>

                {{-- Footer Links --}}
                <div class="flex flex-wrap justify-center items-center gap-6 text-sm font-semibold text-gray-500">

                    <a
                        href="#"
                        class="hover:text-gray-900 transition-colors"
                    >
                        Bantuan
                    </a>

                    <a
                        href="#"
                        class="hover:text-gray-900 transition-colors"
                    >
                        Kebijakan
                    </a>

                    <a
                        href="#"
                        class="hover:text-gray-900 transition-colors"
                    >
                        Syarat & Ketentuan
                    </a>

                    <a
                        href="#"
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