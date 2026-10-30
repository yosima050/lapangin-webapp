@extends('layouts.kasir')

@section('title', 'Scanner Barcode & Check-in Kasir • LapangIn')

@section('content')
<div class="w-full px-6 lg:px-8 py-8 flex flex-col gap-6">

    {{-- Top Heading & Status Indicator --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-[#416900] animate-pulse"></span>
                <span class="text-xs font-black uppercase tracking-wider text-[#416900]">
                    TERMINAL POS SCANNER AKTIF
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-headline font-black text-[#0b1c30] tracking-tight">
                Scan Tiket &amp; Validasi Pelunasan
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Arahkan barcode scanner USB/Wireless HID ke tiket pelanggan. Sistem otomatis mendeteksi kode dan mencetak struk thermal.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="px-4 py-2 rounded-2xl bg-white border border-gray-200 shadow-2xs flex items-center gap-3">
                <span class="material-symbols-outlined text-2xl text-[#1a146b]">receipt_long</span>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-400">Total Check-In Hari Ini</p>
                    <p class="text-lg font-headline font-black text-[#1a146b]">{{ $totalCheckInToday ?? 0 }} Tiket</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash Notifications / Feedback Alert --}}
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start gap-3 shadow-xs">
            <span class="material-symbols-outlined text-emerald-600 text-2xl shrink-0">check_circle</span>
            <div class="text-xs sm:text-sm">
                <p class="font-bold">Berhasil!</p>
                <p class="mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-900 flex items-start gap-3 shadow-xs">
            <span class="material-symbols-outlined text-red-600 text-2xl shrink-0">error</span>
            <div class="text-xs sm:text-sm">
                <p class="font-bold">Peringatan / Validasi Gagal</p>
                <p class="mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- Main Hardware Scan Input Box --}}
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-gray-100 flex flex-col gap-5">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-xl text-[#1a146b]">barcode_scanner</span>
                <h2 class="text-base font-headline font-bold text-[#0b1c30]">
                    Input Pemindai Barcode / QR Code Tiket
                </h2>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#eff4ff] text-[#1a146b] text-xs font-bold border border-[#dce9ff]">
                <span class="w-2 h-2 rounded-full bg-[#416900] animate-ping"></span>
                <span>Auto-Focus Scanner Siap</span>
            </span>
        </div>

        <form id="checkin-form" method="POST" action="{{ route('kasir.checkin.process') }}" class="flex flex-col gap-4">
            @csrf

            <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-2xl text-gray-400 pointer-events-none">
                    qr_code_2
                </span>

                <input
                    id="barcode-input"
                    type="text"
                    name="code"
                    required
                    autofocus
                    autocomplete="off"
                    placeholder="Scan barcode / ketik kode tiket (misal: LPGN-20261029-XXXX atau Token manual 6 digit)..."
                    class="w-full pl-12 pr-32 py-4 rounded-2xl border-2 border-indigo-200 bg-[#f8f9ff] text-base sm:text-lg font-mono font-bold text-[#0b1c30] placeholder-gray-400 focus:outline-none focus:border-[#1a146b] focus:bg-white focus:ring-4 focus:ring-indigo-100 transition shadow-inner"
                >

                <button
                    type="submit"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 px-5 py-2.5 rounded-xl bg-[#1a146b] hover:bg-[#130f52] text-white text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition flex items-center gap-1.5 cursor-pointer"
                >
                    <span>Proses</span>
                    <span class="material-symbols-outlined text-base">keyboard_return</span>
                </button>
            </div>

            <div class="flex flex-wrap items-center justify-between text-xs text-gray-500 pt-1">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base text-emerald-600">flash_on</span>
                    <span>Scanner HID langsung auto-submit saat memindai kode tanpa perlu menekan tombol Enter.</span>
                </span>
                <span class="text-gray-400">Tekan ESC untuk mengosongkan kolom input.</span>
            </div>
        </form>
    </div>

    {{-- Two Columns: Riwayat Check-In Baru Saja & Antrean Hari Ini --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Column 1: Baru Saja Check-in (Siap Cetak Ulang Struk) --}}
        <div class="bg-white rounded-3xl p-6 shadow-xs border border-gray-100 flex flex-col gap-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-600 text-lg">task_alt</span>
                    <h3 class="text-sm font-headline font-bold text-[#0b1c30]">Baru Saja Check-In (Hari Ini)</h3>
                </div>
                <span class="text-xs text-gray-400">{{ count($recentCheckIns) }} data</span>
            </div>

            <div class="flex flex-col divide-y divide-gray-100">
                @forelse ($recentCheckIns as $item)
                    <div class="py-3 flex items-center justify-between gap-3 first:pt-0 last:pb-0">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold text-gray-800 truncate">
                                    {{ $item->kode_qr ?: $item->token_manual }}
                                </span>
                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] font-bold">
                                    Checked In
                                </span>
                            </div>
                            <p class="text-xs text-gray-700 font-semibold truncate mt-0.5">
                                {{ $item->nama_tamu ?: ($item->pelanggan?->nama ?? 'Tamu Lapangan') }}
                                <span class="text-gray-400 font-normal">({{ $item->lapangan?->nama }})</span>
                            </p>
                            <p class="text-[11px] text-gray-400 font-mono">
                                Main: {{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }} WIB
                            </p>
                        </div>

                        <a href="{{ route('kasir.struk', $item->id) }}"
                           class="shrink-0 px-3 py-1.5 rounded-xl bg-gray-50 hover:bg-[#eff4ff] border border-gray-200 text-xs font-bold text-[#1a146b] flex items-center gap-1 transition shadow-2xs">
                            <span class="material-symbols-outlined text-sm">print</span>
                            <span>Cetak Struk</span>
                        </a>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-gray-400">
                        Belum ada tiket yang divalidasi check-in hari ini.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Column 2: Antrean Jadwal Hari Ini (Belum Check-In) --}}
        <div class="bg-white rounded-3xl p-6 shadow-xs border border-gray-100 flex flex-col gap-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-600 text-lg">pending_actions</span>
                    <h3 class="text-sm font-headline font-bold text-[#0b1c30]">Antrean Main Hari Ini (Belum Check-In)</h3>
                </div>
                <span class="text-xs text-gray-400">{{ count($pendingToday) }} data</span>
            </div>

            <div class="flex flex-col divide-y divide-gray-100">
                @forelse ($pendingToday as $item)
                    <div class="py-3 flex items-center justify-between gap-3 first:pt-0 last:pb-0">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold text-gray-800 truncate">
                                    {{ $item->kode_qr ?: $item->token_manual }}
                                </span>
                                @if($item->status_pembayaran === 'dp_terverifikasi')
                                    <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 text-[10px] font-bold">
                                        Perlu Pelunasan Kasir
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 text-[10px] font-bold">
                                        Lunas Online
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-700 font-semibold truncate mt-0.5">
                                {{ $item->nama_tamu ?: ($item->pelanggan?->nama ?? 'Tamu Lapangan') }}
                                <span class="text-gray-400 font-normal">({{ $item->lapangan?->nama }})</span>
                            </p>
                            <p class="text-[11px] text-gray-400 font-mono">
                                Jadwal: {{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }} WIB
                            </p>
                        </div>

                        <form method="POST" action="{{ route('kasir.checkin.process') }}">
                            @csrf
                            <input type="hidden" name="code" value="{{ $item->kode_qr ?: $item->token_manual }}">
                            <button type="submit"
                                    class="shrink-0 px-3 py-1.5 rounded-xl bg-[#acf847] hover:bg-[#9be338] text-[#1a146b] text-xs font-black flex items-center gap-1 transition shadow-xs cursor-pointer">
                                <span class="material-symbols-outlined text-sm">how_to_reg</span>
                                <span>Check-in</span>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-gray-400">
                        Tidak ada antrean jadwal yang tersisa untuk hari ini.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>

{{-- Hardware HID Scanner Script (Auto-Focus Loop & Auto-Submit) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('barcode-input');
    const form = document.getElementById('checkin-form');

    if (!input) return;

    // 1. Fokus otomatis saat pertama kali halaman terbuka
    input.focus();

    // 2. Pertahankan kursor tetap fokus ke input barcode meskipun kasir mengklik area lain di luar form
    document.addEventListener('click', function (e) {
        // Jangan paksa refokus jika pengguna sedang berinteraksi dengan tombol form atau input lain
        const isClickingAction = e.target.closest('button, a, input, select, textarea');
        if (!isClickingAction) {
            input.focus();
        }
    });

    // Refokus juga saat window kembali aktif
    window.addEventListener('focus', function () {
        input.focus();
    });

    // 3. Tangkap keyboard barcode scanner HID (biasanya diakhiri dengan ASCII Enter / KeyCode 13)
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const val = input.value.trim();
            if (val.length > 0) {
                form.submit();
            }
        } else if (e.key === 'Escape') {
            input.value = '';
        }
    });
});
</script>
@endsection

