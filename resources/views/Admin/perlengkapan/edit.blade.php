@extends('layouts.admin')

@section('title', 'Edit Perlengkapan - Admin LapangIn')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Edit Perlengkapan
        </h1>
        <p class="mt-1 text-sm text-gray-500">
            Perbarui informasi, harga, dan pengaturan lapangan yang menyediakan perlengkapan ini.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-100 p-4 text-red-700">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-xl bg-white p-6 shadow">
        <form action="{{ route('admin.perlengkapan.update', $perlengkapan->id) }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <label for="nama" class="mb-2 block font-medium text-gray-700">
                    Nama Perlengkapan
                </label>
                <input
                    type="text"
                    id="nama"
                    name="nama"
                    value="{{ old('nama', $perlengkapan->nama) }}"
                    required
                    maxlength="100"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-[#1a146b] focus:outline-none"
                >
            </div>

            <div class="mb-5">
                <label for="deskripsi" class="mb-2 block font-medium text-gray-700">
                    Deskripsi
                </label>
                <textarea
                    id="deskripsi"
                    name="deskripsi"
                    rows="3"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-[#1a146b] focus:outline-none"
                >{{ old('deskripsi', $perlengkapan->deskripsi) }}</textarea>
            </div>

            <div class="mb-5">
                <label for="harga" class="mb-2 block font-medium text-gray-700">
                    Harga Sewa per Sesi (Rp)
                </label>
                <input
                    type="number"
                    id="harga"
                    name="harga"
                    value="{{ old('harga', $perlengkapan->harga) }}"
                    required
                    min="0"
                    step="0.01"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-[#1a146b] focus:outline-none"
                >
            </div>

            {{-- Pemilihan Lapangan untuk Add-On --}}
            <div class="mb-5">
                <label class="mb-1 block font-medium text-gray-700">
                    Tersedia di Lapangan Mana Saja:
                </label>
                <p class="mb-3 text-xs text-gray-500">
                    Pilih lapangan yang menyediakan add-on ini (misal: Rompi & Bola untuk Futsal, Raket untuk Badminton).  
                    <strong class="text-[#1a146b]">Jika tidak ada yang dicentang, perlengkapan ini otomatis berlaku untuk SEMUA lapangan.</strong>
                </p>

                @php
                    $selectedLapanganIds = old('lapangan_ids', $perlengkapan->lapangans->pluck('id')->toArray());
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 p-4 rounded-xl border border-gray-200 bg-gray-50/50">
                    @foreach($lapangans as $lap)
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 bg-white hover:border-[#1a146b] cursor-pointer transition">
                            <input type="checkbox" name="lapangan_ids[]" value="{{ $lap->id }}"
                                @checked(in_array($lap->id, $selectedLapanganIds))
                                class="h-4 w-4 mt-0.5 rounded text-[#1a146b] focus:ring-[#acf847]">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-gray-900 leading-snug">{{ $lap->nama }}</span>
                                <span class="text-[11px] text-gray-500 font-medium">Kategori: {{ $lap->kategori }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-5">
                <label for="gambar" class="mb-2 block font-medium text-gray-700">
                    Gambar Perlengkapan
                </label>

                @if ($perlengkapan->gambar)
                    <div class="mb-3">
                        <p class="mb-2 text-sm text-gray-600">Gambar saat ini:</p>
                        <img
                            src="{{ asset('storage/' . $perlengkapan->gambar) }}"
                            alt="{{ $perlengkapan->nama }}"
                            class="h-32 w-32 rounded-lg object-cover border"
                        >
                    </div>
                @endif

                <input
                    type="file"
                    id="gambar"
                    name="gambar"
                    accept=".jpg,.jpeg,.png,.webp"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2"
                >
                <p class="mt-1 text-sm text-gray-500">
                    Pilih gambar baru jika ingin mengganti gambar lama. Format JPG, PNG, atau WEBP. Maksimal 2 MB.
                </p>
            </div>

            <div class="mb-6">
                <label class="flex items-center gap-2 font-medium text-gray-700 cursor-pointer">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $perlengkapan->is_active) ? 'checked' : '' }}
                        class="h-4 w-4 rounded text-[#1a146b]"
                    >
                    Perlengkapan aktif dan tersedia untuk disewa pelanggan & kasir
                </label>
            </div>

            <div class="flex flex-wrap gap-3">
                <button
                    type="submit"
                    class="rounded-lg bg-[#1a146b] hover:bg-[#2b248a] px-5 py-2.5 font-semibold text-white transition shadow-sm"
                >
                    Simpan Perubahan
                </button>

                <a
                    href="{{ route('admin.perlengkapan.index') }}"
                    class="rounded-lg border border-gray-300 px-5 py-2.5 font-semibold text-gray-700 hover:bg-gray-100 transition"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
