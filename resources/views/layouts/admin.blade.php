<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>@yield('title', 'Admin Venue - LapangIn')</title>

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
        <div class="flex flex-col gap-4">
            <!-- Brand Logo -->
            <div class="px-3 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-[#1a146b] flex items-center justify-center text-[#acf847] shadow-sm">
                        <span class="material-symbols-outlined text-xl">stadium</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-lg font-headline font-extrabold tracking-tight text-[#1a146b] leading-tight">
                            Lapang<span class="text-[#416900]">In</span>
                        </span>
                        <span class="text-[10px] uppercase font-bold tracking-widest text-[#474651]">Admin Venue</span>
                    </div>
                </div>
            </div>

            <!-- Venue Switcher Card -->
            <div class="px-3 py-2 rounded-xl bg-[#e5eeff] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#416900]"></span>
                    <span class="text-xs font-semibold text-[#0b1c30]">Arena GOR Utama</span>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#474651]">Aktif</span>
            </div>

            <!-- Navigation Links -->
            <nav class="flex flex-col gap-1.5 mt-1">
                <!-- 1. Dashboard Overview -->
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-[#1a146b] text-white shadow-md' : 'text-[#474651] hover:bg-[#dce9ff] hover:text-[#0b1c30]' }}">
                    <span class="material-symbols-outlined text-[20px]">space_dashboard</span>
                    <span>Dashboard Overview</span>
                </a>

                <!-- 2. Kelola Lapangan & Tarif -->
                <a href="{{ route('admin.lapangan.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.lapangan.*') ? 'bg-[#1a146b] text-white shadow-md' : 'text-[#474651] hover:bg-[#dce9ff] hover:text-[#0b1c30]' }}">
                    <span class="material-symbols-outlined text-[20px]">sports_soccer</span>
                    <span>Kelola Lapangan &amp; Tarif</span>
                </a>

                <!-- 3. Custom Price Hari Libur -->
                <a href="{{ route('admin.custom-price') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.custom-price') ? 'bg-[#1a146b] text-white shadow-md' : 'text-[#474651] hover:bg-[#dce9ff] hover:text-[#0b1c30]' }}">
                    <span class="material-symbols-outlined text-[20px]">event_available</span>
                    <span>Custom Price Hari Libur</span>
                </a>

                <!-- 4. Kelola Pegawai / Kasir -->
                <a href="{{ route('admin.pegawai.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.pegawai.*') ? 'bg-[#1a146b] text-white shadow-md' : 'text-[#474651] hover:bg-[#dce9ff] hover:text-[#0b1c30]' }}">
                    <span class="material-symbols-outlined text-[20px]">badge</span>
                    <span>Kelola Pegawai / Kasir</span>
                </a>

                <!-- 5. Approval Reschedule (Kasir POS) -->
                <a href="{{ route('kasir.reschedule') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all text-[#474651] hover:bg-[#dce9ff] hover:text-[#0b1c30]">
                    <span class="material-symbols-outlined text-[20px]">pending_actions</span>
                    <span>Approval Reschedule (POS)</span>
                </a>

                <!-- 6. Laporan & Analitik -->
                <a href="#" onclick="alert('Modul Laporan & Analitik dalam persiapan rilis.'); return false;"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all text-[#474651] hover:bg-[#dce9ff] hover:text-[#0b1c30]">
                    <span class="material-symbols-outlined text-[20px]">monitoring</span>
                    <span>Laporan &amp; Analitik</span>
                </a>

                <!-- 7. Pengaturan Venue -->
                <a href="#" onclick="alert('Pengaturan Venue Arena GOR Utama'); return false;"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all text-[#474651] hover:bg-[#dce9ff] hover:text-[#0b1c30]">
                    <span class="material-symbols-outlined text-[20px]">settings</span>
                    <span>Pengaturan Venue</span>
                </a>
            </nav>
        </div>

        <!-- Venue Owner Profile Card & Logout -->
        <div class="p-3.5 rounded-2xl bg-white shadow-sm border border-[#e5eeff] flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-[#1a146b] flex items-center justify-center text-white text-xs font-bold shadow-xs">
                    {{ strtoupper(substr(Auth::user()->nama ?? 'B', 0, 1)) }}
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-[#0b1c30] leading-tight">{{ Auth::user()->nama ?? 'Bambang S.' }}</span>
                    <span class="text-[10px] text-[#474651]">Venue Owner</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-[#474651] hover:text-[#ba1a1a] transition-colors p-1" title="Logout">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Workspace with pl-72 for Sidebar offset -->
    <div class="pl-72 flex-1 flex flex-col min-h-screen">

        <!-- Topbar Header -->
        <header class="fixed top-0 left-72 right-0 h-16 bg-[#f8f9ff]/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] z-40 flex items-center justify-between px-8 border-b border-[#e5eeff]">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-headline font-bold text-[#0b1c30]">Arena GOR Utama Dashboard</span>
                    <span class="text-[11px] font-semibold uppercase px-2 py-0.5 rounded bg-[#e2dfff] text-[#100563]">
                        Owner Admin
                    </span>
                </div>
                <div class="h-4 w-[1px] bg-[#c8c5d3] hidden sm:block"></div>
                <div class="hidden sm:flex items-center gap-2 text-xs text-[#474651]">
                    <span class="material-symbols-outlined text-[16px]">schedule</span>
                    <span>Senin, 14 Juli 2025 • 10:45 WIB</span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 px-3 py-1 rounded-full bg-[#eff4ff] border border-[#e5eeff] text-xs font-medium text-[#474651]">
                    <span class="w-2 h-2 rounded-full bg-[#416900]"></span>
                    <span>Server Normal</span>
                </div>
                <div class="flex items-center gap-1 text-[#474651]">
                    <button type="button" class="w-9 h-9 rounded-xl flex items-center justify-center hover:bg-[#dce9ff] transition-colors">
                        <span class="material-symbols-outlined text-[20px]">notifications</span>
                    </button>
                    <button type="button" class="w-9 h-9 rounded-xl flex items-center justify-center hover:bg-[#dce9ff] transition-colors">
                        <span class="material-symbols-outlined text-[20px]">help_outline</span>
                    </button>
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