@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-8 sm:py-12">
    <div class="mx-auto max-w-5xl">

        {{-- Header --}}
        <div class="mb-8">
            <a href="{{ route('katalog.show', $lapangan->id) }}"
               class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-800">
                &larr; Kembali ke Lapangan
            </a>

            <h1 class="mt-4 text-3xl font-bold tracking-tight text-gray-900">
                Checkout Booking
            </h1>

            <p class="mt-2 text-gray-600">
                Periksa detail pesanan Anda sebelum melanjutkan pembayaran.
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">

            {{-- Detail Pesanan --}}
            <div class="space-y-6 lg:col-span-2">

                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-5">
                        <h2 class="text-lg font-bold text-gray-900">
                            Detail Pesanan
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Informasi jadwal lapangan yang Anda pilih.
                        </p>
                    </div>

                    <div class="space-y-5 p-6">
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
                                <p class="text-sm text-gray-500">Lapangan pilihan</p>
                                <h3 class="mt-1 text-xl font-bold text-gray-900">
                                    {{ $lapangan->nama }}
                                </h3>
                                <span class="mt-2 inline-flex rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                                    Booking Lapangan
                                </span>
                            </div>
                        </div>

                        <hr class="border-gray-100">

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="rounded-xl bg-gray-50 p-4">
                                <p class="text-sm text-gray-500">Tanggal Booking</p>
                                <p class="mt-2 font-semibold text-gray-900">
                                    {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-gray-50 p-4">
                                <p class="text-sm text-gray-500">Jam Booking</p>
                                <p class="mt-2 font-semibold text-gray-900">
                                    {{ $jamMulai }} – {{ $jamSelesai }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-gray-50 p-4">
                                <p class="text-sm text-gray-500">Durasi Sewa</p>
                                <p class="mt-2 font-semibold text-gray-900">
                                    {{ $durasi }} jam
                                </p>
                            </div>

                            <div class="rounded-xl bg-gray-50 p-4">
                                <p class="text-sm text-gray-500">Harga per Jam</p>
                                <p class="mt-2 font-semibold text-gray-900">
                                    Rp{{ number_format($hargaPerJam, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Informasi Pembayaran --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-5 w-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 8c-2.21 0-4 1.12-4 2.5S9.79 13 12 13s4 1.12 4 2.5S14.21 18 12 18m0-10V6m0 2v10m0-12c1.67 0 3.14.65 3.72 1.58M12 18v2m0-2c-1.67 0-3.14-.65-3.72-1.58M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <div>
                            <h2 class="font-bold text-gray-900">
                                Informasi Pembayaran
                            </h2>
                            <p class="mt-1 text-sm leading-6 text-gray-600">
                                Total pembayaran dihitung berdasarkan durasi sewa dan harga lapangan.
                                Pastikan seluruh informasi pesanan sudah benar.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ringkasan Pembayaran --}}
            <aside>
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm lg:sticky lg:top-6">
                    <div class="bg-indigo-700 px-6 py-5 text-white">
                        <h2 class="text-lg font-bold">Ringkasan Pembayaran</h2>
                        <p class="mt-1 text-sm text-indigo-100">
                            Rincian biaya pesanan Anda.
                        </p>
                    </div>

                    <div class="space-y-4 p-6">
                        <div class="flex justify-between gap-4 text-sm">
                            <span class="text-gray-600">Harga sewa</span>
                            <span class="font-medium text-gray-900">
                                Rp{{ number_format($hargaPerJam, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4 text-sm">
                            <span class="text-gray-600">Durasi</span>
                            <span class="font-medium text-gray-900">
                                {{ $durasi }} jam
                            </span>
                        </div>

                        <hr class="border-gray-100">

                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-gray-900">Total Harga</p>
                                <p class="mt-1 text-xs text-gray-500">
                                    Belum termasuk biaya tambahan jika ada.
                                </p>
                            </div>

                            <p class="text-xl font-bold text-indigo-700">
                                Rp{{ number_format($totalHarga, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <p class="text-sm font-semibold text-amber-900">
                                Periksa kembali pesanan
                            </p>
                            <p class="mt-1 text-xs leading-5 text-amber-800">
                                Pastikan tanggal, jam, dan durasi sudah sesuai sebelum melanjutkan.
                            </p>
                        </div>

                        {{-- Tombol UI; proses konfirmasi belum terhubung --}}
                        <button type="button"
                                disabled
                                class="flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl bg-indigo-300 px-5 py-3.5 font-semibold text-white opacity-80"
                                title="Menunggu integrasi proses pembayaran">
                            Lanjutkan Pembayaran
                            <span aria-hidden="true">&rarr;</span>
                        </button>

                        <p class="text-center text-xs leading-5 text-gray-500">
                            Proses pembayaran akan tersedia setelah integrasi backend selesai.
                        </p>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-center gap-2 text-xs text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-7a2 2 0 00-2-2H6a2 2 0 00-2 2v7a2 2 0 002 2zm10-11V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Informasi pesanan ditampilkan secara aman
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection