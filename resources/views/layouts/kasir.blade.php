<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>@yield('title', 'Kasir POS - LapangIn')</title>

    <!-- Google Fonts & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    <style>
        @layer base {
            html, body {
                margin: 0;
                padding: 0;
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
        }
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-thumb {
            background: #c8c5d3;
            border-radius: 9999px;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f8f9ff] text-[#0b1c30] min-h-screen antialiased flex">

    <!-- Fixed Left Sidebar (w-72) -->
    <aside class="fixed left-0 top-0 h-full w-72 bg-[#eff4ff] z-50 flex flex-col justify-between py-6 px-4 shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-r border-[#e5eeff]">
        <div class="flex flex-col gap-5">
            <!-- Brand Logo -->
            <div class="px-3 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-[#1a146b] flex items-center justify-center text-[#acf847] shadow-sm">
                    <span class="material-symbols-outlined text-xl">sports_soccer</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xl font-headline font-extrabold text-[#1a146b] tracking-tight leading-tight">
                        Lapang<span class="text-[#416900]">In</span>
                    </span>
                    <span class="text-[10px] font-bold text-[#474651] uppercase tracking-wider">
                        Operator Venue
                    </span>
                </div>
            </div>

            <!-- Terminal Connection Chip -->
            <div class="px-3 py-2 rounded-xl bg-[#e5eeff] flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#acf847] animate-pulse"></span>
                <span class="text-xs font-semibold text-[#0b1c30]">Kasir Terhubung</span>
            </div>

            <!-- Navigation Links -->
            <nav class="flex flex-col gap-1.5 mt-1">
                <!-- 1. Check-in & Validasi -->
                <a href="{{ route('kasir.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all text-sm font-semibold {{ request()->routeIs('kasir.index') ? 'bg-[#1a146b] text-white shadow-md' : 'text-[#474651] hover:bg-[#dce9ff] hover:text-[#0b1c30]' }}">
                    <span class="material-symbols-outlined text-[20px]">verified_user</span>
                    <span>Check-in &amp; Validasi</span>
                </a>

                <!-- 2. Jadwal & Walk-in -->
                <a href="{{ route('kasir.jadwal') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all text-sm font-semibold {{ request()->routeIs('kasir.jadwal') ? 'bg-[#1a146b] text-white shadow-md' : 'text-[#474651] hover:bg-[#dce9ff] hover:text-[#0b1c30]' }}">
                    <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                    <span>Jadwal &amp; Walk-in</span>
                </a>

                <!-- 3. Transaksi & Tutup Shift -->
                <a href="{{ route('kasir.transaksi') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all text-sm font-semibold {{ request()->routeIs('kasir.transaksi') ? 'bg-[#1a146b] text-white shadow-md' : 'text-[#474651] hover:bg-[#dce9ff] hover:text-[#0b1c30]' }}">
                    <span class="material-symbols-outlined text-[20px]">point_of_sale</span>
                    <span>Transaksi &amp; Tutup Shift</span>
                </a>

                <!-- 4. Approval Reschedule (with 3 pending badge) -->
                <a href="{{ route('kasir.reschedule') }}"
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all text-sm font-semibold {{ request()->routeIs('kasir.reschedule') ? 'bg-[#1a146b] text-white shadow-md' : 'text-[#474651] hover:bg-[#dce9ff] hover:text-[#0b1c30]' }}">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[20px]">pending_actions</span>
                        <span>Approval Reschedule</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-extrabold bg-[#acf847] text-[#102000] shadow-xs">
                        3 pending
                    </span>
                </a>
            </nav>
        </div>

        <!-- Sidebar Bottom: System Status Card & Logout -->
        <div class="flex flex-col gap-3">
            <div class="p-4 rounded-xl bg-white shadow-sm border border-[#e5eeff] flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#474651]">STATUS SISTEM</span>
                    <span class="px-2 py-0.5 rounded-full bg-[#acf847] text-[#102000] text-[10px] font-extrabold">Online</span>
                </div>
                <div class="flex items-center gap-2 text-[#0b1c30]">
                    <span class="material-symbols-outlined text-base text-[#416900]">database</span>
                    <span class="text-xs font-semibold">POS Kasir LapangIn v1.0</span>
                </div>
                <span class="text-[10px] text-[#777682]">Sinkronisasi Basis Data Lokal</span>
            </div>

            <!-- Logout Link -->
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-[#ba1a1a] hover:bg-[#ffdad6] transition-colors">
                    <span class="material-symbols-outlined text-base">logout</span>
                    <span>Keluar Sesi Kasir</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Container with pl-72 for Sidebar offset -->
    <div class="pl-72 flex-1 flex flex-col min-h-screen">

        <!-- Fixed Topbar Header -->
        <header class="fixed top-0 left-72 right-0 h-16 bg-[#f8f9ff]/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] z-40 flex items-center justify-between px-8 border-b border-[#e5eeff]">
            <!-- Terminal & Shift Status Chips -->
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#eff4ff] border border-[#e5eeff]">
                    <span class="material-symbols-outlined text-[#1a146b] text-base">desktop_windows</span>
                    <span class="text-xs font-semibold text-[#0b1c30]">Terminal Meja Resepsionis #01</span>
                </div>
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#eff4ff] border border-[#e5eeff]">
                    <span class="material-symbols-outlined text-[#474651] text-base">schedule</span>
                    <span class="text-xs font-medium text-[#474651]">Shift 1 Pagi (07:00 - 15:00)</span>
                </div>
            </div>

            <!-- Cashier Identity & Badge -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#acf847] text-[#102000] shadow-sm font-bold text-xs">
                    <span class="material-symbols-outlined text-base">badge</span>
                    <span>{{ Auth::user()->nama ?? 'Bima Kasir' }}</span>
                </div>

                <div class="w-8 h-8 rounded-full bg-[#1a146b] flex items-center justify-center text-white text-xs font-bold shadow-sm">
                    {{ strtoupper(substr(Auth::user()->nama ?? 'B', 0, 1)) }}
                </div>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="w-full pt-16 flex-1 bg-[#f8f9ff]">
            @yield('content')
        </main>

    </div>

</body>
</html>