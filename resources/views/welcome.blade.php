@extends('layouts.app')

@section('title', 'Katalog Lapangan')

@section('content')
<div
    x-data="{
        sport: 'Semua',
        date: '',
        dp: false,
        parking: false,
        indoor: false,
        venues: [
            {
                name: 'Viva Futsal Arena Malang',
                 id: 1,
                category: 'Futsal',
                badge: 'Vinyl AFC Class',
                rating: '4.9',
                reviews: '140 ulasan',
                location: '1.2 km • Lowokwaru',
                price: 'Rp 150.000',
                slots: 3,
                image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBYT6BJ2opGzVfSpOXFNJBColsgCn0dOdKuGwNnMaqIQI4Zu207zVbE9pz0oe8EdVT1iZDo0jVoO7XZfekr5KtsMjMfa-g77KaVmyyW3FXtCoihTzRNzLmTe9h06mLUxnNX3gvp4XU8zhkbOjUdDXPJhEe6B-ACA_kJyla33RS_WDyLW2Qpuat12TgCAxzxX8hTBt6x4hrAPIWcs_Ck0Y26QgHcjjtgLOFMy2padr3y3GJuNth1NTkI',
                facilities: [
                    'Lantai Taraflex',
                    'Ruang Ganti Ber-AC',
                    'Kantin',
                    'Parkir Luas'
                ]
            },
            {
                name: 'Badminton Smash Arena Malang',
                id: 2,
                category: 'Badminton',
                badge: 'BWF Standard Mat',
                rating: '4.95',
                reviews: '210 ulasan',
                location: '2.8 km • Dinoyo',
                price: 'Rp 90.000',
                slots: 5,
                image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDikc4qmwj2RFHcRdd5Wd6vb-lxqvaqpF-m__-yzFxHAZyV5ORMyLk91T1Lk2xW9yW8LavilOCqjtFfgr9We-F-IhD4azlA4Lrdzxb_hJ2oiVHJ0p6hYDcZD6QeqnAIq6ynE1lM1N2spuKr3PBljlj72hFTvowYTc6lLc61ofdVHV3TO5q3dbihU17eKhkl8R5156oFKXH5FGFMoP1a-XRJV824TP2pHyG99EC1nbduJUl73M3IMJtn',
                facilities: [
                    'Karpet Tebal 5mm',
                    'Lampu Anti-Silau',
                    'Sewa Raket & Kok',
                    'Shower Hangat'
                ]
            },
            {
                name: 'Garuda Mini Soccer Stadium Malang',
                id: 3,
                category: 'Mini Soccer',
                badge: 'Rumput Sintetis FIFA Pro',
                rating: '4.88',
                reviews: '324 ulasan',
                location: '3.4 km • Soekarno Hatta',
                price: 'Rp 650.000',
                slots: 2,
                image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBxCzMVeE8pB1abc0ClRl4U_T_8LqlY-ox6AsoAs1vTMxgu_b8jS2Ztnch84tom1o8vC002uLxGGyP6Oky84FZoMcBek-wex4mcteFV2E-szim6Ee0FstDyweDy8RG5RjdavTY2ZlS-xBwaInLsXKS9liYUDm67qJXsf2KxWax5wpM0PT_8XnahpuT_rle2LQuMWVDHhhlWAMqfV1gHLHvCt3D2WbEzsqsaenRoWpecDb50AMkMow92',
                facilities: [
                    '5G Monofilament Turf',
                    'Lampu 800 Lux',
                    'Live Streaming Cam',
                    'Free Rompi & Bola'
                ]
            },
            {
                name: 'Supreme Futsal & Padel Hub Malang',
                id: 4,
                category: 'Futsal',
                badge: 'Interlock Premium',
                rating: '4.79',
                reviews: '98 ulasan',
                location: '4.1 km • Blimbing',
                price: 'Rp 175.000',
                slots: 4,
                image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCEry_sw5rS_9j0unvjYYE9IUOxNaPdbQofcsXezolL9AhXfknzETIvloJumdlXJqiN9P3209ZDO5havQP8t6WL9aIy7Wssi8hVyHY_ahhGt11p6GwGCWU4slxtHHc5YtpfV8wataiTc34fhhv0ZtaEOjm6j0vU-xDNrLBwjvwZSqivHaF9zum1u6GjiTgeRKBdcALrIq0NZ7toh0MqhlIEbUSxB214jdmXl92xn3zLoIR-YLvB21g3',
                facilities: [
                    'Modular Interlock',
                    'Papan Skor Digital',
                    'Coffee Shop Arena',
                    'Free WiFi 100Mbps'
                ]
            }
        ]
    }"
    class="min-h-screen bg-[#f8f9ff] text-[#0b1c30]"
