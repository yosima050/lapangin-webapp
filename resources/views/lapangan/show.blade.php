@extends('layouts.app')

@section('title', 'Detail Lapangan - ' . $lapangan->nama)

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

    {{-- Tombol Kembali --}}
    <a href="{{ route('katalog.index') }}"
       class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-indigo-700">
        ← Kembali ke Katalog
    </a>

    {{-- Informasi Lapangan --}}
    <div class="grid gap-8 lg:grid-cols-2">

        {{-- Gambar Lapangan (Sementara pakai placeholder jika belum ada fitur upload) --}}
        <div class="overflow-hidden rounded-3xl bg-gray-100">
            <img
                src="https://ui-avatars.com/api/?name={{ urlencode($lapangan->nama) }}&background=E0E7FF&color=3730A3&size=512"
                alt="{{ $lapangan->nama }}"
                class="h-80 w-full object-cover sm:h-96"
            >
        </div>

        {{-- Detail --}}
        <div class="flex flex-col justify-center">
            <span class="mb-3 w-fit rounded-full bg-indigo-100 px-3 py-1 text-sm font-semibold text-indigo-700">
                {{ $lapangan->kategori }}
            </span>

            <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                {{ $lapangan->nama }}
            </h1>

            <p class="mt-3 text-gray-600">
                📍 Tersedia di LapangIn
            </p>

            <p class="mt-5 leading-7 text-gray-600">
                Jam Operasional: {{ $jadwalData['jam_operasional']['jam_buka'] }} - {{ $jadwalData['jam_operasional']['jam_tutup'] }}
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                <span class="rounded-lg bg-gray-100 px-3 py-2 text-sm text-gray-700">Indoor</span>
                <span class="rounded-lg bg-gray-100 px-3 py-2 text-sm text-gray-700">Parkir Mobil</span>
                <span class="rounded-lg bg-gray-100 px-3 py-2 text-sm text-gray-700">Bisa DP 50%</span>
            </div>
        </div>
    </div>

    {{-- Form Filter Tanggal --}}
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900">Pilih Tanggal</h2>
        
        <form method="GET" action="{{ route('katalog.show', $lapangan->id) }}" class="mt-4 max-w-sm">
            <input
                type="date"
                name="tanggal"
                value="{{ $tanggalDipilih }}"
                onchange="this.form.submit()"
                class="w-full rounded-xl border border-gray-300 px-4 py-3 outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-200"
            >
        </form>
    </div>

    {{-- Pilih Jam --}}
    <div class="mt-10">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Pilih Jam</h2>
            <div class="flex items-center gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-green-500"></span>
                    <span class="text-gray-600">Tersedia</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-red-400"></span>
                    <span class="text-gray-600">Penuh / Pending</span>
                </div>
            </div>
        </div>

        {{-- Grid Jam Dinamis --}}
        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            
            @forelse ($jadwalData['slots'] as $slot)
                @if ($slot['is_tersedia'])
                    {{-- Tombol Tersedia --}}
                    <button
                        type="button"
                        class="rounded-xl border-2 border-green-500 bg-green-50 px-4 py-4 text-center transition hover:bg-green-100"
                    >
                        <div class="font-bold text-green-700">
                            {{ $slot['jam_label'] }}
                        </div>
                        <div class="mt-1 text-xs text-green-600">
                            Tersedia
                        </div>
                    </button>
                @else
                    {{-- Tombol Penuh/Dibooking --}}
                    <button
                        type="button"
                        disabled
                        class="cursor-not-allowed rounded-xl border-2 border-gray-300 bg-gray-100 px-4 py-4 text-center opacity-70"
                    >
                        <div class="font-bold text-gray-500">
                            {{ $slot['jam_label'] }}
                        </div>
                        <div class="mt-1 text-xs text-red-500 uppercase">
                            {{ $slot['status'] }}
                        </div>
                    </button>
                @endif
            @empty
                <div class="col-span-full py-4 text-gray-500">
                    Tidak ada slot jadwal yang tersedia pada tanggal ini.
                </div>
            @endforelse

        </div>
    </div>
</div>
@endsection