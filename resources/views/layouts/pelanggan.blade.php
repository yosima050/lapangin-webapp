<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@hasSection('title')@yield('title') &bull; LapangIn @else LapangIn &bull; Platform Sewa Lapangan Olahraga @endif</title>
    <link rel="icon" type="image/png" href="{{ asset('images/02_Icon_App.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/02_Icon_App.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/02_Icon_App.png') }}">

    <!-- Google Fonts & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f8f9ff] text-[#0b1c30] min-h-screen flex flex-col font-sans antialiased">
    <div class="min-h-screen flex flex-col">
        {{-- Unified Pelanggan Navbar --}}
        @include('layouts.navigation-pelanggan')

        {{-- Main Content --}}
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-12 flex-1 w-full">
            @yield('content')
        </main>

        {{-- Unified Pelanggan Footer --}}
        @include('layouts.footer-pelanggan')
    </div>
</body>
</html>