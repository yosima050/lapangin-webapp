@extends('layouts.app')

@section('title', 'Detail Lapangan')

@section('content')

@php
    $venues = [
        1 => [
            'name' => 'Viva Futsal Arena Malang',
            'category' => 'Futsal',
            'location' => '1.2 km • Lowokwaru, Malang',
            'description' => 'Lapangan futsal dengan fasilitas lengkap dan nyaman untuk bermain bersama teman maupun komunitas.',
            'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBYT6BJ2opGzVfSpOXFNJBColsgCn0dOdKuGwNnMaqIQI4Zu207zVbE9pz0oe8EdVT1iZDo0jVoO7XZfekr5KtsMjMfa-g77KaVmyyW3FXtCoihTzRNzLmTe9h06mLUxnNX3gvp4XU8zhkbOjUdDXPJhEe6B-ACA_kJyla33RS_WDyLW2Qpuat12TgCAxzxX8hTBt6x4hrAPIWcs_Ck0Y26QgHcjjtgLOFMy2padr3y3GJuNth1NTkI',
        ],

        2 => [
            'name' => 'Badminton Smash Arena Malang',
            'category' => 'Badminton',
            'location' => '2.8 km • Dinoyo, Malang',
            'description' => 'Arena badminton yang nyaman dengan lapangan berkualitas untuk latihan dan bermain bersama.',
            'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDikc4qmwj2RFHcRdd5Wd6vb-lxqvaqpF-m__-yzFxHAZyV5ORMyLk91T1Lk2xW9yW8LavilOCqjtFfgr9We-F-IhD4azlA4Lrdzxb_hJ2oiVHJ0p6hYDcZD6QeqnAIq6ynE1lM1N2spuKr3PBljlj72hFTvowYTc6lLc61ofdVHV3TO5q3dbihU17eKhkl8R5156oFKXH5FGFMoP1a-XRJV824TP2pHyG99EC1nbduJUl73M3IMJtn',
        ],

        3 => [
            'name' => 'Garuda Mini Soccer Stadium Malang',
            'category' => 'Mini Soccer',
            'location' => '3.4 km • Soekarno Hatta, Malang',
            'description' => 'Lapangan mini soccer yang luas dan cocok untuk pertandingan bersama tim maupun komunitas.',
            'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBxCzMVeE8pB1abc0ClRl4U_T_8LqlY-ox6AsoAs1vTMxgu_b8jS2Ztnch84tom1o8vC002uLxGGyP6Oky84FZoMcBek-wex4mcteFV2E-szim6Ee0FstDyweDy8RG5RjdavTY2ZlS-xBwaInLsXKS9liYUDm67qJXsf2KxWax5wpM0PT_8XnahpuT_rle2LQuMWVDHhhlWAMqfV1gHLHvCt3D2WbEzsqsaenRoWpecDb50AMkMow92',
        ],

        4 => [
            'name' => 'Supreme Futsal & Padel Hub Malang',
            'category' => 'Futsal',
            'location' => '4.1 km • Blimbing, Malang',
            'description' => 'Venue olahraga dengan fasilitas futsal dan area olahraga yang nyaman untuk berbagai aktivitas.',
            'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCEry_sw5rS_9j0unvjYYE9IUOxNaPdbQofcsXezolL9AhXfknzETIvloJumdlXJqiN9P3209ZDO5havQP8t6WL9aIy7Wssi8hVyHY_ahhGt11p6GwGCWU4slxtHHc5YtpfV8wataiTc34fhhv0ZtaEOjm6j0vU-xDNrLBwjvwZSqivHaF9zum1u6GjiTgeRKBdcALrIq0NZ7toh0MqhlIEbUSxB214jdmXl92xn3zLoIR-YLvB21g3',
        ],
    ];

    $venue = $venues[$id] ?? $venues[1];
