@extends('layouts.app')

@section('title', 'Katalog Lapangan')

@php
    $firstKatalogItem = $katalog->first();
    $katalogLcpImage = $firstKatalogItem['foto_card_url'] ?? $firstKatalogItem['foto_url'] ?? null;
@endphp

@push('head')
    @if($katalogLcpImage)
        <link rel="preload" as="image" href="{{ $katalogLcpImage }}" fetchpriority="high" />
    @endif
@endpush

@section('content')
<div
    x-data="{
        sport: '{{ request('kategori', 'Semua') }}',
        date: '{{ request('tanggal', now()->toDateString()) }}',
        searchQuery: '',
        dp: false,
        parking: false,
        indoor: false
    }"
    class="min-h-screen bg-[#f8f9ff] text-[#0b1c30]"
>
    {{-- Hero --}}
  <section class="px-4 sm:px-6 lg:px-8 pt-8 pb-8">
        <div class="max-w-7xl mx-auto">

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

    {{-- Kategori Bar: Sticky (Ikut turun saat scroll down) + Horizontal Scroll Gopay Style (Tanpa Numpuk) --}}
    <section class="sticky top-20 z-30 bg-[#f8f9ff]/95 backdrop-blur-md border-b border-gray-200/80 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5">
            <div class="flex items-center gap-2 overflow-x-auto scrollbar-none -mx-4 px-4 sm:mx-0 sm:px-0">

                @php
                    $currentKategori = request('kategori', 'Semua');
                @endphp

                <button
                    type="button"
                    data-testid="filter-kategori-semua"
                    @click="sport = 'Semua'"
                    :class="sport === 'Semua'
                        ? 'border-[#416900] bg-[#eaf7d8] text-[#284200] font-black ring-1 ring-[#acf847] shadow-xs'
                        : 'border-gray-200 bg-white text-gray-700 font-bold hover:bg-gray-50 hover:border-gray-300'"
                    class="shrink-0 px-5 py-2 rounded-full text-xs sm:text-sm border transition-all inline-flex items-center justify-center cursor-pointer whitespace-nowrap"
                >
                    Semua
                </button>

                @foreach (($kategoriList ?? collect($katalog)->pluck('kategori')->filter()->unique()) as $kat)
                    @if(filled($kat))
                        <button
                            type="button"
                            @click="sport = '{{ $kat }}'"
                            :class="sport === '{{ $kat }}'
                                ? 'border-[#416900] bg-[#eaf7d8] text-[#284200] font-black ring-1 ring-[#acf847] shadow-xs'
                                : 'border-gray-200 bg-white text-gray-700 font-bold hover:bg-gray-50 hover:border-gray-300'"
                            class="shrink-0 px-5 py-2 rounded-full text-xs sm:text-sm border transition-all inline-flex items-center justify-center cursor-pointer whitespace-nowrap"
                        >
                            {{ $kat }}
                        </button>
                    @endif
                @endforeach

            </div>
        </div>
    </section>

    {{-- Venue Grid --}}
    <section class="px-4 sm:px-6 lg:px-8 py-10">
        <div class="max-w-7xl mx-auto">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-[#1a146b]">
                        Venue Pilihan
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Temukan lapangan terbaik di sekitarmu.
                    </p>
                </div>

                {{-- Search Bar Lapangan --}}
                <div class="relative w-full sm:w-72 lg:w-80">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 pointer-events-none">
                        <x-lucide-search class="w-5 h-5 shrink-0" />
                    </span>
                    <input
                        type="text"
                        x-model="searchQuery"
                        data-testid="katalog-search-input"
                        placeholder="Cari nama lapangan / venue..."
                        class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-10 text-xs sm:text-sm font-semibold text-gray-800 placeholder-gray-400 shadow-2xs transition focus:border-[#1a146b] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#1a146b]"
                    >
                    <button
                        type="button"
                        x-show="searchQuery.trim().length > 0"
                        @click="searchQuery = ''"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 transition cursor-pointer"
                        title="Hapus pencarian"
                    >
                        <x-lucide-x class="w-4 h-4 shrink-0" />
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5 sm:gap-6">

                @forelse ($katalog as $item)
                    @php
                        $searchKeyword = strtolower($item['lapangan_nama'] . ' ' . $item['kategori'] . ' ' . ($item['deskripsi'] ?? ''));
                    @endphp
                    <article
                        x-show="(sport === 'Semua' || '{{ $item['kategori'] }}' === sport) && (!searchQuery.trim() || {{ json_encode($searchKeyword) }}.includes(searchQuery.toLowerCase().trim()))"
                        x-transition
                        @if($loop->index >= 2)
                            style="content-visibility: auto; contain-intrinsic-size: 1px 160px;"
                        @endif
                        onclick="window.location.href = '{{ route('katalog.show', $item['lapangan_id']) }}'"
                        class="cursor-pointer bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden group flex flex-col"
                    >
                        <div class="grid grid-cols-[116px_1fr] sm:grid-cols-[180px_1fr] md:grid-cols-[240px_1fr] flex-1">

                            {{-- Image Lapangan Asli dari PostgreSQL / Upload --}}
                            <div class="relative h-full min-h-[144px] sm:min-h-[200px] md:min-h-[220px] bg-gray-900 overflow-hidden shrink-0">
                                <img
                                    src="{{ $item['foto_card_url'] ?? $item['foto_url'] ?? ('https://ui-avatars.com/api/?name='.urlencode($item['lapangan_nama']).'&background=1a146b&color=acf847&size=512') }}"
                                    alt="{{ $item['lapangan_nama'] }}"
                                    width="240"
                                    height="220"
                                    @if($loop->first)
                                        fetchpriority="high"
                                        decoding="sync"
                                    @else
                                        loading="lazy"
                                        decoding="async"
                                    @endif
                                   class="w-full h-full object-cover object-center transition duration-300 group-hover:scale-105"
                                    onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($item['lapangan_nama']) }}&background=1a146b&color=acf847&size=512';"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent md:hidden"></div>

                                <span class="absolute top-2 left-2 sm:top-3.5 sm:left-3.5 px-2 py-0.5 sm:px-3 sm:py-1.5 rounded-full bg-[#1a146b]/90 backdrop-blur-md text-[#acf847] text-[10px] sm:text-xs font-bold shadow-sm max-w-[96px] sm:max-w-none truncate">
                                    {{ $item['kategori'] }}
                                </span>
                            </div>

                            {{-- Content Lapangan & Tarif --}}
                            <div class="p-3 sm:p-5 md:p-6 flex flex-col justify-between min-w-0">
                                <div>
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="hidden sm:block text-xs font-bold uppercase tracking-wider text-[#416900]">
                                                {{ $item['kategori'] }}
                                            </p>
                                            <h3 class="sm:mt-1 text-sm sm:text-base md:text-lg font-extrabold text-[#1a146b] group-hover:text-indigo-800 transition leading-snug line-clamp-2">
                                                {{ $item['lapangan_nama'] }}
                                            </h3>
                                        </div>
                                    </div>

                                    @if (!empty($item['deskripsi']))
                                        <p class="hidden sm:line-clamp-2 mt-2 text-xs text-gray-500 leading-relaxed">
                                            {{ $item['deskripsi'] }}
                                        </p>
                                    @endif

                                    <div class="mt-1.5 sm:mt-3 flex items-center gap-1.5 text-[11px] sm:text-xs text-gray-500">
                                        <x-lucide-clock class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#1a146b] shrink-0" />
                                        <span class="truncate">{{ $item['jam_operasional']['jam_buka'] }} – {{ $item['jam_operasional']['jam_tutup'] }} WIB</span>
                                    </div>
                                </div>

                                <div class="mt-2.5 sm:mt-5 pt-2.5 sm:pt-4 border-t border-gray-100">
                                    <div class="flex flex-wrap items-end justify-between gap-2">
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-gray-400">Tarif Mulai</p>
                                                @if(!empty($item['has_harga_khusus']))
                                                    <span class="px-1.5 py-0.5 rounded text-[8px] sm:text-[9px] font-extrabold bg-amber-100 text-amber-800">
                                                        PROMO
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex items-baseline gap-0.5 sm:gap-1">
                                                <span class="text-sm sm:text-lg font-extrabold text-[#0b1c30]">
                                                    Rp {{ number_format($item['harga_termurah'] ?? ($item['harga_weekday'] ?? 100000), 0, ',', '.') }}
                                                </span>
                                                <span class="text-[10px] sm:text-xs text-gray-500 font-medium">/jam</span>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-[10px] sm:text-xs font-bold bg-[#eaf7d8] text-[#416900]">
                                                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-[#416900]"></span>
                                                {{ collect($item['slots'])->where('is_tersedia', true)->count() }} slot
                                            </span>
                                        </div>
                                    </div>

                                    <a
                                        href="{{ route('katalog.show', ['id' => $item['lapangan_id'], 'tanggal' => $tanggal]) }}"
                                        class="mt-2.5 sm:mt-4 flex items-center justify-center w-full px-3 sm:px-4 py-2 sm:py-3 rounded-xl bg-[#1a146b] hover:bg-[#312e81] text-white text-[11px] sm:text-xs font-bold shadow-sm transition"
                                    >
                                        <span class="sm:hidden">Detail &amp; Jadwal →</span>
                                        <span class="hidden sm:inline">Lihat Detail &amp; Jadwal Lengkap →</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full py-16 text-center bg-white rounded-2xl border border-gray-100 p-8">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-indigo-50 text-[#1a146b] flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">Venue tidak ditemukan</h3>
                        <p class="mt-1 text-sm text-gray-500">Belum ada data lapangan yang terdaftar untuk kategori ini.</p>
                    </div>
                @endforelse

                {{-- Empty state saat filter pencarian nama lapangan tidak ditemukan --}}
                @if (count($katalog) > 0)
                    <div
                        x-show="searchQuery.trim().length > 0 && !Array.from($el.closest('.grid').querySelectorAll('article')).some(el => el.style.display !== 'none')"
                        class="col-span-full py-12 text-center bg-white rounded-2xl border border-gray-100 p-8"
                        style="display: none;"
                    >
                        <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-gray-50 text-gray-400 flex items-center justify-center">
                            <x-lucide-search-x class="w-6 h-6 shrink-0" />
                        </div>
                        <h3 class="text-base font-bold text-gray-800">Tidak ada lapangan yang cocok</h3>
                        <p class="mt-1 text-xs sm:text-sm text-gray-500">
                            Tidak ditemukan venue dengan kata kunci "<span class="font-bold text-[#0b1c30]" x-text="searchQuery"></span>".
                        </p>
                        <button
                            type="button"
                            @click="searchQuery = ''; sport = 'Semua'"
                            class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#1a146b] text-white text-xs font-bold hover:bg-indigo-900 transition cursor-pointer shadow-xs"
                        >
                            Reset Pencarian
                        </button>
                    </div>
                @endif

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
