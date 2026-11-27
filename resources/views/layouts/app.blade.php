<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@hasSection('title')@yield('title') &bull; LapangIn @else LapangIn &bull; Platform Sewa Lapangan Olahraga @endif</title>
    <meta name="description" content="Platform pemesanan dan jadwal lapangan olahraga modern berstandar kompetisi. Booking lapangan futsal, badminton, basket, dan mini soccer cepat dan mudah dengan DP 50% di LapangIn.">
    <link rel="icon" type="image/png" href="{{ asset('images/02_Icon_App.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/02_Icon_App.png') }}">

    @stack('head')

    <!-- Google Fonts: Plus Jakarta Sans (Non-blocking dengan display=swap) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'" />
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" />
    </noscript>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f8f9ff] text-[#0b1c30] min-h-screen flex flex-col font-sans antialiased">

    {{-- Unified Pelanggan Navbar --}}
    @include('layouts.navigation-pelanggan')

    {{-- Main Content with Soft Cross-fade Container --}}
    <main id="app-main-content" class="flex-1 pt-20 transition-opacity duration-150 ease-out">
        @yield('content')
    </main>

    {{-- Unified Pelanggan Footer --}}
    @include('layouts.footer-pelanggan')

    {{-- Global GitHub-style Page Progress Bar & Smart Skeleton --}}
    @include('layouts.partials.page-skeleton')
</body>
</html>
