@extends('layouts.app')

@section('title', 'Detail Lapangan - ' . $lapangan->nama)

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

    {{-- Tombol Kembali --}}
    <a href="{{ route('katalog.index') }}"
       class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-indigo-700 transition">
        ← Kembali ke Katalog
    </a>

    {{-- Informasi Lapangan --}}
    <div class="grid gap-8 lg:grid-cols-2 items-start">

        {{-- Gambar Lapangan Asli dari Storage / URL --}}
        <div class="relative overflow-hidden rounded-3xl bg-gray-900 shadow-md h-80 sm:h-96">
            <img
                src="{{ $lapangan->foto_url }}"
                alt="{{ $lapangan->nama }}"
                class="h-full w-full object-cover transition duration-300 hover:scale-105"
                onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($lapangan->nama) }}&background=1a146b&color=acf847&size=512';"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
            <span class="absolute top-4 left-4 px-3.5 py-1.5 rounded-full bg-[#1a146b]/90 backdrop-blur-md text-[#acf847] text-xs font-bold shadow-sm uppercase tracking-wider">
                {{ $lapangan->kategori }}
            </span>
        </div>

        {{-- Detail & Spesifikasi Lapangan --}}
        <div class="flex flex-col justify-center">
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-[#eaf7d8] text-[#416900] text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-[#416900]"></span>
                    Arena Aktif &amp; Terverifikasi
                </span>
                <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full">
                    ★ 5.0 Rating Pelanggan
                </span>
            </div>

            <h1 class="text-3xl font-extrabold tracking-tight text-[#1a146b] sm:text-4xl">
                {{ $lapangan->nama }}
            </h1>

            <p class="mt-3 text-sm text-gray-600 leading-relaxed">
                {{ $lapangan->deskripsi ?: 'Fasilitas arena olahraga berstandar turnamen, dilengkapi pencahayaan optimal dan sistem pemeliharaan berkala untuk kenyamanan bertanding.' }}
            </p>

            {{-- Skema Tarif Master dari Database --}}
            <div class="mt-5 p-4 rounded-2xl bg-indigo-50/70 border border-indigo-100 grid grid-cols-2 gap-4">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Tarif Dasar Weekday</span>
                    <p class="text-lg font-extrabold text-[#1a146b] mt-0.5">
                        Rp {{ number_format($hargaWeekday ?? 120000, 0, ',', '.') }}
                        <span class="text-xs font-normal text-gray-500">/jam</span>
                    </p>
                    <span class="text-[11px] text-gray-500">Senin – Jumat</span>
                </div>
                <div class="border-l border-indigo-200/80 pl-4">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Tarif Dasar Weekend</span>
                    <p class="text-lg font-extrabold text-[#416900] mt-0.5">
                        Rp {{ number_format($hargaWeekend ?? 200000, 0, ',', '.') }}
                        <span class="text-xs font-normal text-gray-500">/jam</span>
                    </p>
                    <span class="text-[11px] text-[#416900] font-medium">Sabtu, Minggu &amp; Libur</span>
                </div>
            </div>

            <div class="mt-4 flex items-center gap-2 text-xs text-gray-600">
                <span class="material-symbols-outlined text-base text-[#1a146b]">schedule</span>
                <span>Jam Operasional: <strong>{{ substr($lapangan->jam_buka, 0, 5) }} – {{ substr($lapangan->jam_tutup, 0, 5) }} WIB</strong></span>
            </div>

            <div class="mt-5 flex flex-wrap gap-2">
                <span class="rounded-xl bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700">Fasilitas Indoor</span>
                <span class="rounded-xl bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700">Parkir Mobil Luas</span>
                <span class="rounded-xl bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700">Bisa DP 50%</span>
                <span class="rounded-xl bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700">Loker &amp; Ruang Ganti</span>
            </div>
        </div>
    </div>

    {{-- Form Filter Tanggal --}}
    <div class="mt-12 pt-8 border-t border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-[#1a146b]">
                    Pilih Jadwal Main
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Klik slot jam yang bertanda <span class="font-bold text-green-600">Tersedia</span> untuk langsung melakukan reservasi atau checkout.
                </p>
            </div>

            <form method="GET" action="{{ route('katalog.show', $lapangan->id) }}" class="flex items-center gap-2">
                <label for="tanggal" class="text-xs font-bold text-gray-700">Tanggal:</label>
                <input
                    id="tanggal"
                    type="date"
                    name="tanggal"
                    value="{{ $tanggalDipilih }}"
                    onchange="this.form.submit()"
                    class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-200"
                >
            </form>
        </div>
    </div>

    {{-- Pilih Jam --}}
    <div class="mt-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">Ketersediaan Slot Jam (60 Menit)</h3>
            <div class="flex items-center gap-4 text-xs font-semibold">
                <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                    <span class="text-gray-700">Tersedia</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-red-400"></span>
                    <span class="text-gray-700">Penuh / Dipesan</span>
                </div>
            </div>
        </div>

        {{-- Grid Jam Dinamis --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            @forelse ($jadwalData['slots'] as $slot)
                @if ($slot['is_tersedia'])
                    {{-- Tombol Tersedia -> Link Langsung ke Checkout Booking --}}
                    <a
                        href="{{ route('checkout.index', [
                            'lapangan_id' => $lapangan->id,
                            'tanggal' => $tanggalDipilih,
                            'jam_mulai' => $slot['jam_mulai'],
                            'durasi' => 1
                        ]) }}"
                        class="group rounded-2xl border-2 border-green-500 bg-green-50/80 p-4 text-center transition hover:bg-green-100 hover:shadow-md block"
                    >
                        <div class="font-bold text-green-900 group-hover:text-green-950 text-sm">
                            {{ $slot['jam_label'] }}
                        </div>
                        <div class="mt-1.5 inline-flex items-center gap-1 text-[11px] font-bold text-green-700 uppercase tracking-wider">
                            <span>Pilih Slot</span>
                            <span>→</span>
                        </div>
                    </a>
                @else
                    {{-- Tombol Penuh/Dibooking --}}
                    <div
                        class="cursor-not-allowed rounded-2xl border border-gray-200 bg-gray-100/90 p-4 text-center opacity-75"
                    >
                        <div class="font-bold text-gray-500 text-sm">
                            {{ $slot['jam_label'] }}
                        </div>
                        <div class="mt-1.5 text-[11px] font-bold text-red-600 uppercase tracking-wider">
                            {{ $slot['status'] }}
                        </div>
                    </div>
                @endif
            @empty
                <div class="col-span-full py-8 text-center bg-gray-50 rounded-2xl border border-gray-200 text-gray-500 text-sm">
                    Tidak ada slot jadwal yang tersedia pada tanggal ini.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection