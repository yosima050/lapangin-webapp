
@extends('layouts.app')

@section('title', 'Data Transaksi')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Data Transaksi
        </h1>
        <p class="mt-1 text-sm text-gray-500">
            Monitor transaksi pembayaran LapangIn.
        </p>
    </div>

    <div class="overflow-x-auto rounded-xl bg-white shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-6 py-4">No.</th>
                    <th class="px-6 py-4">Nama Pelanggan</th>
                    <th class="px-6 py-4">Lapangan</th>
                    <th class="px-6 py-4">Tanggal Main</th>
                    <th class="px-6 py-4">Status Pembayaran</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse ($transaksis as $transaksi)
                    @php
                        $jadwal = $transaksi->jadwal;
                        $pelanggan = $jadwal?->pelanggan;
                        $status = strtolower($transaksi->status ?? 'pending');

                        $badgeClass = match ($status) {
                            'berhasil' => 'bg-green-100 text-green-700',
                            'pending' => 'bg-yellow-100 text-yellow-700',
                            'gagal', 'expired', 'expire' => 'bg-red-100 text-red-700',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp

                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            {{ $transaksis->firstItem() + $loop->index }}
                        </td>

                        <td class="px-6 py-4 font-medium text-gray-800">
                            {{ $pelanggan?->nama ?? $jadwal?->nama_tamu ?? 'Pelanggan tidak diketahui' }}
                        </td>

                        <td class="px-6 py-4">
                            {{ $jadwal?->lapangan?->nama ?? '-' }}
                        </td>

                        <td class="px-6 py-4">
                            {{ $jadwal?->tanggal ?? '-' }}
                        </td>

                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                            Belum ada transaksi yang tersedia.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $transaksis->links() }}
    </div>
</div>
@endsection
