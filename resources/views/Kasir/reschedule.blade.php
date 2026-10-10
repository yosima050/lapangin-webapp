@extends('layouts.kasir')

@section('title', 'Kelola Pengajuan Reschedule Jadwal - LapangIn')

@section('content')
@php
    $currentReschedule = $selectedReschedule ?? $reschedules->first();
    $custName = $currentReschedule?->jadwal?->pelanggan?->nama ?? $currentReschedule?->jadwal?->nama_tamu ?? '-';
    $custPhone = $currentReschedule?->jadwal?->pelanggan?->no_hp ?? $currentReschedule?->jadwal?->no_wa_tamu ?? '-';
    $custDp = $currentReschedule ? (float) $currentReschedule->jadwal?->transaksis->where('status', 'berhasil')->sum('jumlah') : 0;
    $courtName = $currentReschedule?->jadwal?->lapangan?->nama ?? '-';
    $originPrice = $currentReschedule?->jadwal?->harga_disepakati ?? 0;
@endphp

<div class="w-full px-8 py-8 flex flex-col gap-6" x-data="{
    activeTab: 'pending',
    approvalNote: 'Jadwal baru disetujui kasir. Silakan gunakan E-Tiket dengan QR Code yang telah diperbarui di pintu akses lapangan.',
    setRejectPreset(reason) {
        this.approvalNote = 'Mohon maaf permohonan reschedule ditolak karena: ' + reason;
    }
}">
    <!-- Flash Notifications -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-[#acf847]/30 border border-[#acf847] flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-2xl text-[#416900]">check_circle</span>
                <span class="text-xs font-bold text-[#102000]">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-[#416900] hover:text-[#102000]">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
    @endif

    @if (session('warning'))
        <div class="p-4 rounded-2xl bg-[#ffdad6] border border-[#ffb4ab] flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-2xl text-[#ba1a1a]">warning</span>
                <span class="text-xs font-bold text-[#ba1a1a]">{{ session('warning') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-[#ba1a1a] hover:text-black">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
    @endif

    <!-- Subheader Strip -->
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-headline font-extrabold text-[#0b1c30] tracking-tight">
                Kelola Pengajuan Reschedule Jadwal
            </h1>
            <p class="text-sm text-[#474651] mt-1 max-w-2xl">
                Pemeriksaan ketersediaan slot lapangan dan persetujuan pengubahan jadwal reservasi pelanggan secara real-time.
            </p>
        </div>

        <button type="button" onclick="window.location.reload()"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#dce9ff] hover:bg-[#c3c0ff] text-[#0b1c30] text-xs font-bold transition-all shadow-sm">
            <span class="material-symbols-outlined text-base">sync</span>
            <span>Sinkronisasi Data</span>
        </button>
    </div>

    <!-- Summary 4 KPI Metric Cards (Real from Database) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Perlu Tindakan -->
        <div class="p-5 rounded-2xl bg-white shadow-sm border border-[#e5eeff] flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#777682]">PERLU TINDAKAN</span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-[#acf847] text-[#102000]">URGENT</span>
            </div>
            <div class="flex items-baseline gap-2 mt-3">
                <span class="text-3xl font-headline font-black text-[#0b1c30]">{{ $urgentCount }}</span>
                <span class="text-xs font-semibold text-[#474651]">Pengajuan Menunggu</span>
            </div>
        </div>

        <!-- Card 2: Disetujui Hari Ini -->
        <div class="p-5 rounded-2xl bg-white shadow-sm border border-[#e5eeff] flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#777682]">DISETUJUI</span>
                <span class="w-6 h-6 rounded-lg bg-[#eff4ff] text-[#416900] flex items-center justify-center">
                    <span class="material-symbols-outlined text-base">check_circle</span>
                </span>
            </div>
            <div class="flex items-baseline gap-2 mt-3">
                <span class="text-3xl font-headline font-black text-[#0b1c30]">{{ $approvedCount }}</span>
                <span class="text-xs font-semibold text-[#474651]">Jadwal Berhasil Diubah</span>
            </div>
            <span class="text-[10px] text-[#777682] mt-1">Total e-ticket re-issued</span>
        </div>

        <!-- Card 3: Ditolak / Bentrok -->
        <div class="p-5 rounded-2xl bg-white shadow-sm border border-[#e5eeff] flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#777682]">DITOLAK / BENTROK</span>
                <span class="w-6 h-6 rounded-lg bg-[#ffdad6] text-[#ba1a1a] flex items-center justify-center">
                    <span class="material-symbols-outlined text-base">cancel</span>
                </span>
            </div>
            <div class="flex items-baseline gap-2 mt-3">
                <span class="text-3xl font-headline font-black text-[#0b1c30]">{{ $rejectedCount }}</span>
                <span class="text-xs font-semibold text-[#474651]">Permintaan Ditolak</span>
            </div>
            <span class="text-[10px] text-[#777682] mt-1">{{ $rejectedCount }} permohonan tidak memenuhi syarat</span>
        </div>

        <!-- Card 4: Slot Pengganti -->
        <div class="p-5 rounded-2xl bg-white shadow-sm border border-[#e5eeff] flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#777682]">SLOT PENGGANTI</span>
                <span class="w-6 h-6 rounded-lg bg-[#eff4ff] text-[#416900] flex items-center justify-center">
                    <span class="material-symbols-outlined text-base">event_available</span>
                </span>
            </div>
            <div class="flex items-baseline gap-2 mt-3">
                <span class="text-3xl font-headline font-black text-[#0b1c30]">{{ $totalDailySlots }}</span>
                <span class="text-xs font-semibold text-[#474651]">Slot Siap Pakai</span>
            </div>
            <span class="text-[10px] text-[#416900] font-bold mt-1">Kapasitas harian arena</span>
        </div>
    </div>

    <!-- Filter, Search & Segment Bar -->
    <div class="bg-white p-3 rounded-2xl shadow-sm border border-[#e5eeff] flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
            <button type="button" @click="activeTab = 'pending'"
                    :class="activeTab === 'pending' ? 'bg-[#1a146b] text-white shadow-sm' : 'bg-[#eff4ff] text-[#474651] hover:bg-[#e5eeff]'"
                    class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all">
                Menunggu Tindakan ({{ $urgentCount }})
            </button>
            <button type="button" @click="activeTab = 'all'"
                    :class="activeTab === 'all' ? 'bg-[#1a146b] text-white shadow-sm' : 'bg-[#eff4ff] text-[#474651] hover:bg-[#e5eeff]'"
                    class="px-4 py-2 rounded-xl text-xs font-medium whitespace-nowrap transition-all">
                Semua Pengajuan ({{ $totalPengajuan }})
            </button>
            <button type="button" @click="activeTab = 'approved'"
                    :class="activeTab === 'approved' ? 'bg-[#1a146b] text-white shadow-sm' : 'bg-[#eff4ff] text-[#474651] hover:bg-[#e5eeff]'"
                    class="px-4 py-2 rounded-xl text-xs font-medium whitespace-nowrap transition-all">
                Disetujui ({{ $approvedCount }})
            </button>
            <button type="button" @click="activeTab = 'rejected'"
                    :class="activeTab === 'rejected' ? 'bg-[#1a146b] text-white shadow-sm' : 'bg-[#eff4ff] text-[#474651] hover:bg-[#e5eeff]'"
                    class="px-4 py-2 rounded-xl text-xs font-medium whitespace-nowrap transition-all">
                Ditolak ({{ $rejectedCount }})
            </button>
        </div>

        <div class="flex items-center gap-2">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#777682] material-symbols-outlined text-base">search</span>
                <input type="text" placeholder="Cari nama pemesan..."
                       class="pl-9 pr-3 py-1.5 rounded-xl bg-[#eff4ff] text-xs text-[#0b1c30] font-bold border border-[#e5eeff] outline-none focus:bg-white focus:ring-1 focus:ring-[#1a146b] transition-all">
            </div>
        </div>
    </div>

    <!-- Main Content Split Pane (7 cols / 5 cols) -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

        <!-- LEFT COLUMN: Requests Queue (Real from Database) -->
        <div class="xl:col-span-7 flex flex-col gap-4">
            <div class="flex items-center justify-between px-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#777682]">DAFTAR ANTREAN PERMINTAAN PEMINDAHAN JADWAL ({{ $reschedules->count() }})</span>
                <span class="text-xs text-[#474651]">Klik kartu untuk evaluasi</span>
            </div>

            @forelse ($reschedules as $item)
                @php
                    $isItemActive = $currentReschedule && $currentReschedule->id === $item->id;
                    $itemCust = $item->jadwal?->pelanggan?->nama ?? $item->jadwal?->nama_tamu ?? 'Penyewa LapangIn';
                    $itemPhone = $item->jadwal?->pelanggan?->no_hp ?? $item->jadwal?->no_wa_tamu ?? '-';
                    $itemDp = (float) $item->jadwal?->transaksis->where('status', 'berhasil')->sum('jumlah');
                    $itemCourt = $item->jadwal?->lapangan?->nama ?? 'Lapangan Olahraga';

                    // Cek selisih jam ke jadwal awal
                    $jadwalAwalDate = $item->jadwal?->tanggal;
                    $jamMulaiAwal = $item->jadwal?->jam_mulai;
                    $isValid12Jam = true;
                    if ($jadwalAwalDate && $jamMulaiAwal) {
                        $startTime = \Carbon\Carbon::parse($jadwalAwalDate->format('Y-m-d') . ' ' . $jamMulaiAwal);
                        $isValid12Jam = now()->diffInHours($startTime, false) >= 12;
                    }
                @endphp
                <div x-show="activeTab === 'all' || activeTab === '{{ $item->status === 'diajukan' ? 'pending' : ($item->status === 'disetujui' ? 'approved' : 'rejected') }}'">
                    <a href="{{ route('kasir.reschedule', ['id' => $item->id]) }}"
                       class="relative bg-white p-5 rounded-2xl shadow-sm border-2 {{ $isItemActive ? 'border-[#acf847]' : 'border-[#e5eeff] hover:border-[#1a146b]' }} flex flex-col gap-4 transition-all block">
                        @if ($isItemActive)
                            <div class="absolute left-0 top-0 bottom-0 w-2 bg-[#416900] rounded-l-2xl"></div>
                        @endif

                        <div class="flex items-center justify-between {{ $isItemActive ? 'pl-2' : '' }}">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold {{ $item->status === 'diajukan' ? 'bg-[#acf847] text-[#102000]' : ($item->status === 'disetujui' ? 'bg-[#1a146b] text-white' : 'bg-[#ffdad6] text-[#ba1a1a]') }}">
                                {{ strtoupper($item->status === 'diajukan' ? 'Menunggu Approval' : $item->status) }}
                            </span>
                            <div class="flex items-center gap-2 text-xs text-[#777682]">
                                <span class="material-symbols-outlined text-sm">schedule</span>
                                <span>{{ $item->created_at ? $item->created_at->diffForHumans() : 'Baru saja' }}</span>
                                <span>•</span>
                                <span class="{{ $isValid12Jam ? 'text-[#416900]' : 'text-[#ba1a1a]' }} font-bold">
                                    {{ $isValid12Jam ? 'Valid >12 Jam' : 'Kurang dari 12 Jam' }}
                                </span>
                            </div>
                        </div>

                        <!-- Customer Identity -->
                        <div class="p-3.5 rounded-xl bg-[#eff4ff] flex items-center justify-between {{ $isItemActive ? 'ml-2' : '' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#1a146b] text-white flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($itemCust, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-[#0b1c30]">{{ $itemCust }}</span>
                                        <span class="text-[10px] px-2 py-0.5 rounded bg-[#dce9ff] text-[#1a146b] font-bold">
                                            {{ $item->jadwal?->pelanggan ? 'Member Terdaftar' : 'Tamu Walk-in' }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-[#474651]">{{ $itemPhone }}</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-[#1a146b]">Rp {{ number_format($itemDp, 0, ',', '.') }} (DP Masuk)</span>
                        </div>

                        <!-- Visual Comparison Grid: Semula vs Usulan Baru -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 {{ $isItemActive ? 'ml-2' : '' }}">
                            <!-- Jadwal Semula -->
                            <div class="p-3.5 rounded-xl bg-[#eff4ff] border border-[#e5eeff] flex flex-col gap-1">
                                <div class="flex items-center justify-between text-[10px] font-bold uppercase text-[#777682]">
                                    <span>JADWAL SEMULA</span>
                                    <span class="material-symbols-outlined text-sm">history</span>
                                </div>
                                <span class="text-xs font-bold text-[#0b1c30]">{{ $itemCourt }}</span>
                                <span class="text-xs font-extrabold text-[#1a146b]">{{ $item->jadwal?->tanggal ? $item->jadwal->tanggal->format('d M Y') : '-' }}</span>
                                <span class="text-xs text-[#474651]">{{ substr($item->jadwal?->jam_mulai ?? '', 0, 5) }} - {{ substr($item->jadwal?->jam_selesai ?? '', 0, 5) }} WIB</span>
                                <div class="pt-2 mt-1 border-t border-[#dce9ff] flex items-center justify-between text-xs">
                                    <span class="text-[#777682]">Tarif Awal:</span>
                                    <span class="font-bold text-[#0b1c30]">Rp {{ number_format($item->jadwal?->harga_disepakati ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <!-- Usulan Jadwal Baru -->
                            <div class="relative p-3.5 rounded-xl bg-[#dce9ff] border border-[#c3c0ff] flex flex-col gap-1 overflow-hidden">
                                <span class="text-[10px] font-bold uppercase text-[#416900]">USULAN JADWAL BARU</span>
                                <span class="text-xs font-bold text-[#0b1c30]">{{ $itemCourt }}</span>
                                <span class="text-xs font-extrabold text-[#416900]">{{ $item->tanggal_baru ? $item->tanggal_baru->format('d M Y') : '-' }}</span>
                                <span class="text-xs font-bold text-[#0b1c30]">{{ substr($item->jam_mulai_baru, 0, 5) }} - {{ substr($item->jam_selesai_baru, 0, 5) }} WIB</span>
                                <div class="pt-2 mt-1 border-t border-[#c3c0ff] flex items-center justify-between text-xs">
                                    <span class="text-[#474651]">Status:</span>
                                    <span class="font-bold text-[#1a146b]">{{ ucfirst($item->status) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Alasan Pemohon -->
                        <div class="p-3 rounded-xl bg-[#eff4ff] flex items-start gap-2.5 {{ $isItemActive ? 'ml-2' : '' }} text-xs">
                            <span class="material-symbols-outlined text-base text-[#1a146b] shrink-0">chat</span>
                            <div>
                                <span class="font-bold text-[#0b1c30]">Alasan Pemesan:</span>
                                <span class="text-[#474651] italic">“{{ $item->alasan_pengajuan }}”</span>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="bg-white p-8 rounded-2xl border border-[#e5eeff] text-center text-[#777682]">
                    <span class="material-symbols-outlined text-4xl text-[#acf847] mb-2">pending_actions</span>
                    <p class="font-bold text-[#0b1c30]">Tidak ada pengajuan reschedule dalam database.</p>
                </div>
            @endforelse
        </div>

        <!-- RIGHT COLUMN: Evaluation & Decision Action Panel -->
        <div class="xl:col-span-5 flex flex-col gap-4">
            @if ($currentReschedule)
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5eeff] flex flex-col gap-5 sticky top-20">
                    <div class="pb-3 border-b border-[#eff4ff]">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#416900]">KEPUTUSAN OPERASIONAL</span>
                        <h2 class="text-lg font-headline font-extrabold text-[#0b1c30]">Evaluasi Kasir Reschedule</h2>
                        <p class="text-xs text-[#777682] mt-0.5">Pemohon: <span class="font-bold text-[#0b1c30]">{{ $custName }}</span></p>
                    </div>

                    <!-- Green/Red Banner: Slot Availability Check from DB -->
                    @if ($isSlotAvailable)
                        <div class="p-4 rounded-xl bg-[#acf847]/20 border border-[#acf847] flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-[#acf847] text-[#102000] flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-xl font-bold">check</span>
                            </span>
                            <div>
                                <h3 class="text-xs font-bold text-[#102000]">Slot Pengganti Terverifikasi Kosong</h3>
                                <p class="text-[11px] text-[#416900]">Tidak ada jadwal bentrok pada tanggal &amp; jam tersebut di database.</p>
                            </div>
                        </div>
                    @else
                        <div class="p-4 rounded-xl bg-[#ffdad6] border border-[#ffb4ab] flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-[#ba1a1a] text-white flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-xl font-bold">priority_high</span>
                            </span>
                            <div>
                                <h3 class="text-xs font-bold text-[#ba1a1a]">Slot Pengganti Bentrok</h3>
                                <p class="text-[11px] text-[#93000a]">Terdapat booking lain yang sudah terkonfirmasi di rentang jam tersebut.</p>
                            </div>
                        </div>
                    @endif

                    <!-- Rate & Financial Comparison Dynamic -->
                    <div class="p-4 rounded-xl bg-[#eff4ff] border border-[#e5eeff] flex flex-col gap-2.5 text-xs">
                        <div class="flex items-center justify-between text-[#474651]">
                            <span>Tarif Sesi Semula:</span>
                            <span class="font-bold text-[#0b1c30]">Rp {{ number_format($originPrice, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[#474651]">
                            <span>Tarif Sesi Baru:</span>
                            <span class="font-bold text-[#0b1c30]">Rp {{ number_format($tarifSesiBaru, 0, ',', '.') }}</span>
                        </div>
                        <div class="pt-2 border-t border-[#dce9ff] flex items-center justify-between">
                            <span class="font-bold text-[#0b1c30]">Selisih Biaya:</span>
                            @if ($selisihBiaya == 0)
                                <span class="px-2 py-0.5 rounded bg-[#e5eeff] text-[#1a146b] font-black">
                                    Rp 0 (Sama Tarif)
                                </span>
                            @elseif ($selisihBiaya > 0)
                                <span class="px-2 py-0.5 rounded bg-[#ffdad6] text-[#ba1a1a] font-black">
                                    + Rp {{ number_format($selisihBiaya, 0, ',', '.') }} (Tambahan)
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded bg-[#dce9ff] text-[#416900] font-black">
                                    - Rp {{ number_format(abs($selisihBiaya), 0, ',', '.') }} (Lebih Rendah)
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Catatan Persetujuan Form -->
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-[#0b1c30]">Catatan Kasir untuk Pemesan:</label>
                            <span class="text-[10px] text-[#777682]">Tercatat pada histori</span>
                        </div>
                        <textarea x-model="approvalNote" rows="3"
                                  class="w-full p-3 rounded-xl bg-[#eff4ff] text-xs text-[#0b1c30] border border-[#e5eeff] focus:bg-white focus:ring-2 focus:ring-[#acf847] outline-none transition-all"></textarea>
                    </div>

                    <!-- Opsi Cepat Jika Ingin Tolak -->
                    <div class="flex flex-col gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#777682]">PRESET ALASAN TOLAK:</span>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" @click="setRejectPreset('Slot Bentrok dengan Reservasi Lain')"
                                    class="px-2.5 py-1 rounded-lg bg-[#eff4ff] hover:bg-[#ffdad6] hover:text-[#ba1a1a] text-[11px] font-semibold text-[#474651] transition-colors border border-[#e5eeff]">
                                Slot Bentrok
                            </button>
                            <button type="button" @click="setRejectPreset('Kurang dari Batas Minimal 12 Jam')"
                                    class="px-2.5 py-1 rounded-lg bg-[#eff4ff] hover:bg-[#ffdad6] hover:text-[#ba1a1a] text-[11px] font-semibold text-[#474651] transition-colors border border-[#e5eeff]">
                                &lt;12 Jam
                            </button>
                            <button type="button" @click="setRejectPreset('Pemeliharaan / Maintenance Lapangan')"
                                    class="px-2.5 py-1 rounded-lg bg-[#eff4ff] hover:bg-[#ffdad6] hover:text-[#ba1a1a] text-[11px] font-semibold text-[#474651] transition-colors border border-[#e5eeff]">
                                Maintenance
                            </button>
                        </div>
                    </div>

                    <!-- Real Database Action Buttons (POST Forms) -->
                    @if ($currentReschedule->status === 'diajukan')
                        <div class="pt-2 flex flex-col gap-2.5">
                            <form method="POST" action="{{ route('kasir.reschedule.approve', $currentReschedule->id) }}">
                                @csrf
                                <input type="hidden" name="catatan" :value="approvalNote">
                                <button type="submit"
                                        class="w-full py-3.5 px-4 rounded-xl bg-[#1a146b] hover:bg-[#312e81] text-white font-headline font-bold text-xs flex items-center justify-center gap-2 shadow-md transition-all active:scale-95 cursor-pointer">
                                    <span class="material-symbols-outlined text-lg text-[#acf847]">check_circle</span>
                                    <span>Setujui Reschedule &amp; Re-issue E-Tiket</span>
                                </button>
                            </form>

                            <form method="POST" action="{{ route('kasir.reschedule.reject', $currentReschedule->id) }}">
                                @csrf
                                <input type="hidden" name="catatan" :value="approvalNote">
                                <button type="submit"
                                        class="w-full py-2.5 px-4 rounded-xl border border-[#ba1a1a] text-[#ba1a1a] hover:bg-[#ffdad6] font-bold text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                                    <span class="material-symbols-outlined text-base">close</span>
                                    <span>Tolak Pengajuan Reschedule</span>
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="p-3.5 rounded-xl bg-[#eff4ff] text-center border border-[#dce9ff]">
                            <span class="text-xs font-bold text-[#1a146b]">
                                Pengajuan ini telah berstatus: {{ strtoupper($currentReschedule->status) }}
                            </span>
                        </div>
                    @endif

                    <!-- Aturan Reschedule Info Box -->
                    <div class="p-4 rounded-xl bg-[#eff4ff] border border-[#dce9ff] flex items-start gap-2.5 text-xs">
                        <span class="material-symbols-outlined text-base text-[#1a146b] shrink-0 mt-0.5">info</span>
                        <div>
                            <span class="font-bold text-[#0b1c30]">Aturan Operasional Venue:</span>
                            <p class="text-[#474651] text-[11px] mt-0.5 leading-relaxed">
                                Persetujuan kasir akan memperbarui data reservasi di database PostgreSQL dan langsung menerbitkan QR Code jadwal baru kepada pelanggan.
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl p-6 border border-[#e5eeff] text-center text-[#777682]">
                    <p class="text-sm font-bold text-[#0b1c30]">Pilih salah satu permohonan di sebelah kiri untuk melihat evaluasi.</p>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