>

    {{-- Hero --}}
    <section class="px-4 sm:px-6 lg:px-8 pt-10 pb-8">
        <div class="max-w-7xl mx-auto">

            <div class="inline-flex items-center gap-2 px-3 py-1.5 mb-5 rounded-full bg-[#eaf7d8] text-[#416900] text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-[#416900]"></span>
                24 Venue Siap Pakai
            </div>

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-[#1a146b] max-w-3xl">
                Temukan Arena
                <span class="text-[#416900]">
                    Olahraga Favoritmu
                </span>
            </h1>

            <p class="mt-4 max-w-2xl text-sm sm:text-base leading-7 text-gray-600">
                Booking instan terintegrasi, jadwal live terupdate otomatis 24 jam
                dengan konfirmasi langsung tanpa antre.
            </p>

        </div>
    </section>


    {{-- Filter --}}
    <section class="sticky top-20 z-30 bg-[#f8f9ff]/95 backdrop-blur-md border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">

            <div class="flex flex-col gap-4">

                {{-- Category --}}
                <div class="flex flex-wrap items-center gap-2">

                    <span class="text-sm font-bold text-gray-700 mr-1">
                        Kategori:
                    </span>

                    <button
                        type="button"
                        @click="sport = 'Semua'"
                        :class="sport === 'Semua'
                            ? 'bg-[#1a146b] text-white'
                            : 'bg-white text-gray-700 border border-gray-200'"
                        class="px-4 py-2 rounded-full text-sm font-semibold transition"
                    >
                        Semua <span class="opacity-70">(24)</span>
                    </button>

                    <button
                        type="button"
                        @click="sport = 'Futsal'"
                        :class="sport === 'Futsal'
                            ? 'bg-[#1a146b] text-white'
                            : 'bg-white text-gray-700 border border-gray-200'"
                        class="px-4 py-2 rounded-full text-sm font-semibold transition"
                    >
                        Futsal <span class="opacity-70">(8)</span>
                    </button>

                    <button
                        type="button"
                        @click="sport = 'Badminton'"
                        :class="sport === 'Badminton'
                            ? 'bg-[#1a146b] text-white'
                            : 'bg-white text-gray-700 border border-gray-200'"
                        class="px-4 py-2 rounded-full text-sm font-semibold transition"
                    >
                        Badminton <span class="opacity-70">(10)</span>
                    </button>

                    <button
                        type="button"
                        @click="sport = 'Mini Soccer'"
                        :class="sport === 'Mini Soccer'
                            ? 'bg-[#1a146b] text-white'
                            : 'bg-white text-gray-700 border border-gray-200'"
                        class="px-4 py-2 rounded-full text-sm font-semibold transition"
                    >
                        Mini Soccer <span class="opacity-70">(6)</span>
                    </button>

                </div>


                {{-- Date + Options --}}
                <div class="flex flex-col lg:flex-row lg:items-center gap-3">

                    {{-- Date --}}
                    <div class="flex items-center gap-2">
                        <label
                            for="tanggal"
                            class="text-sm font-bold text-gray-700"
                        >
                            Tanggal:
                        </label>

                        <input
                            id="tanggal"
                            type="date"
                            x-model="date"
                            class="px-4 py-2 rounded-xl border border-gray-200 bg-white text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#acf847]"
                        >
                    </div>


                    {{-- Quick Filters --}}
                    <div class="flex flex-wrap gap-2">

                        <button
                            type="button"
                            @click="dp = !dp"
                            :class="dp
                                ? 'bg-[#eaf7d8] text-[#416900] border-[#acf847]'
                                : 'bg-white text-gray-700 border-gray-200'"
                            class="px-4 py-2 rounded-xl border text-sm font-semibold transition"
                        >
                            Bisa DP 50%
                        </button>

                        <button
                            type="button"
                            @click="parking = !parking"
                            :class="parking
                                ? 'bg-[#eaf7d8] text-[#416900] border-[#acf847]'
                                : 'bg-white text-gray-700 border-gray-200'"
                            class="px-4 py-2 rounded-xl border text-sm font-semibold transition"
                        >
                            Parkir Mobil
                        </button>

                        <button
                            type="button"
                            @click="indoor = !indoor"
                            :class="indoor
                                ? 'bg-[#eaf7d8] text-[#416900] border-[#acf847]'
                                : 'bg-white text-gray-700 border-gray-200'"
                            class="px-4 py-2 rounded-xl border text-sm font-semibold transition"
                        >
                            AC / Indoor
                        </button>

                    </div>

                </div>

            </div>
        </div>
    </section>


    {{-- Venue Grid --}}
    <section class="px-4 sm:px-6 lg:px-8 py-10">
        <div class="max-w-7xl mx-auto">

            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-[#1a146b]">
                        Venue Pilihan
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Temukan lapangan terbaik di sekitarmu.
                    </p>
                </div>

                <select
                    class="hidden sm:block px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-700 focus:outline-none"
                >
                    <option>Terdekat dari Lokasimu</option>
                    <option>Rating Tertinggi</option>
                    <option>Harga Terendah</option>
                    <option>Slot Malam Kosong Terbanyak</option>
                </select>
            </div>


            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <template x-for="venue in venues" :key="venue.name">

                    <article
                        x-show="sport === 'Semua' || venue.category === sport"
                        x-transition
                        @click="window.location.href = '/lapangan/' + venue.id"
                        class="cursor-pointer bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition overflow-hidden"
                    >
                        <div class="grid grid-cols-1 md:grid-cols-[220px_1fr]">

                            {{-- Image --}}
                            <div class="relative h-56 md:h-full min-h-[240px] bg-gray-100 overflow-hidden">

                                <img
                                    :src="venue.image"
                                    :alt="venue.name"
                                    class="w-full h-full object-cover"
                                >

                                <span
                                    class="absolute top-4 left-4 px-3 py-1.5 rounded-full bg-white/95 text-[#1a146b] text-xs font-bold shadow-sm"
                                    x-text="venue.badge"
                                ></span>

                            </div>


                            {{-- Content --}}
                            <div class="p-5 flex flex-col">

                                <div class="flex items-start justify-between gap-3">

                                    <div>
                                        <p
                                            class="text-xs font-bold uppercase tracking-wide text-[#416900]"
                                            x-text="venue.category"
                                        ></p>

                                        <h3
                                            class="mt-1 text-lg font-extrabold text-[#1a146b]"
                                            x-text="venue.name"
                                        ></h3>
                                    </div>

                                    <div class="text-right shrink-0">
                                        <div class="font-bold text-sm text-gray-900">
                                            ★ <span x-text="venue.rating"></span>
                                        </div>

                                        <div
                                            class="text-xs text-gray-400"
                                            x-text="venue.reviews"
                                        ></div>
                                    </div>

                                </div>


                                <p
                                    class="mt-3 text-sm text-gray-500"
                                    x-text="venue.location"
                                ></p>


                                {{-- Facilities --}}
                                <div class="grid grid-cols-2 gap-2 mt-5">

                                    <template
                                        x-for="facility in venue.facilities"
                                        :key="facility"
                                    >
                                        <div class="flex items-center gap-2 text-xs text-gray-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#acf847] shrink-0"></span>
                                            <span x-text="facility"></span>
                                        </div>
                                    </template>

                                </div>


                                <div class="mt-5 pt-4 border-t border-gray-100">

                                    <div class="flex items-center justify-between gap-3">

                                        <div>
                                            <p class="text-xs text-gray-400">
                                                Jadwal malam ini
                                            </p>

                                            <p
                                                class="text-sm font-bold text-[#416900]"
                                                x-text="venue.slots + ' slot kosong'"
                                            ></p>
                                        </div>

                                        <div class="text-right">
                                            <p
                                                class="text-lg font-extrabold text-[#1a146b]"
                                                x-text="venue.price"
                                            ></p>

                                            <p class="text-xs text-gray-400">
                                                / jam
                                            </p>
                                        </div>

                                    </div>


                                    <a
                                        href="#"
                                        class="mt-4 flex items-center justify-center w-full px-4 py-3 rounded-xl bg-[#1a146b] text-white text-sm font-bold hover:bg-[#312e81] transition"
                                    >
                                        Lihat Detail & Jadwal
                                    </a>

                                </div>

                            </div>

                        </div>

                    </article>

                </template>

            </div>


            {{-- Empty State --}}
            <div
                x-show="venues.filter(v => sport === 'Semua' || v.category === sport).length === 0"
                class="py-16 text-center"
            >
                <h3 class="text-lg font-bold text-gray-700">
                    Venue tidak ditemukan
                </h3>

                <p class="mt-2 text-sm text-gray-500">
                    Coba pilih kategori lainnya.
                </p>
            </div>


            {{-- Pagination --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-10">

                <p class="text-sm text-gray-500">
                    Menampilkan <span class="font-bold text-gray-700">1 - 4</span>
                    dari <span class="font-bold text-gray-700">24</span> venue terdekat
                </p>

                <div class="flex items-center gap-2">

                    <button
                        type="button"
                        class="w-10 h-10 rounded-xl border border-gray-200 bg-white text-gray-400"
                    >
                        ‹
                    </button>

                    <button
                        type="button"
                        class="w-10 h-10 rounded-xl bg-[#1a146b] text-white font-bold"
                    >
                        1
                    </button>

                    <button
                        type="button"
                        class="w-10 h-10 rounded-xl border border-gray-200 bg-white text-gray-700"
                    >
                        2
                    </button>

                    <button
                        type="button"
                        class="w-10 h-10 rounded-xl border border-gray-200 bg-white text-gray-700"
                    >
                        3
                    </button>

                    <button
                        type="button"
                        class="w-10 h-10 rounded-xl border border-gray-200 bg-white text-gray-700"
                    >
                        ›
                    </button>

                </div>

            </div>

        </div>
    </section>


    {{-- Benefits --}}
    <section class="px-4 sm:px-6 lg:px-8 pb-12">
        <div class="max-w-7xl mx-auto">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h3 class="font-extrabold text-[#1a146b]">
                        Garansi Slot Resmi
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-gray-500">
                        Jadwal yang tampil diperbarui secara berkala agar
                        informasi slot tetap akurat.
                    </p>
                </div>

                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h3 class="font-extrabold text-[#1a146b]">
                        DP 50% Ringan
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-gray-500">
                        Pilihan pembayaran fleksibel untuk memudahkan proses
                        booking lapangan.
                    </p>
                </div>

                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h3 class="font-extrabold text-[#1a146b]">
                        Bebas Reschedule
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-gray-500">
                        Pengguna dapat melakukan perubahan jadwal sesuai
                        kebijakan venue.
                    </p>
                </div>

            </div>

        </div>
    </section>

</div>
@endsection