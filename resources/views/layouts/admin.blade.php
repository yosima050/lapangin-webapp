<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Admin - Lapangin')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 text-gray-900 min-h-screen">

    <div class="min-h-screen flex">

        {{-- Sidebar --}}
        <aside class="w-64 bg-indigo-950 text-white fixed inset-y-0 left-0 z-40">

            {{-- Logo --}}
            <div class="h-20 flex items-center px-6 border-b border-indigo-800">
                <span class="text-2xl font-extrabold">
                    Lapangin
                </span>
            </div>

            {{-- Menu --}}
            <nav class="p-4 space-y-2">

                <a href="#"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl bg-indigo-800 font-semibold">
                    <span>📊</span>
                    <span>Dashboard</span>
                </a>

                <a href="#"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-indigo-100 hover:bg-indigo-800 transition">
                    <span>🏟️</span>
                    <span>Data Lapangan</span>
                </a>

                <a href="#"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-indigo-100 hover:bg-indigo-800 transition">
                    <span>💰</span>
                    <span>Manajemen Harga</span>
                </a>

                <a href="#"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-indigo-100 hover:bg-indigo-800 transition">
                    <span>👥</span>
                    <span>Manajemen Pegawai</span>
                </a>

                <a href="#"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-indigo-100 hover:bg-indigo-800 transition">
                    <span>📈</span>
                    <span>Laporan</span>
                </a>

                <a href="#"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-indigo-100 hover:bg-indigo-800 transition">
                    <span>👤</span>
                    <span>Profil</span>
                </a>

            </nav>

        </aside>

        {{-- Main Content --}}
        <div class="flex-1 ml-64">

            {{-- Topbar --}}
            <header class="h-20 bg-white border-b border-gray-200 flex items-center justify-between px-8 sticky top-0 z-30">

                <div>
                    <h1 class="text-xl font-bold text-gray-900">
                        @yield('page-title', 'Dashboard Admin')
                    </h1>

                    <p class="text-sm text-gray-500">
                        Kelola sistem Lapangin
                    </p>
                </div>

                {{-- Profile --}}
                <div class="flex items-center gap-3">

                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-800">
                            Admin
                        </p>

                        <p class="text-xs text-gray-500">
                            Administrator
                        </p>
                    </div>

                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-indigo-900">
                        A
                    </div>

                </div>

            </header>

            {{-- Content --}}
            <main class="p-8">

                @yield('content')

            </main>

        </div>

    </div>

</body>
</html>