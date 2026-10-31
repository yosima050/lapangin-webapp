@extends('layouts.app')

@section('title', 'Cek Jadwal Lapangan - LapangIn')

@section('content')
<div class="min-h-screen bg-[#f8f9ff] text-[#0b1c30] pb-16"
     x-data="{
         selectedKategori: '{{ $kategori }}',
         selectedLapangan: '{{ $selectedLapanganId ?? '' }}',
         activeDate: '{{ $tanggal }}',
         matrix: {{ Js::from($matrix) }},
         totalTersedia: {{ $totalSlotTersedia }},
         totalBooked: {{ $totalSlotDibooking }},

         filterMatrix() {
             return this.matrix.filter(row => {
                 const matchKategori = (this.selectedKategori === 'Semua' || !this.selectedKategori || row.lapangan.kategori === this.selectedKategori);
                 const matchLapangan = (!this.selectedLapangan || row.lapangan.id === this.selectedLapangan);
                 return matchKategori && matchLapangan;
             });
         },

         countFilteredTersedia() {
             return this.filterMatrix().reduce((acc, row) => acc + (row.tersedia_count || 0), 0);
         },

         countFilteredBooked() {
             return this.filterMatrix().reduce((acc, row) => acc + (row.booked_count || 0), 0);
         }
     }">

    {{-- Hero Section (Selaras dengan Katalog Lapangan) --}}
    <section class="px-4 sm:px-6 lg:px-8 pt-8 pb-8">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-[#1a146b] max-w-3xl">
                    Cek Ketersediaan
                    <span class="text-[#416900]">
                        Lapangan
                    </span>
                </h1>
                <p class="mt-4 max-w-2xl text-sm sm:text-base leading-7 text-gray-600">
                    Pantau ketersediaan slot jam setiap venue secara transparan. Klik slot jam hijau untuk langsung memesan tiket.
                </p>
            </div>

            {{-- Summary Stats Pills --}}
            <div class="flex flex-wrap items-center gap-3">
                <div class="bg-white rounded-2xl px-5 py-3 border border-gray-200/80 shadow-xs min-w-[110px]">
                    <span class="text-[11px] uppercase tracking-wider font-bold text-gray-500 block">Slot Kosong</span>
                    <span class="text-2xl font-black text-[#416900]" x-text="countFilteredTersedia()">{{ $totalSlotTersedia }}</span>
                </div>
                <div class="bg-white rounded-2xl px-5 py-3 border border-gray-200/80 shadow-xs min-w-[110px]">
                    <span class="text-[11px] uppercase tracking-wider font-bold text-gray-500 block">Terisi</span>
                    <span class="text-2xl font-black text-rose-600" x-text="countFilteredBooked()">{{ $totalSlotDibooking }}</span>
                </div>
                <div class="bg-white rounded-2xl px-5 py-3 border border-gray-200/80 shadow-xs min-w-[110px]">
                    <span class="text-[11px] uppercase tracking-wider font-bold text-gray-500 block">Venue Tampil</span>
                    <span class="text-2xl font-black text-[#1a146b]" x-text="filterMatrix().length">{{ count($matrix) }}</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Filter & Date Carousel (Lega, Bersih & Elegan) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-2">
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 sm:p-5">
            <div class="flex flex-col gap-5">

                {{-- Baris 1: Date Quick Selector (7 Hari) + Date Picker --}}
                <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                    {{-- Carousel 7 Hari --}}
                    <div class="flex items-center gap-2 overflow-x-auto py-1 scrollbar-none">
                        @foreach ($dateOptions as $dOpt)
                            <a
                                href="{{ route('jadwal.index', array_merge(request()->query(), ['tanggal' => $dOpt['date']])) }}"
                                class="flex-shrink-0 flex flex-col items-center justify-center min-w-[80px] sm:min-w-[92px] px-3 py-2.5 rounded-xl border transition-all text-center {{ $dOpt['is_active'] ? 'bg-[#1a146b] text-white border-[#1a146b] shadow-sm' : 'bg-gray-50/80 hover:bg-gray-100 text-gray-700 border-gray-200' }}"
                            >
                                <span class="text-[10px] font-extrabold tracking-wider {{ $dOpt['is_active'] ? 'text-[#acf847]' : 'text-gray-500' }}">
                                    {{ $dOpt['label_top'] }}
                                </span>
                                <span class="text-lg font-black leading-tight mt-0.5">
                                    {{ $dOpt['day_num'] }}
                                </span>
                                <span class="text-[11px] font-medium opacity-80">
                                    {{ $dOpt['month_name'] }}
                                </span>
                            </a>
                        @endforeach
                    </div>

                    {{-- Manual Date Picker Form --}}
                    <form method="GET" action="{{ route('jadwal.index') }}" class="flex items-center gap-2 flex-shrink-0">
                        @if(request('kategori'))
                            <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                        @endif
                        @if(request('lapangan_id'))
                            <input type="hidden" name="lapangan_id" value="{{ request('lapangan_id') }}">
                        @endif
                        <label for="tanggal_picker" class="text-xs font-bold text-gray-600 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-[#1a146b]">calendar_month</span>
                            Pilih Tanggal:
                        </label>
                        <input
                            id="tanggal_picker"
                            name="tanggal"
                            type="date"
                            min="{{ $today }}"
                            value="{{ $tanggal }}"
                            onchange="this.form.submit()"
                            class="px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-xs font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#acf847]"
                        >
                    </form>
                </div>

                {{-- Baris 2: Filter Kategori & Filter Spesifik Lapangan (Instan Client-side) --}}
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                    {{-- Kategori Badges (Instan 0 ms) --}}
                    <div class="flex items-center gap-2 overflow-x-auto py-1 scrollbar-none">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mr-1">Kategori:</span>

                        <button
                            type="button"
                            @click="selectedKategori = 'Semua'"
                            :class="selectedKategori === 'Semua' ? 'bg-[#1a146b] text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-3.5 py-1.5 rounded-full text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                        >
                            Semua
                        </button>

                        @foreach ($kategoriList as $kat)
                            @if(filled($kat))
                                <button
                                    type="button"
                                    @click="selectedKategori = '{{ $kat }}'"
                                    :class="selectedKategori === '{{ $kat }}' ? 'bg-[#1a146b] text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition cursor-pointer"
                                >
                                    {{ $kat }}
                                </button>
                            @endif
                        @endforeach
                    </div>

                    {{-- Dropdown Pilih Lapangan Spesifik (Instan 0 ms) --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <select
                            x-model="selectedLapangan"
                            class="px-3 py-2 rounded-xl border border-gray-200 bg-white text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#acf847]"
                        >
                            <option value="">-- Semua Lapangan --</option>
                            @foreach ($allLapangans as $al)
                                <option value="{{ $al->id }}">
                                    {{ $al->nama }} ({{ $al->kategori }})
                                </option>
                            @endforeach
                        </select>

                        <button
                            type="button"
                            x-show="selectedLapangan !== ''"
                            @click="selectedLapangan = ''"
                            class="p-2 text-gray-400 hover:text-red-500 transition rounded-lg"
                            title="Reset filter lapangan"
                        >
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Legend Info Bar --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-5">
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3 flex flex-wrap items-center justify-between gap-4 text-xs">
            <div class="flex flex-wrap items-center gap-4 sm:gap-6">
                <span class="font-bold text-gray-500 uppercase tracking-wider">Keterangan Slot:</span>
                
                {{-- Slot Tersedia --}}
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded-md bg-[#eaf7d8] border border-[#acf847] text-[#416900] flex items-center justify-center font-bold text-[10px]">
                        ✓
                    </span>
                    <span class="font-medium text-gray-700">Tersedia (Bisa Dipesan)</span>
                </div>

                {{-- Slot Terisi --}}
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded-md bg-red-100 border border-red-200 text-red-600 flex items-center justify-center font-bold text-[10px]">
                        ✕
                    </span>
                    <span class="font-medium text-gray-700">Terisi / Dibooking</span>
                </div>

                {{-- Slot Pending --}}
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded-md bg-amber-100 border border-amber-300 text-amber-700 flex items-center justify-center font-bold text-[10px]">
                        ⏳
                    </span>
                    <span class="font-medium text-gray-700">Menunggu Pembayaran</span>
                </div>

                {{-- Slot Lewat --}}
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded-md bg-gray-100 border border-gray-200 text-gray-400 flex items-center justify-center font-bold text-[10px]">
                        -
                    </span>
                    <span class="font-medium text-gray-400">Lewat Jam</span>
                </div>
            </div>

            <div class="text-gray-500 text-[11px] italic">
                *Klik pada kotak jam yang <strong>tersedia</strong> untuk langsung masuk ke pemesanan lapangan.
            </div>
        </div>
    </section>

    {{-- Matriks Jadwal ala Bioskop --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        @if (empty($matrix))
            <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
                <span class="material-symbols-outlined text-5xl text-gray-300">event_busy</span>
                <h3 class="mt-3 text-lg font-extrabold text-gray-700">Tidak ada lapangan ditemukan</h3>
                <p class="mt-1 text-sm text-gray-500">Coba sesuaikan filter kategori atau tanggal Anda.</p>
                <a href="{{ route('jadwal.index') }}" class="mt-4 inline-block px-5 py-2 rounded-xl bg-[#1a146b] text-white text-xs font-bold hover:bg-opacity-90">
                    Reset Filter
                </a>
            </div>
        @else
            <div class="flex flex-col gap-6">
                @foreach ($matrix as $row)
                    @php
                        $lap = $row['lapangan'];
                        $slots = $row['slots'];
                    @endphp
                    <div
                        x-show="(selectedKategori === 'Semua' || !selectedKategori || '{{ $lap->kategori }}' === selectedKategori) && (!selectedLapangan || '{{ $lap->id }}' === selectedLapangan)"
                        x-transition
                        class="bg-white rounded-2xl border border-gray-150 shadow-xs hover:shadow-md transition-shadow overflow-hidden"
                    >
                        {{-- Header Bar Lapangan --}}
                        <div class="px-5 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <img
                                    src="{{ $lap->foto_url }}"
                                    alt="{{ $lap->nama }}"
                                    class="w-12 h-12 rounded-xl object-cover border border-gray-200 shadow-xs"
                                    onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($lap->nama) }}&background=1a146b&color=acf847&size=200';"
                                >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-md bg-[#1a146b]/10 text-[#1a146b] text-[10px] font-extrabold uppercase tracking-wider">
                                            {{ $lap->kategori }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            Operasional: {{ $row['jam_operasional']['jam_buka'] }} – {{ $row['jam_operasional']['jam_tutup'] }} WIB
                                        </span>
                                    </div>
                                    <h3 class="text-base sm:text-lg font-extrabold text-[#0b1c30] mt-0.5">
                                        {{ $lap->nama }}
                                    </h3>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 self-end sm:self-center">
                                <div class="text-right">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $row['tersedia_count'] > 0 ? 'bg-[#eaf7d8] text-[#416900]' : 'bg-red-50 text-red-600' }}">
                                        <span class="w-2 h-2 rounded-full {{ $row['tersedia_count'] > 0 ? 'bg-[#416900]' : 'bg-red-500' }}"></span>
                                        {{ $row['tersedia_count'] }} Slot Tersedia
                                    </span>
                                </div>
                                <a
                                    href="{{ route('katalog.show', ['id' => $lap->id, 'tanggal' => $tanggal]) }}"
                                    class="px-3.5 py-1.5 rounded-xl border border-gray-200 hover:border-[#1a146b] text-[#1a146b] text-xs font-bold transition flex items-center gap-1"
                                >
                                    Detail
                                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                </a>
                            </div>
                        </div>

                        {{-- Cinema Seat Matrix of Hours --}}
                        <div class="p-5">
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2.5">
                                @forelse ($slots as $slot)
                                    @php
                                        $status = $slot['status'];
                                        $isTersedia = $slot['is_tersedia'];
                                        $isBooked = ($status === 'dibooking');
                                        $isPending = ($status === 'pending');
                                        $isLewat = ($status === 'lewat_jam');
                                    @endphp

                                    @if ($isTersedia)
                                        <a
                                            href="{{ route('katalog.show', ['id' => $lap->id, 'tanggal' => $tanggal, 'jam' => $slot['jam_mulai']]) }}"
                                            class="group relative flex flex-col items-center justify-center py-3 px-2 rounded-xl border-2 border-[#acf847] bg-[#f7fee7] hover:bg-[#acf847] hover:border-[#98e235] text-[#102000] transition transform hover:-translate-y-0.5 hover:shadow-md cursor-pointer text-center"
                                            title="Slot Kosong: {{ $slot['jam_label'] }} (Klik untuk Booking)"
                                        >
                                            <span class="text-xs font-black tracking-tight group-hover:scale-105 transition-transform">
                                                {{ $slot['jam_mulai'] }}
                                            </span>
                                            <span class="text-[10px] font-bold text-[#416900] group-hover:text-[#102000]">
                                                s/d {{ $slot['jam_selesai'] }}
                                            </span>
                                            <span class="mt-1 px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase bg-white/80 group-hover:bg-white text-[#416900]">
                                                KOSONG
                                            </span>
                                        </a>
                                    @elseif ($isPending)
                                        <div
                                            class="relative flex flex-col items-center justify-center py-3 px-2 rounded-xl border border-amber-300 bg-amber-50 text-amber-900 cursor-not-allowed opacity-90 text-center"
                                            title="Menunggu Pembayaran: {{ $slot['jam_label'] }}"
                                        >
                                            <span class="text-xs font-bold tracking-tight text-amber-800">
                                                {{ $slot['jam_mulai'] }}
                                            </span>
                                            <span class="text-[10px] font-medium text-amber-600">
                                                s/d {{ $slot['jam_selesai'] }}
                                            </span>
                                            <span class="mt-1 px-1.5 py-0.2 rounded text-[9px] font-bold uppercase bg-amber-200/70 text-amber-800">
                                                PROSES
                                            </span>
                                        </div>
                                    @elseif ($isBooked)
                                        <div
                                            class="relative flex flex-col items-center justify-center py-3 px-2 rounded-xl border border-red-200 bg-red-50 text-red-900 cursor-not-allowed opacity-85 text-center"
                                            title="Sudah Dibooking: {{ $slot['jam_label'] }}"
                                        >
                                            <span class="text-xs font-bold tracking-tight text-red-800 line-through">
                                                {{ $slot['jam_mulai'] }}
                                            </span>
                                            <span class="text-[10px] font-medium text-red-600">
                                                s/d {{ $slot['jam_selesai'] }}
                                            </span>
                                            <span class="mt-1 px-1.5 py-0.2 rounded text-[9px] font-bold uppercase bg-red-200/70 text-red-800">
                                                TERISI
                                            </span>
                                        </div>
                                    @else
                                        {{-- Lewat Jam --}}
                                        <div
                                            class="relative flex flex-col items-center justify-center py-3 px-2 rounded-xl border border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed text-center"
                                            title="Jam telah berlalu: {{ $slot['jam_label'] }}"
                                        >
                                            <span class="text-xs font-semibold tracking-tight text-gray-400">
                                                {{ $slot['jam_mulai'] }}
                                            </span>
                                            <span class="text-[10px] font-medium text-gray-400">
                                                s/d {{ $slot['jam_selesai'] }}
                                            </span>
                                            <span class="mt-1 px-1.5 py-0.2 rounded text-[9px] font-semibold uppercase bg-gray-200 text-gray-500">
                                                LEWAT
                                            </span>
                                        </div>
                                    @endif
                                @empty
                                    <div class="col-span-full py-4 text-center text-xs text-gray-400">
                                        Tidak ada jam operasional untuk hari ini.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

</div>
@endsection

