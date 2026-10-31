<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Thermal #{{ $jadwal->kode_qr ?: $jadwal->token_manual }} • LapangIn Arena</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts & Tailwind -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    @vite(['resources/css/app.css'])

    <style id="dynamic-page-style">
        /* Standar POS Thermal Paper Styling */
        @page {
            size: 58mm auto;
            margin: 0mm;
        }

        @media print {
            html, body {
                width: 58mm;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                color: #000 !important;
                font-family: 'JetBrains Mono', monospace, -apple-system, sans-serif !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .thermal-container {
                width: 54mm !important;
                max-width: 54mm !important;
                padding: 2mm !important;
                box-shadow: none !important;
                border: none !important;
                margin: 0 auto !important;
            }

            .thermal-text-xs {
                font-size: 9px !important;
                line-height: 1.2 !important;
            }

            .thermal-text-sm {
                font-size: 10px !important;
                line-height: 1.3 !important;
            }

            .thermal-text-base {
                font-size: 12px !important;
                line-height: 1.3 !important;
            }

            .thermal-text-lg {
                font-size: 14px !important;
                font-weight: 800 !important;
                line-height: 1.2 !important;
            }
        }

        /* Screen Preview Styling */
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
        }

        .receipt-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .dashed-line {
            border-bottom: 1px dashed #000;
            width: 100%;
            margin: 6px 0;
        }

        .double-line {
            border-bottom: 2px double #000;
            width: 100%;
            margin: 6px 0;
        }
    </style>
