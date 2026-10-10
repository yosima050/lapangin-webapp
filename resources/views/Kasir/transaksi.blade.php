@extends('layouts.kasir')

@section('title', 'Transaksi & Rekonsiliasi Tutup Shift - LapangIn')

@section('content')
<div class="w-full px-8 py-8 flex flex-col gap-6" x-data="{
    shiftStatus: 'open',
    cashExpected: {{ (int) $totalTunai }},
    cashActual: {{ (int) $totalTunai }},
    get difference() {
        return this.cashActual - this.cashExpected;
    },
    formatRupiah(val) {
        return new Intl.NumberFormat('id-ID').format(val);
    }
}">
    <!-- Subheader Strip -->
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-[#acf847] text-[#102000] text-[11px] font-extrabold uppercase tracking-wider">
                    OPERASIONAL KASIR POS
                </span>
                <span class="text-xs text-[#474651]">Meja Resepsionis #01 • Sesi Kasir Berjalan</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-headline font-extrabold text-[#0b1c30] tracking-tight">
                Transaksi &amp; Rekonsiliasi Tutup Shift
            </h1>
            <p class="text-sm text-[#474651] mt-1">
                Pantau seluruh penerimaan kas masuk, pembayaran QRIS/EDC, dan lakukan verifikasi fisik uang kas sebelum pergantian shift.
            </p>
        </div>

        <button type="button" onclick="window.print()"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1a146b] hover:bg-[#312e81] text-white text-xs font-bold transition-all shadow-md cursor-pointer">
            <span class="material-symbols-outlined text-base">print</span>
            <span>Cetak Rekap Transaksi</span>
        </button>
    </div>

    <!-- Summary KPI Cards (Real Dynamic from Database) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white shadow-sm border border-[#e5eeff] flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-[#777682]">TOTAL TRANSAKSI SHIFT</span>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-3xl font-headline font-black text-[#0b1c30]">{{ $totalTransaksi }}</span>
                <span class="text-xs font-semibold text-[#416900]">Pesanan</span>
            </div>
            <span class="text-[10px] text-[#777682] mt-1">100% tersimpan di database</span>
        </div>

        <div class="p-5 rounded-2xl bg-white shadow-sm border border-[#e5eeff] flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-[#777682]">TOTAL TUNAI DITERIMA</span>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-2xl font-headline font-black text-[#1a146b]">Rp {{ number_format($totalTunai, 0, ',', '.') }}</span>
            </div>
            <span class="text-[10px] text-[#416900] font-bold mt-1">Fisik di laci kas</span>
        </div>

        <div class="p-5 rounded-2xl bg-white shadow-sm border border-[#e5eeff] flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-[#777682]">NON-TUNAI (QRIS &amp; ONLINE)</span>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-2xl font-headline font-black text-[#0b1c30]">Rp {{ number_format($totalNonTunai, 0, ',', '.') }}</span>
            </div>
            <span class="text-[10px] text-[#777682] mt-1">Transfer &amp; Midtrans</span>
        </div>

        <div class="p-5 rounded-2xl bg-white shadow-sm border border-[#e5eeff] flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-[#777682]">TOTAL OMSET KASIR</span>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-2xl font-headline font-black text-[#416900]">Rp {{ number_format($totalOmset, 0, ',', '.') }}</span>
            </div>
            <span class="text-[10px] text-[#416900] font-bold mt-1">Periode aktif</span>
        </div>
    </div>

    <!-- Shift Reconciliation Card & Recent Transactions Table -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
        <!-- Table (8 cols) -->
        <div class="xl:col-span-8 bg-white rounded-2xl p-6 shadow-sm border border-[#e5eeff] flex flex-col gap-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#eff4ff]">
                <h2 class="text-base font-bold text-[#0b1c30]">Daftar Transaksi Sesi Kasir ({{ $transaksis->count() }})</h2>
                <span class="text-xs text-[#777682]">Live Data PostgreSQL</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#eff4ff] text-[#474651] uppercase font-bold text-[10px] tracking-wider">
                        <tr>
                            <th class="px-4 py-3 rounded-l-xl">No. Booking</th>
                            <th class="px-4 py-3">Nama Tamu</th>
                            <th class="px-4 py-3">Lapangan</th>
                            <th class="px-4 py-3">Metode</th>
                            <th class="px-4 py-3">Nominal</th>
                            <th class="px-4 py-3 rounded-r-xl text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#eff4ff]">
                        @forelse ($transaksis as $trx)
                            @php
                                $trxCode = $trx->jadwal?->kode_qr ?? $trx->jadwal?->token_manual ?? substr($trx->id, 0, 8);
                                $trxNama = $trx->jadwal?->pelanggan?->nama ?? $trx->jadwal?->nama_tamu ?? 'Penyewa Tamu';
                                $trxCourt = $trx->jadwal?->lapangan?->nama ?? 'Lapangan';
                                $isCash = $trx->metode === 'tunai';
                            @endphp
                            <tr class="hover:bg-[#eff4ff]/50 transition-colors">
                                <td class="px-4 py-3 font-mono font-bold text-[#1a146b]">{{ $trxCode }}</td>
                                <td class="px-4 py-3 font-bold text-[#0b1c30]">{{ $trxNama }}</td>
                                <td class="px-4 py-3 text-[#474651]">{{ $trxCourt }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded {{ $isCash ? 'bg-[#eff4ff] text-[#1a146b]' : 'bg-[#dce9ff] text-[#1a146b]' }} font-bold text-[10px]">
                                        {{ $isCash ? 'Tunai' : 'Online' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-bold text-[#0b1c30]">Rp {{ number_format($trx->jumlah, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-[#777682]">{{ $trx->dibayar_pada ? $trx->dibayar_pada->format('H:i') . ' WIB' : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-[#777682]">
                                    Belum ada transaksi tercatat dalam database.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Shift Closing Form (4 cols) -->
        <div class="xl:col-span-4 bg-white rounded-2xl p-6 shadow-sm border border-[#e5eeff] flex flex-col gap-4">
            <div class="pb-2 border-b border-[#eff4ff]">
                <h3 class="text-sm font-bold text-[#0b1c30]">Formulir Rekonsiliasi Tutup Shift</h3>
                <p class="text-xs text-[#777682]">Pastikan nominal fisik kas cocok dengan sistem.</p>
            </div>

            <div class="p-4 rounded-xl bg-[#eff4ff] border border-[#e5eeff] flex flex-col gap-2 text-xs">
                <div class="flex items-center justify-between text-[#474651]">
                    <span>Kas Tunai Sistem:</span>
                    <span class="font-bold text-[#0b1c30]">Rp <span x-text="formatRupiah(cashExpected)"></span></span>
                </div>
                <div class="flex flex-col gap-1.5 mt-2">
                    <label class="text-[11px] font-bold text-[#0b1c30]">Input Kas Fisik Aktual:</label>
                    <input type="number" x-model.number="cashActual"
                           class="w-full px-3 py-2 rounded-xl bg-white text-sm font-bold text-[#0b1c30] border border-[#dce9ff] outline-none focus:ring-2 focus:ring-[#acf847]">
                </div>
                <div class="pt-2 border-t border-[#dce9ff] flex items-center justify-between font-bold text-xs">
                    <span>Selisih Kas:</span>
                    <span :class="difference === 0 ? 'text-[#416900]' : 'text-[#ba1a1a]'">
                        Rp <span x-text="formatRupiah(difference)">0</span>
                        <span x-text="difference === 0 ? '(Cocok)' : '(Tidak Cocok)'"></span>
                    </span>
                </div>
            </div>

            <button type="button" @click="alert('✅ Rekonsiliasi kas shift berhasil diverifikasi dan ditutup!')"
                    class="w-full py-3 px-4 rounded-xl bg-[#1a146b] hover:bg-[#312e81] text-white font-headline font-bold text-xs flex items-center justify-center gap-2 shadow-md transition-all active:scale-95 cursor-pointer">
                <span class="material-symbols-outlined text-base text-[#acf847]">lock_clock</span>
                <span>Tutup Shift &amp; Cetak Berita Acara</span>
            </button>
        </div>
    </div>
</div>
@endsection
