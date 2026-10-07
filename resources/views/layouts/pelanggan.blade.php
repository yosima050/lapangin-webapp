<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Pelanggan - Lapangin')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 text-gray-900 min-h-screen">
    <div class="min-h-screen">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-6 py-5 flex items-center justify-between">
                <a href="{{ route('pelanggan.dashboard') }}" class="text-2xl font-extrabold text-indigo-950">
                    Lapangin
                </a>

                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">{{ Auth::user()->nama }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-semibold text-indigo-900 hover:text-indigo-700">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-6 py-10">
            @yield('content')
        </main>
    </div>
</body>
</html>