</head>
<body class="min-h-screen py-8 px-4 flex flex-col items-center justify-start">

    {{-- Screen Action Header (Hidden when printing) --}}
    <div class="no-print w-full max-w-sm mb-6 flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <a href="{{ route('kasir.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-50 shadow-2xs transition">
                    <span class="material-symbols-outlined text-base">desktop_windows</span>
                    <span>Meja Kasir</span>
                </a>
                <a href="{{ route('kasir.checkin.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-50 shadow-2xs transition">
                    <span class="material-symbols-outlined text-base">barcode_scanner</span>
                    <span>Scanner</span>
                </a>
            </div>

            <div class="flex items-center gap-2">
                <button type="button"
                        onclick="togglePaperWidth('58mm')"
                        id="btn-58mm"
                        class="px-2.5 py-1 text-xs font-bold rounded-lg bg-[#1a146b] text-white shadow-xs">
                    58mm
                </button>
                <button type="button"
                        onclick="togglePaperWidth('80mm')"
                        id="btn-80mm"
                        class="px-2.5 py-1 text-xs font-bold rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
                    80mm
                </button>
            </div>
        </div>

        <button type="button"
                onclick="window.print()"
                class="w-full py-3 px-4 rounded-xl bg-[#acf847] hover:bg-[#9be338] text-[#1a146b] font-headline font-black text-sm flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition cursor-pointer">
            <span class="material-symbols-outlined text-lg">print</span>
            <span>Cetak Struk Sekarang (Ctrl + P)</span>
        </button>
    </div>

    {{-- Thermal Receipt Paper Container --}}
    <div id="receipt-paper"
         class="thermal-container w-[58mm] max-w-[58mm] bg-white p-4 text-black shadow-lg border border-gray-200 text-[10px] leading-tight receipt-mono transition-all">

        {{-- Venue Header --}}
        <div class="text-center flex flex-col items-center">
            <h1 class="text-xs font-extrabold tracking-wider uppercase">LAPANGIN ARENA</h1>
            <p class="text-[9px] text-gray-700 mt-0.5">Jl. TB Simatupang No. 18, Jakarta Selatan</p>
            <p class="text-[8px] text-gray-500">Telp / WA: 0812-3456-7890</p>
        </div>

        <div class="dashed-line"></div>

        {{-- Receipt Meta Info --}}
        <div class="flex flex-col gap-0.5 text-[9px]">
            <div class="flex justify-between">
                <span>No. Nota:</span>
                <span class="font-bold">{{ $jadwal->kode_qr ?: $jadwal->token_manual }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tanggal:</span>
                <span>{{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</span>
            </div>
            <div class="flex justify-between">
                <span>Kasir:</span>
                <span>{{ $namaKasir }}</span>
            </div>
            <div class="flex justify-between">
                <span>Penyewa:</span>
                <span class="font-bold truncate max-w-[120px]">{{ $namaPenyewa }}</span>
            </div>
            <div class="flex justify-between">
                <span>No. WA:</span>
                <span>{{ $noWaPenyewa }}</span>
            </div>
        </div>

        <div class="dashed-line"></div>

        {{-- Match Schedule Details --}}
        <div class="flex flex-col gap-0.5 text-[9px]">
            <div class="font-bold uppercase text-[10px]">{{ $jadwal->lapangan?->nama ?? 'Lapangan Olahraga' }}</div>
            <div class="flex justify-between">
                <span>Tgl Main:</span>
                <span>{{ \Carbon\Carbon::parse($jadwal->tanggal)->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Jam Main:</span>
                <span class="font-bold">{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB</span>
            </div>
            <div class="flex justify-between">
                <span>Check-in:</span>
                <span class="font-bold text-emerald-800">
                    {{ $jadwal->checked_in_at ? \Carbon\Carbon::parse($jadwal->checked_in_at)->timezone('Asia/Jakarta')->format('H:i:s') . ' WIB' : 'SELESAI' }}
                </span>
            </div>
        </div>

        {{-- Equipment Add-ons (if any) --}}
        @if($jadwal->detailSewaPerlengkapans && $jadwal->detailSewaPerlengkapans->count() > 0)
            <div class="dashed-line"></div>
            <div class="flex flex-col gap-1 text-[9px]">
                <span class="font-bold uppercase text-[8px] text-gray-600">Sewa Perlengkapan:</span>
                @foreach($jadwal->detailSewaPerlengkapans as $item)
                    <div class="flex justify-between">
                        <span>{{ $item->perlengkapan?->nama ?? 'Alat' }} (x{{ $item->jumlah }})</span>
                        <span>Rp{{ number_format((float)$item->subtotal, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="double-line"></div>

        {{-- Payment Breakdown --}}
        <div class="flex flex-col gap-1 text-[9px]">
            <div class="flex justify-between font-semibold">
                <span>Total Sewa:</span>
                <span>Rp{{ number_format($totalHarga, 0, ',', '.') }}</span>
            </div>

            @if($dpTerbayar > 0)
                <div class="flex justify-between text-gray-700">
                    <span>DP Online (Midtrans):</span>
                    <span>- Rp{{ number_format($dpTerbayar, 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="flex justify-between font-bold text-[10px] pt-0.5 border-t border-dotted border-gray-400">
                <span>Sisa Tagihan:</span>
                <span>Rp{{ number_format($sisaTagihan, 0, ',', '.') }}</span>
            </div>

            <div class="flex justify-between text-gray-700">
                <span>Pelunasan Kasir:</span>
                <span>Rp{{ number_format($pelunasanBayar ?: $uangDiterima, 0, ',', '.') }}</span>
            </div>

            @if($uangDiterima > 0)
                <div class="flex justify-between text-gray-700">
                    <span>Uang Diterima:</span>
                    <span>Rp{{ number_format($uangDiterima, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-gray-700">
                    <span>Kembalian:</span>
                    <span>Rp{{ number_format($kembalian, 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="flex justify-between font-black text-[10px] mt-0.5 pt-1 border-t border-black">
                <span>STATUS:</span>
                <span class="uppercase">LUNAS - VALID</span>
            </div>
        </div>

        <div class="dashed-line"></div>

        {{-- Footer Note & Barcode ASCII Representation --}}
        <div class="text-center flex flex-col items-center gap-1 pt-1">
            <p class="font-bold text-[9px]">Terima kasih telah bermain di LapangIn!</p>
            <p class="text-[8px] text-gray-600">Simpan struk ini sebagai bukti check-in yang sah.</p>
            <p class="text-[8px] text-gray-400 mt-1">Selamat bertanding &amp; junjung sportivitas</p>
            <p class="text-[8px] text-gray-400">www.lapangin.biz.id</p>
        </div>

    </div>

    {{-- Auto-Print Script --}}
    <script>
        function togglePaperWidth(size) {
            const paper = document.getElementById('receipt-paper');
            const btn58 = document.getElementById('btn-58mm');
            const btn80 = document.getElementById('btn-80mm');
            const dynamicStyle = document.getElementById('dynamic-page-style');

            if (size === '80mm') {
                paper.className = paper.className.replace('w-[58mm] max-w-[58mm]', 'w-[80mm] max-w-[80mm]');
                btn80.className = 'px-2.5 py-1 text-xs font-bold rounded-lg bg-[#1a146b] text-white shadow-xs';
                btn58.className = 'px-2.5 py-1 text-xs font-bold rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50';
                if (dynamicStyle) {
                    dynamicStyle.innerHTML = `
                        @page { size: 80mm auto; margin: 0mm; }
                        @media print {
                            html, body { width: 80mm !important; margin: 0 !important; padding: 0 !important; background: #fff !important; color: #000 !important; font-family: 'JetBrains Mono', monospace, -apple-system, sans-serif !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                            .no-print { display: none !important; }
                            .thermal-container { width: 76mm !important; max-width: 76mm !important; padding: 2mm !important; box-shadow: none !important; border: none !important; margin: 0 auto !important; }
                        }
                    `;
                }
            } else {
                paper.className = paper.className.replace('w-[80mm] max-w-[80mm]', 'w-[58mm] max-w-[58mm]');
                btn58.className = 'px-2.5 py-1 text-xs font-bold rounded-lg bg-[#1a146b] text-white shadow-xs';
                btn80.className = 'px-2.5 py-1 text-xs font-bold rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50';
                if (dynamicStyle) {
                    dynamicStyle.innerHTML = `
                        @page { size: 58mm auto; margin: 0mm; }
                        @media print {
                            html, body { width: 58mm !important; margin: 0 !important; padding: 0 !important; background: #fff !important; color: #000 !important; font-family: 'JetBrains Mono', monospace, -apple-system, sans-serif !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                            .no-print { display: none !important; }
                            .thermal-container { width: 54mm !important; max-width: 54mm !important; padding: 2mm !important; box-shadow: none !important; border: none !important; margin: 0 auto !important; }
                        }
                    `;
                }
            }
        }

        // Pemicu otomatis window.print() setelah seluruh resource dimuat
        window.addEventListener('load', function () {
            // Beri sedikit jeda agar font dirender sempurna sebelum dialog print muncul
            setTimeout(function () {
                window.print();
            }, 350);
        });
    </script>
</body>
</html>

