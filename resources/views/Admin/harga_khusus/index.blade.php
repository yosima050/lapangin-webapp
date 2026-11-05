@extends('layouts.admin')

@section('title', 'Pengaturan Harga Khusus - Admin LapangIn')

@section('content')
<div class="w-full px-8 py-8 flex flex-col gap-6"
    x-data="{
        editModalOpen: false,
        editData: {
            id: '',
            lapangan_id: '',
            tanggal: '',
            jam_mulai: '',
            jam_selesai: '',
            harga: '',
            keterangan: '',
            is_active: true
        }
    }">

    {{-- Header --}}
    <div>
        <span class="text-xs font-semibold text-[#416900]">
            PENGATURAN TARIF
        </span>
        <h1 class="text-2xl lg:text-3xl font-extrabold text-[#0b1c30] mt-2">
            Pengaturan Harga Khusus
        </h1>
        <p class="text-sm text-[#474651] mt-1">
            Atur tarif khusus berdasarkan tanggal dan rentang jam tanpa mengubah harga master.
        </p>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
            <p class="font-bold mb-2">Periksa kembali data berikut:</p>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form tambah harga khusus --}}
    <div class="bg-white rounded-2xl shadow-sm border border-[#e5eeff] overflow-hidden">
        <div class="px-6 py-5 border-b border-[#e5eeff]">
            <h2 class="text-lg font-bold text-[#0b1c30]">
                Tambah Harga Khusus
            </h2>
            <p class="text-sm text-[#777682] mt-1">
                Tentukan lapangan, tanggal, tarif, dan jam berlakunya harga khusus.
            </p>
        </div>

        <form action="{{ route('admin.harga-khusus.store') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">

                <div>
                    <label for="lapangan_id" class="block text-sm font-semibold text-[#474651] mb-2">
                        Lapangan
                    </label>
                    <select name="lapangan_id" id="lapangan_id" required
                        class="w-full rounded-xl border border-[#d8deeb] bg-white px-4 py-3 text-sm text-[#0b1c30] focus:outline-none focus:ring-2 focus:ring-[#1a146b]">
                        <option value="">Pilih lapangan</option>
                        <option value="all" class="font-bold text-[#1a146b]" @selected(old('lapangan_id') == 'all')>⭐ Semua Lapangan (Terapkan ke Semua)</option>
                        @foreach ($lapangans as $lapangan)
                            <option value="{{ $lapangan->id }}"
                                @selected(old('lapangan_id') == $lapangan->id)>
                                {{ $lapangan->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tanggal" class="block text-sm font-semibold text-[#474651] mb-2">
                        Tanggal
                    </label>
                    <input type="date" name="tanggal" id="tanggal"
                        value="{{ old('tanggal') }}" required
                        class="w-full rounded-xl border border-[#d8deeb] bg-white px-4 py-3 text-sm text-[#0b1c30] focus:outline-none focus:ring-2 focus:ring-[#1a146b]">
                </div>

                <div>
                    <label for="harga" class="block text-sm font-semibold text-[#474651] mb-2">
                        Tarif (Rp)
                    </label>
                    <input type="number" name="harga" id="harga"
                        min="0" max="9999999999.99" step="0.01"
                        value="{{ old('harga') }}"
                        placeholder="Contoh: 175000" required
                        class="w-full rounded-xl border border-[#d8deeb] bg-white px-4 py-3 text-sm text-[#0b1c30] focus:outline-none focus:ring-2 focus:ring-[#1a146b]">
                </div>

                <div>
                    <label for="jam_mulai" class="block text-sm font-semibold text-[#474651] mb-2">
                        Jam Mulai
                    </label>
                    <input type="time" name="jam_mulai" id="jam_mulai"
                        value="{{ old('jam_mulai') }}" required
                        class="w-full rounded-xl border border-[#d8deeb] bg-white px-4 py-3 text-sm text-[#0b1c30] focus:outline-none focus:ring-2 focus:ring-[#1a146b]">
                </div>

                <div>
                    <label for="jam_selesai" class="block text-sm font-semibold text-[#474651] mb-2">
                        Jam Selesai
                    </label>
                    <input type="time" name="jam_selesai" id="jam_selesai"
                        value="{{ old('jam_selesai') }}" required
                        class="w-full rounded-xl border border-[#d8deeb] bg-white px-4 py-3 text-sm text-[#0b1c30] focus:outline-none focus:ring-2 focus:ring-[#1a146b]">
                </div>

                <div>
                    <label for="keterangan" class="block text-sm font-semibold text-[#474651] mb-2">
                        Keterangan (opsional)
                    </label>
                    <input type="text" name="keterangan" id="keterangan"
                        maxlength="150" value="{{ old('keterangan') }}"
                        placeholder="Contoh: Hari libur nasional"
                        class="w-full rounded-xl border border-[#d8deeb] bg-white px-4 py-3 text-sm text-[#0b1c30] focus:outline-none focus:ring-2 focus:ring-[#1a146b]">
                </div>

            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#1a146b] px-5 py-3 text-sm font-bold text-white hover:bg-[#312e81] transition-colors">
                    <span class="material-symbols-outlined text-[#acf847] text-xl">save</span>
                    Simpan Harga Khusus
                </button>
            </div>
        </form>
    </div>

    {{-- Daftar aturan harga --}}
    <div class="bg-white rounded-2xl shadow-sm border border-[#e5eeff] overflow-hidden">
        <div class="px-6 py-5 border-b border-[#e5eeff]">
            <h2 class="text-lg font-bold text-[#0b1c30]">
                Daftar Aturan Harga
            </h2>
            <p class="text-sm text-[#777682] mt-1">
                Kelola tarif khusus yang telah terdaftar.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-[#eff4ff] text-[#474651]">
                    <tr>
                        <th class="px-5 py-4 font-bold">Lapangan</th>
                        <th class="px-5 py-4 font-bold">Tanggal</th>
                        <th class="px-5 py-4 font-bold">Rentang Jam</th>
                        <th class="px-5 py-4 font-bold">Tarif</th>
                        <th class="px-5 py-4 font-bold">Keterangan</th>
                        <th class="px-5 py-4 font-bold">Status</th>
                        <th class="px-5 py-4 font-bold">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#e5eeff]">
                    @forelse ($hargaKhusus as $item)
                        <tr class="hover:bg-[#f8faff]">
                            <td class="px-5 py-4 font-semibold text-[#0b1c30]">
                                {{ $item->lapangan?->nama ?? 'Lapangan tidak tersedia' }}
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                {{ $item->tanggal->format('d-m-Y') }}
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                {{ substr($item->jam_mulai, 0, 5) }}
                                – {{ substr($item->jam_selesai, 0, 5) }}
                            </td>

                            <td class="px-5 py-4 font-bold text-[#0b1c30] whitespace-nowrap">
                                Rp {{ number_format((float) $item->harga, 0, ',', '.') }}
                            </td>

                            <td class="px-5 py-4 text-[#777682]">
                                {{ $item->keterangan ?: '—' }}
                            </td>

                            <td class="px-5 py-4">
                                @if ($item->is_active)
                                    <span class="inline-flex px-3 py-1 rounded-full bg-[#acf847] text-[#102000] text-xs font-bold">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                        @click="editData = {
                                            id: '{{ $item->id }}',
                                            lapangan_id: '{{ $item->lapangan_id }}',
                                            tanggal: '{{ $item->tanggal->format('Y-m-d') }}',
                                            jam_mulai: '{{ substr($item->jam_mulai, 0, 5) }}',
                                            jam_selesai: '{{ substr($item->jam_selesai, 0, 5) }}',
                                            harga: '{{ (float) $item->harga }}',
                                            keterangan: '{{ addslashes($item->keterangan ?? '') }}',
                                            is_active: {{ $item->is_active ? 'true' : 'false' }}
                                        }; editModalOpen = true;"
                                        class="whitespace-nowrap rounded-lg bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-100 transition-colors">
                                        Edit
                                    </button>

                                    <form
                                        action="{{ route('admin.harga-khusus.toggle-status', $item->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                            class="whitespace-nowrap rounded-lg px-3 py-2 text-xs font-bold transition-colors {{ $item->is_active ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                                            {{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    <form
                                        action="{{ route('admin.harga-khusus.destroy', $item->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus aturan harga ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            class="whitespace-nowrap rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100 transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-[#777682]">
                                Belum ada aturan harga khusus.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Edit Harga Khusus --}}
    <div x-show="editModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
        <div @click.away="editModalOpen = false"
             class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-2xl w-full p-6 transition-all transform">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-[#0b1c30]">
                    Edit Aturan Harga Khusus
                </h3>
                <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form :action="'{{ url('admin/harga-khusus') }}/' + editData.id" method="POST" class="mt-5 space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#474651] mb-1.5">Lapangan</label>
                        <select name="lapangan_id" x-model="editData.lapangan_id" required
                                class="w-full rounded-xl border border-[#d8deeb] bg-white px-3 py-2.5 text-sm text-[#0b1c30] focus:ring-2 focus:ring-[#1a146b] outline-none">
                            <option value="">Pilih lapangan</option>
                            @foreach ($lapangans as $lapangan)
                                <option value="{{ $lapangan->id }}">{{ $lapangan->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#474651] mb-1.5">Tanggal</label>
                        <input type="date" name="tanggal" x-model="editData.tanggal" required
                               class="w-full rounded-xl border border-[#d8deeb] bg-white px-3 py-2.5 text-sm text-[#0b1c30] focus:ring-2 focus:ring-[#1a146b] outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#474651] mb-1.5">Jam Mulai</label>
                        <input type="time" name="jam_mulai" x-model="editData.jam_mulai" required
                               class="w-full rounded-xl border border-[#d8deeb] bg-white px-3 py-2.5 text-sm text-[#0b1c30] focus:ring-2 focus:ring-[#1a146b] outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#474651] mb-1.5">Jam Selesai</label>
                        <input type="time" name="jam_selesai" x-model="editData.jam_selesai" required
                               class="w-full rounded-xl border border-[#d8deeb] bg-white px-3 py-2.5 text-sm text-[#0b1c30] focus:ring-2 focus:ring-[#1a146b] outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#474651] mb-1.5">Tarif (Rp)</label>
                        <input type="number" name="harga" x-model="editData.harga" min="0" step="0.01" required
                               class="w-full rounded-xl border border-[#d8deeb] bg-white px-3 py-2.5 text-sm text-[#0b1c30] focus:ring-2 focus:ring-[#1a146b] outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#474651] mb-1.5">Keterangan (opsional)</label>
                        <input type="text" name="keterangan" x-model="editData.keterangan" maxlength="150"
                               class="w-full rounded-xl border border-[#d8deeb] bg-white px-3 py-2.5 text-sm text-[#0b1c30] focus:ring-2 focus:ring-[#1a146b] outline-none">
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="editModalOpen = false"
                            class="px-4 py-2.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-[#1a146b] text-xs font-bold text-white hover:bg-[#312e81] transition-colors inline-flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-[#acf847]">check</span>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
