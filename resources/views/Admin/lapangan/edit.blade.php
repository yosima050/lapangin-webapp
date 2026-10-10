@extends('layouts.admin')

@section('title', 'Edit Lapangan - Admin LapangIn')
@section('page-title', 'Edit Lapangan')

@section('content')
<div class="w-full px-8 py-8 max-w-5xl mx-auto flex flex-col gap-6">

    {{-- Breadcrumb & Back --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.lapangan.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#474651] hover:text-[#1a146b] transition-colors">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Master Data Lapangan</span>
        </a>
        <span class="px-2.5 py-1 rounded-full text-[10px] font-mono text-[#777682] bg-[#eff4ff] border border-[#dce9ff]">
            UUID: {{ $lapangan->id }}
        </span>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-2xl border border-[#e5eeff] shadow-sm p-8">
        <form action="{{ route('admin.lapangan.update', $lapangan->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            {{-- Bagian 1: Data Utama Lapangan --}}
            <div>
                <div class="border-b border-[#eff4ff] pb-3 flex items-center justify-between">
                    <h2 class="text-base font-headline font-extrabold text-[#0b1c30] flex items-center gap-2">
                        <span class="material-symbols-outlined text-xl text-[#1a146b]">sports_soccer</span>
                        <span>Informasi Utama Lapangan</span>
                    </h2>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#777682]">FR-12 UPDATE</span>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-5 text-xs">

                    {{-- Nama Lapangan --}}
                    <div class="md:col-span-2">
                        <label for="nama" class="block font-bold text-[#0b1c30] uppercase text-[11px] tracking-wider mb-1.5">
                            Nama Lapangan <span class="text-[#ba1a1a]">*</span>
                        </label>
                        <input type="text" name="nama" id="nama" value="{{ old('nama', $lapangan->nama) }}" required
                               class="w-full rounded-xl bg-[#eff4ff] border border-[#e5eeff] px-4 py-3 text-xs font-semibold text-[#0b1c30] focus:bg-white focus:ring-2 focus:ring-[#acf847] outline-none transition @error('nama') border-[#ba1a1a] @enderror">
                        @error('nama')
                            <p class="text-xs text-[#ba1a1a] mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kategori --}}
                    <div>
                        <label for="kategori" class="block font-bold text-[#0b1c30] uppercase text-[11px] tracking-wider mb-1.5">
                            Kategori Cabang Olahraga <span class="text-[#ba1a1a]">*</span>
                        </label>
                        <select name="kategori" id="kategori" required
                                class="w-full rounded-xl bg-[#eff4ff] border border-[#e5eeff] px-4 py-3 text-xs font-semibold text-[#0b1c30] focus:bg-white focus:ring-2 focus:ring-[#acf847] outline-none transition @error('kategori') border-[#ba1a1a] @enderror">
                            @foreach (['Futsal', 'Badminton', 'Mini Soccer', 'Basket', 'Tenis', 'Padel'] as $cat)
                                <option value="{{ $cat }}" {{ old('kategori', $lapangan->kategori) == $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                        @error('kategori')
                            <p class="text-xs text-[#ba1a1a] mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jam Buka & Jam Tutup --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="jam_buka" class="block font-bold text-[#0b1c30] uppercase text-[11px] tracking-wider mb-1.5">
                                Jam Buka <span class="text-[#ba1a1a]">*</span>
                            </label>
                            <input type="time" name="jam_buka" id="jam_buka" value="{{ old('jam_buka', substr($lapangan->jam_buka, 0, 5)) }}" required
                                   class="w-full rounded-xl bg-[#eff4ff] border border-[#e5eeff] px-3 py-3 text-xs font-semibold text-[#0b1c30] focus:bg-white focus:ring-2 focus:ring-[#acf847] outline-none transition @error('jam_buka') border-[#ba1a1a] @enderror">
                            @error('jam_buka')
                                <p class="text-xs text-[#ba1a1a] mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jam_tutup" class="block font-bold text-[#0b1c30] uppercase text-[11px] tracking-wider mb-1.5">
                                Jam Tutup <span class="text-[#ba1a1a]">*</span>
                            </label>
                            <input type="time" name="jam_tutup" id="jam_tutup" value="{{ old('jam_tutup', substr($lapangan->jam_tutup, 0, 5)) }}" required
                                   class="w-full rounded-xl bg-[#eff4ff] border border-[#e5eeff] px-3 py-3 text-xs font-semibold text-[#0b1c30] focus:bg-white focus:ring-2 focus:ring-[#acf847] outline-none transition @error('jam_tutup') border-[#ba1a1a] @enderror">
                            @error('jam_tutup')
                                <p class="text-xs text-[#ba1a1a] mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Foto Saat Ini & Upload Baru --}}
                    <div class="md:col-span-2">
                        <label class="block font-bold text-[#0b1c30] uppercase text-[11px] tracking-wider mb-2">
                            Foto Lapangan
                        </label>
                        <div class="flex items-center gap-4 mb-3">
                            <img src="{{ $lapangan->foto_url }}" alt="{{ $lapangan->nama }}" class="w-16 h-16 rounded-2xl object-cover border border-[#e5eeff] shadow-xs">
                            <div>
                                <p class="text-xs font-bold text-[#0b1c30]">Foto Saat Ini</p>
                                <p class="text-[11px] text-[#777682]">Pilih file baru di bawah jika ingin mengganti gambar.</p>
                            </div>
                        </div>
                        <input type="file" name="foto" id="foto" accept="image/jpeg,image/png,image/jpg,image/webp"
                               class="w-full rounded-xl bg-[#eff4ff] border border-[#e5eeff] px-4 py-2.5 text-xs file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#1a146b] file:text-white hover:file:bg-[#312e81] transition cursor-pointer @error('foto') border-[#ba1a1a] @enderror">
                        @error('foto')
                            <p class="text-xs text-[#ba1a1a] mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div class="md:col-span-2">
                        <label for="deskripsi" class="block font-bold text-[#0b1c30] uppercase text-[11px] tracking-wider mb-1.5">
                            Deskripsi &amp; Fasilitas
                        </label>
                        <textarea name="deskripsi" id="deskripsi" rows="3"
                                  class="w-full rounded-xl bg-[#eff4ff] border border-[#e5eeff] px-4 py-3 text-xs font-medium text-[#0b1c30] focus:bg-white focus:ring-2 focus:ring-[#acf847] outline-none transition">{{ old('deskripsi', $lapangan->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <p class="text-xs text-[#ba1a1a] mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- Bagian 2: Tarif Dasar --}}
            <div>
                <div class="border-b border-[#eff4ff] pb-3 flex items-center justify-between">
                    <h2 class="text-base font-headline font-extrabold text-[#0b1c30] flex items-center gap-2">
                        <span class="material-symbols-outlined text-xl text-[#1a146b]">payments</span>
                        <span>Tarif Dasar (Harga Master)</span>
                    </h2>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-5 text-xs">

                    {{-- Tarif Weekday --}}
                    <div class="bg-[#eff4ff] p-5 rounded-2xl border border-[#dce9ff]">
                        <label for="harga_weekday" class="block font-bold text-[#0b1c30] uppercase text-[11px] tracking-wider mb-1.5">
                            Tarif Weekday (Senin – Jumat)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-[#777682]">Rp</span>
                            <input type="number" name="harga_weekday" id="harga_weekday" value="{{ old('harga_weekday', (int)$hargaWeekday) }}" min="0"
                                   class="w-full pl-11 pr-4 py-2.5 rounded-xl bg-white border border-[#dce9ff] text-xs font-bold text-[#0b1c30] focus:ring-2 focus:ring-[#acf847] outline-none transition @error('harga_weekday') border-[#ba1a1a] @enderror">
                        </div>
                        @error('harga_weekday')
                            <p class="text-xs text-[#ba1a1a] mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tarif Weekend --}}
                    <div class="bg-[#eff4ff] p-5 rounded-2xl border border-[#dce9ff]">
                        <label for="harga_weekend" class="block font-bold text-[#0b1c30] uppercase text-[11px] tracking-wider mb-1.5">
                            Tarif Weekend (Sabtu – Minggu)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-[#777682]">Rp</span>
                            <input type="number" name="harga_weekend" id="harga_weekend" value="{{ old('harga_weekend', (int)$hargaWeekend) }}" min="0"
                                   class="w-full pl-11 pr-4 py-2.5 rounded-xl bg-white border border-[#dce9ff] text-xs font-bold text-[#0b1c30] focus:ring-2 focus:ring-[#acf847] outline-none transition @error('harga_weekend') border-[#ba1a1a] @enderror">
                        </div>
                        @error('harga_weekend')
                            <p class="text-xs text-[#ba1a1a] mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- Bagian 3: Tarif Khusus Tanggal Merah --}}
            <div class="bg-[#eff4ff] p-6 rounded-2xl border border-[#dce9ff]">
                <h3 class="text-xs font-bold text-[#0b1c30] flex items-center gap-1.5 border-b border-[#dce9ff] pb-3">
                    <span class="material-symbols-outlined text-base text-[#1a146b]">event</span>
                    <span>Tarif Khusus Tanggal Merah (Opsional)</span>
                </h3>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label for="tanggal_khusus" class="block font-bold text-[#0b1c30] mb-1">
                            Tanggal Merah / Libur
                        </label>
                        <input type="date" name="tanggal_khusus" id="tanggal_khusus"
                               value="{{ old('tanggal_khusus', $hargaKhusus?->tanggal_khusus?->format('Y-m-d')) }}"
                               class="w-full rounded-xl border border-[#dce9ff] px-4 py-2.5 text-xs bg-white text-[#0b1c30] font-semibold focus:ring-2 focus:ring-[#acf847] outline-none transition @error('tanggal_khusus') border-[#ba1a1a] @enderror">
                    </div>

                    <div>
                        <label for="harga_tanggal_merah" class="block font-bold text-[#0b1c30] mb-1">
                            Tarif Khusus Per Jam (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-[#777682]">Rp</span>
                            <input type="number" name="harga_tanggal_merah" id="harga_tanggal_merah"
                                   value="{{ old('harga_tanggal_merah', $hargaKhusus ? (int)$hargaKhusus->harga : '') }}" min="0"
                                   placeholder="200000"
                                   class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-[#dce9ff] bg-white text-xs font-bold text-[#0b1c30] focus:ring-2 focus:ring-[#acf847] outline-none transition @error('harga_tanggal_merah') border-[#ba1a1a] @enderror">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#eff4ff]">
                <a href="{{ route('admin.lapangan.index') }}"
                   class="px-5 py-2.5 rounded-xl border border-[#dce9ff] text-xs font-bold text-[#474651] hover:bg-[#eff4ff] transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-[#1a146b] hover:bg-[#312e81] text-white text-xs font-bold shadow-md transition-all cursor-pointer">
                    Perbarui Data Lapangan
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
