@extends('layouts.pelanggan')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-8 sm:py-10">
    <div class="mx-auto max-w-6xl">

        {{-- Header --}}
        <div class="mb-8">
            <span class="text-sm font-semibold uppercase tracking-wider text-indigo-600">
                Area Pelanggan
            </span>

            <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">
                Riwayat Pesanan
            </h1>

            <p class="mt-2 max-w-2xl text-gray-600">
                Pantau status booking dan akses e-ticket Lapangin Anda
                untuk persiapan sebelum bermain.
            </p>
        </div>

        {{-- Notifikasi --}}
        @if (session('success'))
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">
                <span class="font-bold">✓</span>
                <p class="text-sm">{{ session('success') }}</p>
            </div>
        @endif

        @forelse ($jadwals as $jadwal)
            @php
                $lunas = in_array($jadwal->status_pembayaran, [
                    'lunas_online',
                    'lunas_kasir'
                ]);

                $statusLabel = ucfirst(str_replace(
                    '_',
                    ' ',
                    $jadwal->status_pembayaran ?? 'belum_bayar'
                ));
            @endphp

            <article class="mb-7 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

                {{-- Ringkasan Booking --}}
                <div class="flex flex-col justify-between gap-4 border-b border-gray-100 p-5 sm:flex-row sm:items-center sm:p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-6 w-6"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9v.01M9 12v.01M9 15v.01M9 18v.01M15 14v.01M15 17v.01" />
                            </svg>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Lapangan
                            </p>

                            <h2 class="mt-1 text-lg font-bold text-gray-900">
                                {{ $jadwal->lapangan?->nama ?? 'Nama lapangan tidak tersedia' }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                Booking
                                {{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('d F Y') }}
                            </p>
                        </div>
                    </div>

                    <span class="inline-flex w-fit items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold
                        {{ $lunas ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                        <span class="h-2 w-2 rounded-full {{ $lunas ? 'bg-green-500' : 'bg-amber-500' }}"></span>
                        {{ $statusLabel }}
                    </span>
                </div>

                {{-- Informasi Booking --}}
                <div class="grid gap-4 p-5 sm:grid-cols-3 sm:p-6">
                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="text-sm text-gray-500">Tanggal Booking</p>
                        <p class="mt-2 font-semibold text-gray-900">
                            {{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('d F Y') }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="text-sm text-gray-500">Jam Bermain</p>
                        <p class="mt-2 font-semibold text-gray-900">
                            {{ substr((string) $jadwal->jam_mulai, 0, 5) }}
                            – {{ substr((string) $jadwal->jam_selesai, 0, 5) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="text-sm text-gray-500">Total Harga</p>
                        <p class="mt-2 font-semibold text-gray-900">
                            Rp{{ number_format((float) $jadwal->harga_disepakati, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                {{-- Boarding Pass: hanya jika lunas --}}
                @if ($lunas)
                    <div class="border-t border-gray-200 bg-indigo-50/60 p-5 sm:p-6">
                        <div class="mb-5 flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-600">
                                    Lapangin Digital Ticket
                                </p>

                                <h3 class="mt-1 text-xl font-bold text-gray-900">
                                    Boarding Pass
                                </h3>

                                <p class="mt-1 text-sm text-gray-600">
                                    Tunjukkan tiket ini kepada petugas saat check-in.
                                </p>
                            </div>

                            <span class="inline-flex w-fit items-center gap-2 rounded-full bg-green-100 px-3 py-1.5 text-xs font-bold text-green-800">
                                <span>✓</span> PEMBAYARAN LUNAS
                            </span>
                        </div>

                        {{-- Desain Tiket --}}
                        <div class="overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm">

                            {{-- Header Tiket --}}
                            <div class="bg-indigo-700 px-5 py-5 text-white sm:px-7">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-indigo-200">
                                            LAPANGIN
                                        </p>
                                        <h4 class="mt-1 text-xl font-bold">
                                            E-Ticket Lapangan
                                        </h4>
                                    </div>

                                    <div class="rounded-lg border border-white/30 px-3 py-2 text-center">
                                        <p class="text-[10px] uppercase tracking-wider text-indigo-200">
                                            Status
                                        </p>
                                        <p class="mt-1 text-xs font-bold">
                                            TERKONFIRMASI
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Isi Tiket --}}
                            <div class="grid md:grid-cols-[1fr_auto]">
                                <div class="p-5 sm:p-7">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        Nama Lapangan
                                    </p>

                                    <h5 class="mt-2 text-2xl font-bold text-gray-900">
                                        {{ $jadwal->lapangan?->nama ?? 'Nama lapangan tidak tersedia' }}
                                    </h5>

                                    <div class="mt-6 grid gap-x-6 gap-y-5 sm:grid-cols-2">
                                        <div>
                                            <p class="text-xs text-gray-500">Tanggal</p>
                                            <p class="mt-1 font-semibold text-gray-900">
                                                {{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('d F Y') }}
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs text-gray-500">Waktu Bermain</p>
                                            <p class="mt-1 font-semibold text-gray-900">
                                                {{ substr((string) $jadwal->jam_mulai, 0, 5) }}
                                                – {{ substr((string) $jadwal->jam_selesai, 0, 5) }}
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs text-gray-500">Durasi</p>
                                            <p class="mt-1 font-semibold text-gray-900">
                                                @php
                                                    $mulaiMenit = \Carbon\Carbon::parse($jadwal->jam_mulai)->hour * 60
                                                        + \Carbon\Carbon::parse($jadwal->jam_mulai)->minute;
                                                    $selesaiMenit = \Carbon\Carbon::parse($jadwal->jam_selesai)->hour * 60
                                                        + \Carbon\Carbon::parse($jadwal->jam_selesai)->minute;
                                                    $durasiMenit = $selesaiMenit - $mulaiMenit;
                                                @endphp

                                                {{ $durasiMenit > 0 ? floor($durasiMenit / 60) . ' jam' . ($durasiMenit % 60 ? ' ' . ($durasiMenit % 60) . ' menit' : '') : 'Sesuai jadwal' }}
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs text-gray-500">Total Pembayaran</p>
                                            <p class="mt-1 font-bold text-indigo-700">
                                                Rp{{ number_format((float) $jadwal->harga_disepakati, 0, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-6 border-t border-dashed border-gray-200 pt-5">
                                        <p class="text-xs text-gray-500">
                                            Kode E-Ticket / Booking
                                        </p>

                                        <p class="mt-1 break-all font-mono text-lg font-bold tracking-wide text-gray-900">
                                            {{ $jadwal->kode_qr ?: 'Kode tiket belum tersedia' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- QR Code --}}
                                <div class="flex flex-col items-center justify-center border-t border-dashed border-gray-200 bg-gray-50 p-6 text-center md:border-l md:border-t-0">
                                    @if ($jadwal->kode_qr)
                                        <div class="rounded-xl border border-gray-200 bg-white p-3">
                                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(160)->margin(1)->generate($jadwal->kode_qr) !!}
                                        </div>

                                        <p class="mt-3 text-sm font-semibold text-gray-900">
                                            QR Code Tiket
                                        </p>

                                        <p class="mt-1 max-w-[190px] text-xs leading-5 text-gray-500">
                                            Siapkan kode ini untuk membantu petugas memeriksa booking Anda.
                                        </p>
                                    @else
                                        <div class="flex h-40 w-40 items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-white p-5">
                                            <p class="text-sm text-gray-500">
                                                QR Code belum tersedia
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Footer Tiket --}}
                            <div class="border-t border-gray-100 bg-gray-50 px-5 py-4 sm:px-7">
                                <p class="text-xs leading-5 text-gray-500">
                                    Harap datang sesuai jadwal dan tunjukkan e-ticket kepada petugas.
                                    Tiket ini merupakan tampilan informasi booking; validasi check-in
                                    tetap mengikuti sistem petugas.
                                </p>
                            </div>
                        </div>

                        {{-- Tombol Cetak --}}
                        <div class="mt-5 flex flex-wrap gap-3 print:hidden">
                            <button type="button"
                                    onclick="window.print()"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-700 px-5 py-3 font-semibold text-white transition hover:bg-indigo-800">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-5 w-5"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
                                </svg>
                                Cetak / Simpan PDF
                            </button>
                        </div>
                    </div>
                @else
                    <div class="border-t border-amber-100 bg-amber-50 p-5 sm:p-6">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                                !
                            </div>

                            <div>
                                <h3 class="font-bold text-amber-900">
                                    E-Ticket Belum Tersedia
                                </h3>

                                <p class="mt-1 text-sm leading-6 text-amber-800">
                                    E-ticket akan ditampilkan setelah pembayaran dinyatakan lunas.
                                    Status saat ini: {{ $statusLabel }}.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </article>
        @empty
            {{-- Tampilan Kosong --}}
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-8 w-8"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                    </svg>
                </div>

                <h2 class="mt-5 text-xl font-bold text-gray-900">
                    Belum Ada Pesanan
                </h2>

                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-600">
                    Booking lapangan Anda akan muncul di sini setelah pesanan tersedia.
                </p>
            </div>
        @endforelse

        {{-- Pagination --}}
        @if ($jadwals->hasPages())
            <div class="mt-6">
                {{ $jadwals->links() }}
            </div>
        @endif

    </div>
</div>

{{-- Pengaturan Cetak --}}
<style>
    @media print {
        body {
            background: white !important;
        }

        nav, header, footer {
            print-color-adjust: exact;
        }

        .print\:hidden {
            display: none !important;
        }

        article {
            break-inside: avoid;
            box-shadow: none !important;
        }
    }
</style>
@endsection