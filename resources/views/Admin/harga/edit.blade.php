@extends('layouts.admin')

@section('title', 'Edit Tarif Master - Admin LapangIn')
@section('page-title', 'Edit Aturan Tarif Master')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">

        <div class="flex items-center justify-between">
            <a href="{{ route('admin.harga.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-indigo-900 transition">
                ← Kembali ke Manajemen Harga
            </a>
            <span class="text-xs font-mono text-gray-400">ID: {{ $hargaMaster->id }}</span>
        </div>

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8">
            <form action="{{ route('admin.harga.update', $hargaMaster->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="lapangan_id" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                        Pilih Lapangan <span class="text-red-500">*</span>
                    </label>
                    <select name="lapangan_id" id="lapangan_id" required
                            class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none transition @error('lapangan_id') border-red-500 @enderror">
                        @foreach ($lapangans as $lap)
                            <option value="{{ $lap->id }}" {{ old('lapangan_id', $hargaMaster->lapangan_id) == $lap->id ? 'selected' : '' }}>
                                {{ $lap->nama }} ({{ $lap->kategori }})
                            </option>
                        @endforeach
                    </select>
                    @error('lapangan_id')
                        <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="jenis_hari" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                            Jenis Hari <span class="text-red-500">*</span>
                        </label>
                        <select name="jenis_hari" id="jenis_hari" required
                                class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none transition @error('jenis_hari') border-red-500 @enderror">
                            <option value="weekday" {{ old('jenis_hari', $hargaMaster->jenis_hari) == 'weekday' ? 'selected' : '' }}>Weekday (Senin - Jumat)</option>
                            <option value="weekend" {{ old('jenis_hari', $hargaMaster->jenis_hari) == 'weekend' ? 'selected' : '' }}>Weekend (Sabtu - Minggu)</option>
                        </select>
                        @error('jenis_hari')
                            <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_khusus" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                            Tanggal Merah (Opsional)
                        </label>
                        <input type="date" name="tanggal_khusus" id="tanggal_khusus"
                               value="{{ old('tanggal_khusus', $hargaMaster->tanggal_khusus?->format('Y-m-d')) }}"
                               class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none transition @error('tanggal_khusus') border-red-500 @enderror">
                        @error('tanggal_khusus')
                            <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="jam_mulai" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="jam_mulai" id="jam_mulai"
                               value="{{ old('jam_mulai', substr($hargaMaster->jam_mulai, 0, 5)) }}" required
                               class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none transition @error('jam_mulai') border-red-500 @enderror">
                        @error('jam_mulai')
                            <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jam_selesai" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                            Jam Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="jam_selesai" id="jam_selesai"
                               value="{{ old('jam_selesai', substr($hargaMaster->jam_selesai, 0, 5)) }}" required
                               class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none transition @error('jam_selesai') border-red-500 @enderror">
                        @error('jam_selesai')
                            <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="harga" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                        Tarif Harga (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-2 relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-sm font-bold text-gray-400">Rp</span>
                        <input type="number" name="harga" id="harga"
                               value="{{ old('harga', (int)$hargaMaster->harga) }}" min="0" required
                               class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 text-sm font-semibold focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none transition @error('harga') border-red-500 @enderror">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Input harga divalidasi tidak boleh bernilai minus.</p>
                    @error('harga')
                        <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                    <a href="{{ route('admin.harga.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-900 hover:bg-indigo-800 text-white font-bold text-sm rounded-xl transition shadow">
                        Perbarui Aturan Tarif
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection
