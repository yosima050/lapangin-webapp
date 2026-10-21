@extends('layouts.admin')

@section('title', 'Laporan & Analitik - Admin LapangIn')

@section('content')
<div class="px-8 py-8 flex flex-col gap-8 max-w-[1400px]">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="text-xs font-semibold text-[#474651] flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#416900]"></span>
                    Laporan Finansial &amp; Operasional
                </span>
            </div>
            <h1 class="text-2xl font-headline font-extrabold text-[#0b1c30] tracking-tight">
                Laporan &amp; Analitik Transaksi
            </h1>
            <p class="text-xs text-[#474651] mt-0.5">
                Rekapitulasi pendapatan dan aktivitas transaksi arena {{ config('app.venue_name', 'LapangIn Venue') }}.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 shadow-xs transition">
                <span class="material-symbols-outlined text-base">print</span>
                <span>Cetak Rekap</span>
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div class="p-5 rounded-2xl bg-white border border-[#e5eeff] shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Pendapatan Sukses</p>
                <h3 class="text-2xl font-headline font-black text-[#1a146b] mt-1">
                    Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                </h3>
                <p class="text-[11px] text-[#416900] font-semibold mt-1">Dana terkonfirmasi otomatis</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#eff4ff] text-[#1a146b] flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">payments</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#e5eeff] shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Transaksi Selesai</p>
                <h3 class="text-2xl font-headline font-black text-[#0b1c30] mt-1">
                    {{ $totalTransaksiSukses }}
                </h3>
                <p class="text-[11px] text-gray-500 font-semibold mt-1">Midtrans &amp; Kasir Tunai</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#eaf7d8] text-[#416900] flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">verified</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#e5eeff] shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Status Integrasi POS</p>
                <h3 class="text-xl font-headline font-bold text-[#416900] mt-1">
                    Live Terhubung
                </h3>
                <p class="text-[11px] text-gray-500 font-semibold mt-1">Sinkronisasi Database Aktif</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#f8f9ff] text-[#416900] flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">sync</span>
            </div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="bg-white rounded-3xl border border-[#e5eeff] shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-[#e5eeff] flex items-center justify-between">
            <div>
                <h2 class="text-base font-headline font-extrabold text-[#0b1c30]">Aktivitas Transaksi Terbaru</h2>
                <p class="text-xs text-gray-500">Daftar transaksi sewa lapangan yang masuk ke sistem.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50/80 text-gray-600 font-bold uppercase tracking-wider text-[11px] border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3.5">ID / Ref</th>
                        <th class="px-6 py-3.5">Pelanggan</th>
                        <th class="px-6 py-3.5">Lapangan</th>
                        <th class="px-6 py-3.5">Metode</th>
                        <th class="px-6 py-3.5">Nominal</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse ($transaksiTerbaru as $tx)
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-6 py-4 font-mono text-[11px] text-gray-700">
                                {{ substr($tx->id, 0, 8) }}...
                            </td>
                            <td class="px-6 py-4 text-gray-900 font-bold">
                                {{ $tx->jadwal?->pelanggan?->nama ?? 'Walk-in Guest' }}
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ $tx->jadwal?->lapangan?->nama ?? 'Lapangan' }}
                            </td>
                            <td class="px-6 py-4 uppercase font-bold text-gray-600">
                                {{ $tx->metode ?? 'Online' }}
                            </td>
                            <td class="px-6 py-4 font-bold text-[#1a146b]">
                                Rp {{ number_format($tx->jumlah, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider {{ $tx->status === 'settlement' ? 'bg-[#eaf7d8] text-[#416900]' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $tx->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $tx->dibayar_pada ? $tx->dibayar_pada->timezone('Asia/Jakarta')->format('d M Y, H:i') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                Belum ada transaksi yang tercatat dalam sistem.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