@endphp

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

    {{-- Tombol Kembali --}}
    <a href="{{ url('/') }}"
       class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-indigo-700">
        ← Kembali ke Katalog
    </a>

    {{-- Informasi Lapangan --}}
    <div class="grid gap-8 lg:grid-cols-2">

        {{-- Gambar Lapangan --}}
        <div class="overflow-hidden rounded-3xl bg-gray-100">
            <img
                src="{{ $venue['image'] }}"
                alt="{{ $venue['name'] }}"
                class="h-80 w-full object-cover sm:h-96"
            >
        </div>

        {{-- Detail --}}
        <div class="flex flex-col justify-center">

            <span class="mb-3 w-fit rounded-full bg-indigo-100 px-3 py-1 text-sm font-semibold text-indigo-700">
                {{ $venue['category'] }}
            </span>

            <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                {{ $venue['name'] }}
            </h1>

            <p class="mt-3 text-gray-600">
                📍 {{ $venue['location'] }}
            </p>

            <p class="mt-5 leading-7 text-gray-600">
                {{ $venue['description'] }}
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                <span class="rounded-lg bg-gray-100 px-3 py-2 text-sm text-gray-700">
                    Indoor
                </span>

                <span class="rounded-lg bg-gray-100 px-3 py-2 text-sm text-gray-700">
                    Parkir Mobil
                </span>

                <span class="rounded-lg bg-gray-100 px-3 py-2 text-sm text-gray-700">
                    Bisa DP 50%
                </span>
            </div>

        </div>
    </div>

    {{-- Pilih Tanggal --}}
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900">
            Pilih Tanggal
        </h2>

        <div class="mt-4 max-w-sm">
            <input
                type="date"
                class="w-full rounded-xl border border-gray-300 px-4 py-3 outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-200"
            >
        </div>
    </div>

    {{-- Pilih Jam --}}
    <div class="mt-10">

        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">
                Pilih Jam
            </h2>

            <div class="flex items-center gap-4 text-sm">

                {{-- Tersedia --}}
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-green-500"></span>
                    <span class="text-gray-600">Tersedia</span>
                </div>

                {{-- Penuh --}}
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-red-400"></span>
                    <span class="text-gray-600">Penuh</span>
                </div>

            </div>
        </div>

        {{-- Grid Jam --}}
        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">

            {{-- Tersedia --}}
            <button
                type="button"
                class="rounded-xl border-2 border-green-500 bg-green-50 px-4 py-4 text-center transition hover:bg-green-100"
            >
                <div class="font-bold text-green-700">
                    08:00 - 09:00
                </div>

                <div class="mt-1 text-xs text-green-600">
                    Tersedia
                </div>
            </button>

            {{-- Tersedia --}}
            <button
                type="button"
                class="rounded-xl border-2 border-green-500 bg-green-50 px-4 py-4 text-center transition hover:bg-green-100"
            >
                <div class="font-bold text-green-700">
                    09:00 - 10:00
                </div>

                <div class="mt-1 text-xs text-green-600">
                    Tersedia
                </div>
            </button>

            {{-- Penuh --}}
            <button
                type="button"
                disabled
                class="cursor-not-allowed rounded-xl border-2 border-gray-300 bg-gray-100 px-4 py-4 text-center opacity-70"
            >
                <div class="font-bold text-gray-500">
                    10:00 - 11:00
                </div>

                <div class="mt-1 text-xs text-red-500">
                    Penuh
                </div>
            </button>

            {{-- Tersedia --}}
            <button
                type="button"
                class="rounded-xl border-2 border-green-500 bg-green-50 px-4 py-4 text-center transition hover:bg-green-100"
            >
                <div class="font-bold text-green-700">
                    11:00 - 12:00
                </div>

                <div class="mt-1 text-xs text-green-600">
                    Tersedia
                </div>
            </button>

            {{-- Penuh --}}
            <button
                type="button"
                disabled
                class="cursor-not-allowed rounded-xl border-2 border-gray-300 bg-gray-100 px-4 py-4 text-center opacity-70"
            >
                <div class="font-bold text-gray-500">
                    12:00 - 13:00
                </div>

                <div class="mt-1 text-xs text-red-500">
                    Penuh
                </div>
            </button>

            {{-- Tersedia --}}
            <button
                type="button"
                class="rounded-xl border-2 border-green-500 bg-green-50 px-4 py-4 text-center transition hover:bg-green-100"
            >
                <div class="font-bold text-green-700">
                    13:00 - 14:00
                </div>

                <div class="mt-1 text-xs text-green-600">
                    Tersedia
                </div>
            </button>

            {{-- Tersedia --}}
            <button
                type="button"
                class="rounded-xl border-2 border-green-500 bg-green-50 px-4 py-4 text-center transition hover:bg-green-100"
            >
                <div class="font-bold text-green-700">
                    14:00 - 15:00
                </div>

                <div class="mt-1 text-xs text-green-600">
                    Tersedia
                </div>
            </button>

            {{-- Penuh --}}
            <button
                type="button"
                disabled
                class="cursor-not-allowed rounded-xl border-2 border-gray-300 bg-gray-100 px-4 py-4 text-center opacity-70"
            >
                <div class="font-bold text-gray-500">
                    15:00 - 16:00
                </div>

                <div class="mt-1 text-xs text-red-500">
                    Penuh
                </div>
            </button>

        </div>
    </div>

</div>

@endsection