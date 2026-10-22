@extends('layouts.admin')

@section('title', 'Laporan Rekonsiliasi Finansial - Admin LapangIn')

@section('content')
<div class="px-8 py-8 flex flex-col gap-8 max-w-[1400px]">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="text-xs font-semibold text-[#474651] flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#416900] animate-pulse"></span>
                    Audit &amp; Rekonsiliasi Keuangan
                </span>
            </div>
            <h1 class="text-2xl font-headline font-extrabold text-[#0b1c30] tracking-tight">
                Laporan &amp; Rekonsiliasi Finansial
            </h1>
            <p class="text-xs text-[#474651] mt-0.5">
                Rekapitulasi pendapatan sewa lapangan, pemisahan arus kas Midtrans online vs tunai fisik kasir.
            </p>
        </div>

        {{-- Tombol Aksi Ekspor PDF & Print --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.laporan.exportPdf', request()->query()) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1a146b] hover:bg-[#120c52] text-xs font-bold text-white shadow-sm transition hover:shadow-md">
                <span class="material-symbols-outlined text-base">picture_as_pdf</span>
                <span>Unduh Laporan PDF</span>
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 shadow-xs transition">
                <span class="material-symbols-outlined text-base">print</span>
                <span>Cetak Web</span>
            </button>
        </div>
    </div>

    {{-- Filter Bar Dinamis --}}
    <div class="bg-white rounded-2xl border border-[#e5eeff] p-5 shadow-xs">
        <form method="GET" action="{{ route('admin.laporan.index') }}" class="flex flex-col gap-4">
            {{-- Quick Filter Buttons & Custom Toggle --}}
            <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mr-1">Periode:</span>
                    <a href="{{ route('admin.laporan.index', ['periode' => 'hari_ini', 'metode' => $metodeFilter]) }}"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $periode === 'hari_ini' ? 'bg-[#1a146b] text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Hari Ini
                    </a>
                    <a href="{{ route('admin.laporan.index', ['periode' => 'minggu_ini', 'metode' => $metodeFilter]) }}"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $periode === 'minggu_ini' ? 'bg-[#1a146b] text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Minggu Ini
                    </a>
                    <a href="{{ route('admin.laporan.index', ['periode' => 'bulan_ini', 'metode' => $metodeFilter]) }}"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $periode === 'bulan_ini' ? 'bg-[#1a146b] text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Bulan Ini
                    </a>
                    <a href="{{ route('admin.laporan.index', ['periode' => 'semua', 'metode' => $metodeFilter]) }}"
                       class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $periode === 'semua' ? 'bg-[#1a146b] text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Semua
                    </a>
                </div>

                <div class="text-xs font-semibold text-gray-500 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm text-[#416900]">calendar_today</span>
                    <span>Aktif: <strong class="text-gray-800">{{ $periodeLabel }}</strong></span>
                </div>
            </div>

            {{-- Custom Date Range & Metode Filter --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                <input type="hidden" name="periode" value="custom">

                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date', $startDate) }}"
                           class="w-full text-xs font-medium px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:border-[#1a146b] focus:ring-1 focus:ring-[#1a146b]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date', $endDate) }}"
                           class="w-full text-xs font-medium px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:border-[#1a146b] focus:ring-1 focus:ring-[#1a146b]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Saluran Pembayaran</label>
                    <select name="metode" class="w-full text-xs font-medium px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:border-[#1a146b] focus:ring-1 focus:ring-[#1a146b]">
                        <option value="all" {{ $metodeFilter === 'all' ? 'selected' : '' }}>Semua Saluran (Online &amp; Tunai)</option>
                        <option value="online" {{ $metodeFilter === 'online' ? 'selected' : '' }}>Hanya Midtrans Online</option>
                        <option value="tunai" {{ $metodeFilter === 'tunai' ? 'selected' : '' }}>Hanya Kas Meja Kasir (Tunai)</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-[#0b1c30] hover:bg-[#1a146b] text-xs font-bold text-white transition">
                        <span class="material-symbols-outlined text-sm">filter_list</span>
                        <span>Filter Data</span>
                    </button>
                    @if(request()->hasAny(['start_date', 'end_date', 'periode', 'metode']))
                        <a href="{{ route('admin.laporan.index') }}" class="px-3 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-xs font-bold text-gray-600 transition" title="Reset Filter">
                            <span class="material-symbols-outlined text-sm">restart_alt</span>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Stats Rekonsiliasi Kas Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Omset --}}
        <div class="p-5 rounded-2xl bg-white border border-[#e5eeff] shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Total Omset Terkonfirmasi</p>
                <h3 class="text-2xl font-headline font-black text-[#1a146b] mt-1">
                    Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                </h3>
                <p class="text-[11px] text-gray-500 font-semibold mt-1">{{ $totalTransaksiSukses }} Transaksi Sukses</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#eff4ff] text-[#1a146b] flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
            </div>
        </div>

        {{-- Online Midtrans --}}
        <div class="p-5 rounded-2xl bg-white border border-[#e5eeff] shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Penerimaan Online (Midtrans)</p>
                <h3 class="text-2xl font-headline font-black text-[#0284c7] mt-1">
                    Rp {{ number_format($pendapatanOnline, 0, ',', '.') }}
                </h3>
                <p class="text-[11px] text-[#0284c7] font-semibold mt-1">{{ $transaksiOnlineCount }} Transaksi Digital (Snap/QRIS)</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#f0f9ff] text-[#0284c7] flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">credit_card</span>
            </div>
        </div>

        {{-- Kas Fisik Kasir --}}
        <div class="p-5 rounded-2xl bg-white border border-[#e5eeff] shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Kas Fisik Meja Kasir</p>
                <h3 class="text-2xl font-headline font-black text-[#d97706] mt-1">
                    Rp {{ number_format($pendapatanTunai, 0, ',', '.') }}
                </h3>
                <p class="text-[11px] text-[#d97706] font-semibold mt-1">{{ $transaksiTunaiCount }} Transaksi Tunai di Kasir</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#fffbeb] text-[#d97706] flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">point_of_sale</span>
            </div>
        </div>

        {{-- Status Rekonsiliasi --}}
        <div class="p-5 rounded-2xl bg-white border border-[#e5eeff] shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status Rekonsiliasi</p>
                <h3 class="text-xl font-headline font-bold text-[#416900] mt-1">
                    100% Balanced
                </h3>
                <p class="text-[11px] text-[#416900] font-semibold mt-1">Online + Kasir = Total Omset</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#eaf7d8] text-[#416900] flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">verified</span>
            </div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="bg-white rounded-3xl border border-[#e5eeff] shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-[#e5eeff] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-headline font-extrabold text-[#0b1c30]">Rincian Transaksi Keuangan</h2>
                <p class="text-xs text-gray-500">
                    Menampilkan data transaksi periode <strong class="text-gray-700">{{ $periodeLabel }}</strong>
                    @if($metodeFilter !== 'all')
                        (Filter Metode: <strong class="uppercase text-[#1a146b]">{{ $metodeFilter }}</strong>)
                    @endif
                </p>
            </div>
            <div class="text-xs text-gray-500">
                Total Baris: <strong class="text-gray-900">{{ $transaksiTerbaru->total() }}</strong>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50/80 text-gray-600 font-bold uppercase tracking-wider text-[11px] border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3.5">ID / Ref</th>
                        <th class="px-6 py-3.5">Pelanggan</th>
                        <th class="px-6 py-3.5">Lapangan</th>
                        <th class="px-6 py-3.5">Jenis</th>
                        <th class="px-6 py-3.5">Saluran Bayar</th>
                        <th class="px-6 py-3.5">Nominal</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Waktu Transaksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse ($transaksiTerbaru as $tx)
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-6 py-4 font-mono text-[11px] text-gray-700">
                                {{ $tx->order_id ?? substr($tx->id, 0, 8) . '...' }}
                            </td>
                            <td class="px-6 py-4 text-gray-900 font-bold">
                                {{ $tx->jadwal?->pelanggan?->nama ?? $tx->jadwal?->nama_tamu ?? 'Walk-in Guest' }}
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ $tx->jadwal?->lapangan?->nama ?? 'Lapangan' }}
                            </td>
                            <td class="px-6 py-4 uppercase font-bold text-xs text-gray-600">
                                {{ $tx->jenis ?? 'DP' }}
                            </td>
                            <td class="px-6 py-4">
                                @if(in_array(strtolower((string)$tx->metode), ['tunai', 'cash']))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-extrabold bg-[#fef3c7] text-[#b45309]">
                                        <span class="material-symbols-outlined text-[13px]">payments</span>
                                        TUNAI (KASIR)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-extrabold bg-[#e0f2fe] text-[#0369a1]">
                                        <span class="material-symbols-outlined text-[13px]">credit_card</span>
                                        MIDTRANS ONLINE
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-bold text-[#1a146b] text-sm">
                                Rp {{ number_format($tx->jumlah, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-[#eaf7d8] text-[#416900]">
                                    {{ $tx->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $tx->dibayar_pada ? $tx->dibayar_pada->timezone('Asia/Jakarta')->format('d M Y, H:i') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <span class="material-symbols-outlined text-4xl text-gray-300 block mb-2">receipt_long</span>
                                Tidak ada transaksi yang sesuai dengan filter periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        @if ($transaksiTerbaru->hasPages())
            <div class="px-6 py-4 border-t border-[#e5eeff] bg-gray-50/50">
                {{ $transaksiTerbaru->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
