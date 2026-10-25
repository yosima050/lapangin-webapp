  @extends('layouts.admin')

@section('title', 'Kelola Perlengkapan - Admin LapangIn')

@section('content')
<div class="w-full px-8 py-8 flex flex-col gap-6">

    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-semibold text-[#416900]">
                MASTER DATA PERLENGKAPAN
            </span>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-[#0b1c30] mt-2">
                Kelola Perlengkapan Tambahan
            </h1>
            <p class="text-sm text-[#474651] mt-1">
                Atur daftar perlengkapan dan harga sewa untuk transaksi kasir.
            </p>
        </div>

        <a href="{{ route('admin.perlengkapan.create') }}"
           class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#1a146b] hover:bg-[#312e81] text-white font-bold text-sm">
            <span class="material-symbols-outlined text-[#acf847]">add_circle</span>
            Tambah Perlengkapan
        </a>
    </div>

    {{-- Pesan sukses --}}
    @if (session('success'))
        <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Pesan error --}}
    @if ($errors->any())
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="p-5 rounded-2xl bg-white shadow-sm border border-[#e5eeff]">
            <span class="text-xs font-bold tracking-wider text-[#777682]">
                TOTAL PERLENGKAPAN
            </span>
            <div class="text-3xl font-black text-[#0b1c30] mt-2">
                {{ $perlengkapans->total() }}
            </div>
            <p class="text-xs text-[#777682] mt-2">
                Data perlengkapan terdaftar
            </p>
        </div>

        <div class="p-5 rounded-2xl bg-white shadow-sm border border-[#e5eeff]">
            <span class="text-xs font-bold tracking-wider text-[#777682]">
                PERLENGKAPAN AKTIF DI HALAMAN INI
            </span>
            <div class="text-3xl font-black text-[#416900] mt-2">
                {{ $perlengkapans->where('is_active', true)->count() }}
            </div>
            <p class="text-xs text-[#777682] mt-2">
                Siap dipilih oleh petugas kasir
            </p>
        </div>
    </div>

    {{-- Tabel perlengkapan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-[#e5eeff] overflow-hidden">
        <div class="px-5 py-4 border-b border-[#e5eeff]">
            <h2 class="font-bold text-[#0b1c30]">
                Daftar Perlengkapan
            </h2>
            <p class="text-xs text-[#777682] mt-1">
                Harga di sini menjadi acuan untuk penyewaan perlengkapan tambahan.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-[#eff4ff] text-[#474651]">
                    <tr>
                        <th class="px-5 py-4">No.</th>
                        <th class="px-5 py-4">Gambar</th>
                        <th class="px-5 py-4">Nama Perlengkapan</th>
                        <th class="px-5 py-4">Deskripsi</th>
                        <th class="px-5 py-4">Harga Sewa</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#e5eeff]">
                    @forelse ($perlengkapans as $item)
                        <tr class="hover:bg-[#f8faff]">

                            {{-- Nomor --}}
                            <td class="px-5 py-4">
                                {{ $perlengkapans->firstItem() + $loop->index }}
                            </td>

                            {{-- Gambar --}}
                            <td class="px-5 py-4">
                                @if ($item->gambar)
                                    <img
                                        src="{{ asset('storage/' . $item->gambar) }}"
                                        alt="{{ $item->nama }}"
                                        class="h-16 w-16 rounded-lg object-cover"
                                    >
                                @else
                                    <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-gray-100 text-gray-400">
                                        <span class="material-symbols-outlined">image</span>
                                    </div>
                                @endif
                            </td>

                            {{-- Nama --}}
                            <td class="px-5 py-4 font-bold text-[#0b1c30]">
                                {{ $item->nama }}
                            </td>

                            {{-- Deskripsi --}}
                            <td class="px-5 py-4 text-[#777682]">
                                {{ $item->deskripsi ?: '-' }}
                            </td>

                            {{-- Harga --}}
                            <td class="px-5 py-4 font-bold text-[#0b1c30]">
                                Rp {{ number_format((float) $item->harga, 0, ',', '.') }}
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4">
                                @if ($item->is_active)
                                    <span class="px-3 py-1 rounded-full bg-[#acf847] text-[#102000] text-xs font-bold">
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">

                                    {{-- Tombol Edit --}}
                                    <a href="{{ route('admin.perlengkapan.edit', $item) }}"
                                       class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-[#eff4ff] text-[#1a146b] font-bold text-xs hover:bg-[#dce9ff]">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                        Edit
                                    </a>

                                    {{-- Tombol Hapus --}}
                                    <form action="{{ route('admin.perlengkapan.destroy', $item) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus perlengkapan ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-red-50 text-red-700 font-bold text-xs hover:bg-red-100">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-gray-500">
                                Belum ada data perlengkapan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-5 py-4">
            {{ $perlengkapans->links() }}
        </div>
    </div>

</div>
@endsection
