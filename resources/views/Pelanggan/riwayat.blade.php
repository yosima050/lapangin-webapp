@extends('layouts.pelanggan')

@section('title', 'Riwayat Booking')

@section('content')
@php
    $countSemua = $jadwals->count();
    $countAktif = 0;
    $countSelesai = 0;
    $countDibatalkan = 0;

    foreach ($jadwals as $j) {
        $jIsCancelled = $j->isCancelled();
        $jTanggalStr = $j->tanggal instanceof \Carbon\Carbon ? $j->tanggal->format('Y-m-d') : (string) $j->tanggal;
        $jJamSelesai = in_array(substr((string)$j->jam_selesai, 0, 5), ['24:00', '00:00'], true) ? '23:59:59' : (string)$j->jam_selesai;
        $jEndTime = \Carbon\Carbon::parse($jTanggalStr . ' ' . $jJamSelesai);
        $jIsPast = now()->gt($jEndTime);
        $jIsSelesai = ! $jIsCancelled && ($j->status === 'selesai' || $jIsPast);

        if ($jIsCancelled) {
            $countDibatalkan++;
        } elseif ($jIsSelesai) {
            $countSelesai++;
        } else {
            $countAktif++;
        }
    }
@endphp
<div x-data="{
    activeTab: '{{ $countAktif > 0 ? "aktif" : "all" }}',
    searchQuery: '',
    bookingItems: [
        @foreach ($jadwals as $j)
            @php
                $rawKode = (string) ($j->kode_booking ?? $j->id);
                $displayKode = strlen($rawKode) > 12 ? '#BK-' . strtoupper(substr($rawKode, 0, 8)) : '#BK-' . $rawKode;
                $namaLap = $j->lapangan?->nama ?? 'Lapangan';
                $jIsCancelled = $j->isCancelled();
                $jTanggalStr = $j->tanggal instanceof \Carbon\Carbon ? $j->tanggal->format('Y-m-d') : (string) $j->tanggal;
                $jJamSelesai = in_array(substr((string)$j->jam_selesai, 0, 5), ['24:00', '00:00'], true) ? '23:59:59' : (string)$j->jam_selesai;
                $jEndTime = \Carbon\Carbon::parse($jTanggalStr . ' ' . $jJamSelesai);
                $jIsPast = now()->gt($jEndTime);
                $jIsSelesai = ! $jIsCancelled && ($j->status === 'selesai' || $jIsPast);
                $tabCat = $jIsCancelled ? 'dibatalkan' : ($jIsSelesai ? 'selesai' : 'aktif');
                $searchKeywords = strtolower($rawKode . ' ' . $displayKode . ' ' . $namaLap . ' ' . ($j->lapangan?->kategori ?? ''));
            @endphp
            {
                id: '{{ $j->id }}',
                category: '{{ $tabCat }}',
                search: {{ json_encode($searchKeywords) }}
            },
        @endforeach
    ],
    get visibleCount() {
        return this.bookingItems.filter(item => {
            const matchesTab = (this.activeTab === 'all') || (this.activeTab === item.category);
            if (!matchesTab) return false;
            if (!this.searchQuery.trim()) return true;
            return item.search.includes(this.searchQuery.toLowerCase().trim());
        }).length;
    },
    showTicketModal: false,
    activeTicket: null,
    payModalOpen: false,
    selectedJadwalId: null,
    selectedLapanganNama: '',
    selectedTanggal: '',
    selectedJamMulai: '',
    selectedJamSelesai: '',
    selectedHarga: 0,
    checkoutUrl: '',
    tipeBayar: 'dp',
    isProcessing: false,
    errorMessage: '',
    modalRemainingSec: 600,
    isModalExpired: false,

    // Auto-Penalty Cancellation State (Sprint 5)
    cancelModalOpen: false,
    cancelLoading: false,
    cancelSubmitting: false,
    cancelJadwalId: null,
    cancelLapanganNama: '',
    cancelTanggal: '',
    cancelJam: '',
    cancelPenalty: null,
    cancelFormUrl: '',

    openCancelModal(id, nama, tgl, jam) {
        this.cancelJadwalId = id;
        this.cancelLapanganNama = nama;
        this.cancelTanggal = tgl;
        this.cancelJam = jam;
        this.cancelLoading = true;
        this.cancelSubmitting = false;
        this.cancelPenalty = null;
        this.cancelFormUrl = '/pelanggan/booking/' + id + '/cancel';
        this.cancelModalOpen = true;

        fetch('/pelanggan/booking/' + id + '/cancel-preview', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            this.cancelPenalty = data.penalty;
            this.cancelLoading = false;
        })
        .catch(err => {
            console.error(err);
            this.cancelLoading = false;
        });
    },

    openPayModal(id, nama, tgl, mulai, selesai, harga, url, remainingSec) {
        this.selectedJadwalId = id;
        this.selectedLapanganNama = nama;
        this.selectedTanggal = tgl;
        this.selectedJamMulai = mulai;
        this.selectedJamSelesai = selesai;
        this.selectedHarga = harga;
        this.checkoutUrl = url;
        this.tipeBayar = 'dp';
        this.errorMessage = '';
        this.isProcessing = false;
        this.isModalExpired = false;
        this.modalRemainingSec = (typeof remainingSec === 'number' && remainingSec >= 0) ? remainingSec : 600;

        if (this.modalTimerInterval) clearInterval(this.modalTimerInterval);

        const self = this;
        this.modalTimerInterval = setInterval(() => {
            if (self.modalRemainingSec <= 0) {
                self.modalRemainingSec = 0;
                self.isModalExpired = true;
                self.errorMessage = 'Batas waktu pembayaran (10 menit) telah habis. Reservasi otomatis dibatalkan.';
                clearInterval(self.modalTimerInterval);
                self.expireBooking(self.selectedJadwalId);
            } else {
                self.modalRemainingSec--;
            }
        }, 1000);

        this.payModalOpen = true;
    },

    formatTimerText(sec) {
        const s = Math.max(0, parseInt(sec || 0, 10));
        const m = Math.floor(s / 60);
        const rem = s % 60;
        return String(m).padStart(2, '0') + ':' + String(rem).padStart(2, '0');
    },

    expireBooking(jadwalId) {
        if (!jadwalId) return;
        fetch('/checkout/expire/' + jadwalId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(() => {
            setTimeout(() => {
                window.location.reload();
            }, 2500);
        }).catch(console.error);
    },

    nominalBayar() {
        if (this.tipeBayar === 'full') {
            return this.selectedHarga;
        }
        return Math.round(this.selectedHarga * 0.5);
    },

    formatRupiah(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
    },

    processPayment() {
        if (this.isProcessing) return;
        if (this.isModalExpired || this.modalRemainingSec <= 0) {
            this.errorMessage = 'Batas waktu pembayaran telah kedaluwarsa. Silakan pesan ulang di katalog.';
            return;
        }

        this.isProcessing = true;
        this.errorMessage = '';

        const self = this;
        fetch('{{ route('checkout.pay') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                jadwal_id: this.selectedJadwalId,
                tipe_bayar: this.tipeBayar
            })
        })
        .then(function (res) {
            if (!res.ok) {
                return res.json().then(function (e) {
                    throw new Error(e.message || 'Gagal memproses pembayaran');
                });
            }
            return res.json();
        })
        .then(function (data) {
            if (!data.snap_token) {
                throw new Error('Snap token tidak diterima dari server.');
            }

            if (typeof window.snap === 'undefined') {
                window.location.href = self.checkoutUrl + '&tipe_bayar=' + self.tipeBayar;
                return;
            }

            window.snap.pay(data.snap_token, {
                onSuccess: function (result) {
                    window.location.href = '{{ route('pelanggan.booking.index') }}?status=success';
                },
                onPending: function (result) {
                    window.location.href = '{{ route('pelanggan.booking.index') }}?status=pending';
                },
                onError: function (result) {
                    self.errorMessage = 'Pembayaran gagal diproses atau dibatalkan. Silakan coba kembali.';
                    self.isProcessing = false;
                },
                onClose: function () {
                    self.isProcessing = false;
                }
            });
        })
        .catch(function (err) {
            self.errorMessage = err.message || 'Terjadi kesalahan sistem, silakan coba kembali.';
            self.isProcessing = false;
        });
    }
}" class="space-y-4 sm:space-y-6" style="font-family: 'Plus Jakarta Sans', sans-serif;">

        {{-- Header Riwayat Booking --}}
        <div class="mb-4 sm:mb-8 overflow-hidden rounded-2xl bg-[#1a146b] text-white shadow-lg sm:rounded-3xl">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between px-4 py-4 sm:px-8 sm:py-9 lg:px-10 gap-3.5 sm:gap-6">
                <div>
                    <h1 class="text-lg font-extrabold tracking-tight sm:text-3xl sm:font-bold lg:text-4xl leading-snug">
                        Riwayat Booking &amp; Jadwal Saya
                    </h1>

                    <p class="mt-1.5 sm:mt-3 max-w-xl text-xs leading-relaxed text-indigo-100/90 sm:text-base sm:leading-6 sm:text-indigo-100">
                        Pantau tiket akses gerbang, unduh bukti pembayaran lunas, atau kelola permohonan penjadwalan ulang lapangan secara mandiri.
                    </p>
                </div>

                {{-- Statistik Ringkasan ala Figma --}}
                <div class="grid grid-cols-2 gap-2 sm:gap-3 rounded-xl sm:rounded-2xl bg-white/10 py-2.5 px-3 sm:p-4 backdrop-blur-md border border-white/10 shrink-0">
                    <div class="flex sm:block items-center justify-center gap-2 text-center px-2 sm:px-4 border-r border-white/15">
                        <p class="text-lg sm:text-2xl font-extrabold text-white leading-none sm:leading-normal">
                            {{ $countAktif }}
                        </p>
                        <p class="sm:mt-0.5 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-indigo-200 whitespace-nowrap">
                            Jadwal Aktif
                        </p>
                    </div>
                    <div class="flex sm:block items-center justify-center gap-2 text-center px-2 sm:px-4">
                        <p class="text-lg sm:text-2xl font-extrabold text-white leading-none sm:leading-normal">{{ $countSelesai }}</p>
                        <p class="sm:mt-0.5 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-indigo-200 whitespace-nowrap">
                            Selesai
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notifikasi --}}
        @if (session('success'))
            <div class="mb-4 sm:mb-6 flex items-start gap-2.5 sm:gap-3 rounded-xl border border-green-200 bg-green-50 p-3.5 sm:p-4 text-green-800">
                <svg class="w-5 h-5 shrink-0 stroke-current text-green-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-xs sm:text-sm font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Filter & Pencarian ala Figma --}}
        <div class="mb-4 sm:mb-6 flex flex-col gap-3 sm:gap-4 sm:flex-row sm:items-center sm:justify-between">
            {{-- Tab Filter Status (1 baris kompak 4 kolom di HP, pill fleksibel di Desktop) --}}
            <div class="grid grid-cols-4 sm:flex sm:flex-wrap items-center gap-1 sm:gap-2 rounded-xl sm:rounded-2xl bg-gray-200/70 sm:bg-gray-200/60 p-1 sm:p-1.5 text-[11px] sm:text-xs font-semibold text-gray-600">
                <button type="button"
                        @click="activeTab = 'all'"
                        :class="activeTab === 'all' ? 'bg-[#1a146b] text-white font-bold shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                        class="inline-flex items-center justify-center rounded-lg sm:rounded-xl px-1.5 py-2 sm:px-4 sm:py-2 whitespace-nowrap transition-all duration-150 active:scale-95 cursor-pointer">
                    Semua ({{ $countSemua }})
                </button>
                <button type="button"
                        @click="activeTab = 'aktif'"
                        :class="activeTab === 'aktif' ? 'bg-[#1a146b] text-white font-bold shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                        class="inline-flex items-center justify-center gap-1 sm:gap-1.5 rounded-lg sm:rounded-xl px-1.5 py-2 sm:px-4 sm:py-2 whitespace-nowrap transition-all duration-150 active:scale-95 cursor-pointer">
                    <span>Aktif<span class="hidden sm:inline"> &amp; Mendatang</span></span>
                    <span :class="activeTab === 'aktif' ? 'bg-[#acf847] text-[#1a146b]' : 'bg-gray-300 text-gray-700'"
                          class="flex h-4 min-w-4 px-1 items-center justify-center rounded-full text-[10px] font-extrabold transition-colors duration-150">
                        {{ $countAktif }}
                    </span>
                </button>
                <button type="button"
                        @click="activeTab = 'selesai'"
                        :class="activeTab === 'selesai' ? 'bg-[#1a146b] text-white font-bold shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                        class="inline-flex items-center justify-center rounded-lg sm:rounded-xl px-1.5 py-2 sm:px-4 sm:py-2 whitespace-nowrap transition-all duration-150 active:scale-95 cursor-pointer">
                    Selesai ({{ $countSelesai }})
                </button>
                <button type="button"
                        @click="activeTab = 'dibatalkan'"
                        :class="activeTab === 'dibatalkan' ? 'bg-[#1a146b] text-white font-bold shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                        class="inline-flex items-center justify-center rounded-lg sm:rounded-xl px-1.5 py-2 sm:px-4 sm:py-2 whitespace-nowrap transition-all duration-150 active:scale-95 cursor-pointer">
                    <span class="sm:hidden">Batal</span><span class="hidden sm:inline">Dibatalkan</span>&nbsp;({{ $countDibatalkan }})
                </button>
            </div>

            {{-- Input Pencarian --}}
            <div class="relative w-full sm:w-72">
                <input type="text"
                       x-model="searchQuery"
                       placeholder="Cari lapangan..."
                       class="w-full rounded-xl sm:rounded-2xl border border-gray-200 bg-white py-2.5 pl-10 pr-9 text-xs shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <button type="button"
                        x-show="searchQuery.trim().length > 0"
                        @click="searchQuery = ''"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 cursor-pointer">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Sub-header Judul Bagian --}}
        <div class="mb-3 sm:mb-4 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0">
                <span class="h-2 w-2 sm:h-2.5 sm:w-2.5 shrink-0 rounded-full bg-green-500"></span>
                <h2 class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-gray-800 truncate"
                    x-text="activeTab === 'all' ? 'Semua Jadwal Reservasi' : (activeTab === 'aktif' ? 'Jadwal Pertandingan Aktif & Mendatang' : (activeTab === 'selesai' ? 'Riwayat Pertandingan Selesai' : 'Daftar Booking Dibatalkan'))">
                    Jadwal Pertandingan Terdekat
                </h2>
            </div>
            <p class="text-[10px] sm:text-[11px] text-gray-400 shrink-0 whitespace-nowrap">
                <span class="hidden sm:inline">Update sinkronisasi: </span>Baru saja
            </p>
        </div>

        {{-- Daftar Kartu Booking Aktif --}}
        @forelse ($jadwals as $jadwal)
            @php
                $isDibatalkan = in_array($jadwal->status, ['dibatalkan', 'cancelled', 'rejected']);

                $isPaidOrVerified = ! $isDibatalkan && (in_array($jadwal->status_pembayaran, [
                    'lunas_online',
                    'lunas_kasir',
                    'dp_terverifikasi'
                ]) || $jadwal->status === 'terverifikasi');

                $isLunas = in_array($jadwal->status_pembayaran, [
                    'lunas_online',
                    'lunas_kasir'
                ]);

                $isDP = $jadwal->status_pembayaran === 'dp_terverifikasi';
                $isUnpaid = ! $isPaidOrVerified && ! $isDibatalkan;

                $statusLabel = ucfirst(str_replace(
                    '_',
                    ' ',
                    $jadwal->status_pembayaran ?? 'belum_bayar'
                ));

                // Format kode booking pendek
                $rawKode = (string) ($jadwal->kode_booking ?? $jadwal->id);
                $displayKode = strlen($rawKode) > 12 ? '#BK-' . strtoupper(substr($rawKode, 0, 8)) : '#BK-' . $rawKode;

                // Menghitung durasi jam
                $mulaiMenit = \Carbon\Carbon::parse($jadwal->jam_mulai)->hour * 60 + \Carbon\Carbon::parse($jadwal->jam_mulai)->minute;
                $selesaiMenit = \Carbon\Carbon::parse($jadwal->jam_selesai)->hour * 60 + \Carbon\Carbon::parse($jadwal->jam_selesai)->minute;
                $durasiJam = max(1, round(($selesaiMenit - $mulaiMenit) / 60));
                $tanggalStr = $jadwal->tanggal instanceof \Carbon\Carbon ? $jadwal->tanggal->toDateString() : (string) $jadwal->tanggal;
                $namaLapangan = $jadwal->lapangan?->nama ?? 'Lapangan';

                $jJamSelesai = in_array(substr((string)$jadwal->jam_selesai, 0, 5), ['24:00', '00:00'], true) ? '23:59:59' : (string)$jadwal->jam_selesai;
                $jEndTime = \Carbon\Carbon::parse($tanggalStr . ' ' . $jJamSelesai);
                $jIsPast = now()->gt($jEndTime);
                $jIsSelesai = ! $isDibatalkan && ($jadwal->status === 'selesai' || $jIsPast);
                $tabCategory = $isDibatalkan ? 'dibatalkan' : ($jIsSelesai ? 'selesai' : 'aktif');
                $searchKeywords = strtolower($rawKode . ' ' . $displayKode . ' ' . $namaLapangan . ' ' . ($jadwal->lapangan?->kategori ?? ''));

                // Helper Reschedule & Pembatalan (Sprint 5)
                $kickoff = \Carbon\Carbon::parse($tanggalStr . ' ' . substr((string) $jadwal->jam_mulai, 0, 5));
                $hoursUntilKickoff = now()->diffInHours($kickoff, false);
                $pendingReschedule = $jadwal->reschedules ? $jadwal->reschedules->firstWhere('status', 'diajukan') : null;
                $canReschedule = ! $isDibatalkan && empty($jadwal->checked_in_at) && ! $pendingReschedule && $hoursUntilKickoff >= 24;
                $canCancel = ! $isDibatalkan && empty($jadwal->checked_in_at) && $jadwal->status !== 'selesai' && ! $jIsPast;
            @endphp

            <article x-data="{ showModal: false }"
                     x-show="(activeTab === 'all' || activeTab === '{{ $tabCategory }}') && (!searchQuery.trim() || {{ json_encode($searchKeywords) }}.includes(searchQuery.toLowerCase().trim()))"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="mb-4 sm:mb-6 overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-sm transition-all">

                {{-- Baris Atas: Waktu Pemesanan & Badge Status --}}
                <div class="flex flex-wrap items-center justify-between border-b border-gray-100 bg-indigo-50/30 px-3.5 py-2.5 sm:px-5 sm:py-3 gap-2">
                    <div class="flex items-center gap-1.5 text-[11px] sm:text-xs text-gray-500">
                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Dipesan: {{ \Carbon\Carbon::parse($jadwal->created_at)->translatedFormat('d M Y, H:i') }} WIB</span>
                    </div>

                    {{-- Badge Status Utama --}}
                    @if ($isDibatalkan)
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 sm:px-3 sm:py-1 text-[10px] sm:text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span>
                            Dibatalkan
                        </span>
                    @elseif ($jIsSelesai)
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 sm:px-3 sm:py-1 text-[10px] sm:text-xs font-bold bg-gray-100 text-gray-700 border border-gray-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-gray-500"></span>
                            Pertandingan Selesai
                        </span>
                    @elseif ($isLunas)
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 sm:px-3 sm:py-1 text-[10px] sm:text-xs font-bold bg-green-100 text-green-800 border border-green-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-green-600"></span>
                            Lunas 100% (Siap Main)
                        </span>
                    @elseif ($isDP)
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 sm:px-3 sm:py-1 text-[10px] sm:text-xs font-bold bg-[#acf847]/40 text-emerald-950 border border-[#acf847]">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                            DP 50% Terverifikasi (Siap Main)
                        </span>
                    @else
                        @php
                            $elapsedSec = abs(now()->diffInSeconds(\Carbon\Carbon::parse($jadwal->created_at)));
                            $remainingSec = max(0, 600 - $elapsedSec);
                        @endphp
                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 sm:px-3 sm:py-1 text-[10px] sm:text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                Belum Dibayar
                            </span>
                            <span class="inline-flex items-center gap-1 sm:gap-1.5 rounded-full px-2 py-0.5 sm:px-2.5 sm:py-1 text-[10px] sm:text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200"
                                  data-card-timer="{{ $jadwal->id }}"
                                  data-remaining="{{ $remainingSec }}">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="font-mono">{{ sprintf('%02d:%02d', floor($remainingSec / 60), $remainingSec % 60) }}</span>
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Konten Utama 3 Kolom ala Figma --}}
                <div class="p-3.5 sm:p-5">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 sm:gap-5 items-center">
                        
                        {{-- Kolom 1: Thumbnail Venue & Nama Lapangan --}}
                        <div class="lg:col-span-4 flex items-stretch gap-3 sm:gap-4">
                        {{-- Gambar Full Height ala Katalog --}}
                        <div class="relative w-20 min-h-[84px] sm:w-32 shrink-0 overflow-hidden rounded-xl bg-gray-900 border border-gray-100 self-stretch flex items-center justify-center">
                            @php
                                $fotoUrl = $jadwal->lapangan?->foto_url ?? asset('storage/' . $jadwal->lapangan?->foto);
                                $fallbackAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($namaLapangan) . '&background=1a146b&color=acf847&size=512';
                            @endphp

                            <img src="{{ $jadwal->lapangan?->foto ? $fotoUrl : $fallbackAvatar }}"
                                alt="{{ $namaLapangan }}"
                                class="h-full w-full object-cover object-center transition duration-300 hover:scale-105"
                                onerror="this.onerror=null; this.src='{{ $fallbackAvatar }}';">

                            <span class="absolute top-1.5 left-1.5 sm:top-2 sm:left-2 rounded-md bg-[#1a146b]/90 backdrop-blur-md px-1.5 sm:px-2 py-0.5 text-[8px] font-bold text-[#acf847] uppercase tracking-wider shadow-xs">
                                {{ $jadwal->lapangan?->kategori ?? 'VENUE' }}
                            </span>
                        </div>

                        {{-- Detail Nama & Spesifikasi Nyata --}}
                        <div class="min-w-0 flex-1 py-0.5 sm:py-1 flex flex-col justify-center">
                            <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                {{ $displayKode }}
                            </p>
                            <h3 class="text-sm sm:text-base font-bold text-gray-900 leading-snug line-clamp-2 sm:truncate">
                                {{ $jadwal->lapangan?->nama ?? 'Lapangan Tidak Tersedia' }}
                            </h3>

                            @php
                                $addonItems = [];
                                if ($jadwal->relationLoaded('detailSewaPerlengkapans') && $jadwal->detailSewaPerlengkapans->isNotEmpty()) {
                                    foreach ($jadwal->detailSewaPerlengkapans as $det) {
                                        $namaAddon = $det->perlengkapan?->nama;
                                        if ($namaAddon) {
                                            $addonItems[] = $namaAddon . ($det->jumlah > 1 ? ' (' . $det->jumlah . 'x)' : '');
                                        }
                                    }
                                }
                                if (empty($addonItems)) {
                                    if (!empty($jadwal->sewa_rompi)) {
                                        $addonItems[] = 'Rompi';
                                    }
                                    if (!empty($jadwal->sewa_bola)) {
                                        $addonItems[] = 'Bola';
                                    }
                                }
                            @endphp

                            @if (!empty($addonItems))
                                <p class="mt-1 sm:mt-1.5 text-[11px] sm:text-xs text-[#416900] font-semibold flex items-center gap-1.5">
                                    <x-lucide-package-plus class="w-3.5 h-3.5 shrink-0" />
                                    <span class="truncate">Add-on: {{ implode(', ', $addonItems) }}</span>
                                </p>
                            @elseif ($jadwal->lapangan?->kategori)
                                <p class="mt-1 text-[11px] sm:text-xs text-gray-500 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-[#416900] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="2" y="5" width="20" height="14" rx="2" />
                                        <line x1="12" y1="5" x2="12" y2="19" />
                                        <circle cx="12" cy="12" r="2.5" />
                                        <path d="M2 9h3v6H2" />
                                        <path d="M22 9h-3v6h3" />
                                    </svg>
                                    <span class="truncate">{{ $jadwal->lapangan->kategori }}</span>
                                </p>
                            @endif
                        </div>
                    </div>

                        {{-- Kolom 2: Detail Jadwal & Pembayaran --}}
                        <div class="lg:col-span-5 rounded-xl sm:rounded-2xl bg-indigo-50/40 border border-indigo-50 p-3 sm:p-4">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Jadwal Kickoff</p>
                                    <div class="mt-1 flex items-center gap-1.5 sm:gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-[#1a146b] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                                        </svg>
                                        <span class="text-xs sm:text-sm font-bold text-gray-900 truncate">
                                            {{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('l, d F Y') }}
                                        </span>
                                    </div>
                                    <div class="mt-1 flex items-center gap-1.5 sm:gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-[#1a146b] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-xs sm:text-sm font-extrabold text-[#1a146b]">
                                            {{ substr((string) $jadwal->jam_mulai, 0, 5) }} – {{ substr((string) $jadwal->jam_selesai, 0, 5) }} <span class="text-[11px] sm:text-xs font-normal text-gray-500">WIB</span>
                                        </span>
                                    </div>
                                </div>
                                <span class="shrink-0 whitespace-nowrap rounded-lg bg-indigo-100 px-2 py-0.5 sm:px-2.5 sm:py-1 text-[10px] sm:text-[11px] font-bold text-indigo-900">
                                    {{ $durasiJam }} Jam Sesi
                                </span>
                            </div>

                            <div class="mt-2.5 sm:mt-3 border-t border-indigo-100/60 pt-2 flex justify-between items-center text-xs">
                                <span class="text-gray-500">Total Harga:</span>
                                <span class="font-bold text-gray-900">Rp{{ number_format((float) $jadwal->harga_disepakati, 0, ',', '.') }}</span>
                            </div>

                            {{-- Status Pengajuan Reschedule (Sprint 5) --}}
                            @if ($pendingReschedule)
                                <div class="mt-3 rounded-xl bg-amber-50 border border-amber-200 p-2.5 text-xs text-amber-900 flex items-start gap-2">
                                    <span class="text-sm">⏳</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold">Permohonan Reschedule Menunggu Kasir</p>
                                        <p class="text-[11px] text-amber-700 mt-0.5">
                                            Jadwal baru: <strong>{{ \Carbon\Carbon::parse($pendingReschedule->tanggal_baru)->translatedFormat('d M Y') }}</strong> ({{ substr((string) $pendingReschedule->jam_mulai_baru, 0, 5) }} - {{ substr((string) $pendingReschedule->jam_selesai_baru, 0, 5) }} WIB).
                                        </p>
                                    </div>
                                </div>
                            @endif

                            {{-- Catatan Denda Penalti Jika Dibatalkan (Sprint 5) --}}
                            @if ($isDibatalkan)
                                <div class="mt-3 rounded-xl bg-red-50 border border-red-200 p-2.5 text-xs text-red-900 space-y-1">
                                    <div class="flex items-center justify-between font-bold">
                                        <span>Denda Penalti Pembatalan:</span>
                                        <span class="text-red-700">Rp{{ number_format((float) $jadwal->denda_pembatalan, 0, ',', '.') }}</span>
                                    </div>
                                    @if ($jadwal->catatan_kasir)
                                        <p class="text-[10px] text-red-700/80 italic leading-snug">
                                            {{ $jadwal->catatan_kasir }}
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Kolom 3: Tombol Aksi Kanan --}}
                        <div class="lg:col-span-3 flex flex-col gap-2">
                            @if ($isPaidOrVerified)
                                <button type="button" @click="showModal = true" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#1a146b] px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-900 active:scale-[0.98] transition cursor-pointer">
                                    <x-lucide-qr-code class="w-4 h-4 shrink-0" />
                                    Lihat E-Tiket Masuk &amp; QR
                                </button>
                            @elseif ($isUnpaid)
                                @php
                                    $checkoutUrl = route('checkout.index', [
                                        'lapangan_id' => $jadwal->lapangan_id,
                                        'tanggal' => $tanggalStr,
                                        'jam_mulai' => substr((string) $jadwal->jam_mulai, 0, 5),
                                        'durasi' => $durasiJam,
                                    ]);
                                @endphp
                                <button type="button"
                                        @click="openPayModal('{{ $jadwal->id }}', '{{ addslashes($namaLapangan) }}', '{{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('l, d F Y') }}', '{{ substr((string) $jadwal->jam_mulai, 0, 5) }}', '{{ substr((string) $jadwal->jam_selesai, 0, 5) }}', {{ (float) $jadwal->harga_disepakati }}, '{{ $checkoutUrl }}', {{ $remainingSec ?? 600 }})"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#acf847] hover:bg-[#9de03d] text-[#1a146b] px-4 py-2.5 text-xs font-black shadow-md transition cursor-pointer">
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                    </svg>
                                    <span>Bayar Sekarang</span>
                                </button>
                                <a href="{{ $checkoutUrl }}"
                                   class="inline-flex w-full items-center justify-center gap-1 rounded-xl border border-indigo-200 bg-white hover:bg-indigo-50 px-3 py-1.5 text-[11px] font-bold text-[#1a146b] transition">
                                    Buka Checkout Penuh &rarr;
                                </a>
                            @else
                                <button type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gray-200 px-4 py-2.5 text-xs font-bold text-gray-500 cursor-not-allowed">
                                    Booking Dibatalkan
                                </button>
                            @endif

                            {{-- Tombol Aksi Reschedule & Pembatalan (Sprint 5) --}}
                            <div class="grid grid-cols-2 gap-2">
                                @if ($pendingReschedule)
                                    <a href="{{ route('pelanggan.booking.reschedule', $jadwal->id) }}"
                                       class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-amber-300 bg-amber-50 px-2 py-2 text-[10px] font-bold text-amber-900 hover:bg-amber-100 transition text-center leading-tight">
                                        <svg class="w-3.5 h-3.5 text-amber-700 shrink-0" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Review Kasir
                                    </a>
                                @elseif ($canReschedule)
                                    <a href="{{ route('pelanggan.booking.reschedule', $jadwal->id) }}"
                                       class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-gray-200 bg-white px-2.5 py-2 text-[11px] font-bold text-gray-700 hover:bg-indigo-50 hover:text-[#1a146b] hover:border-indigo-200 transition">
                                        <svg class="w-3.5 h-3.5 text-gray-500 shrink-0" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                        Reschedule
                                    </a>
                                @else
                                    <button type="button"
                                            disabled
                                            title="{{ $isDibatalkan ? 'Jadwal telah dibatalkan' : (!empty($jadwal->checked_in_at) ? 'Tiket sudah check-in' : 'Reschedule minimal H-1 (24 jam sebelum kickoff)') }}"
                                            class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-gray-100 bg-gray-100 px-2.5 py-2 text-[11px] font-semibold text-gray-400 cursor-not-allowed">
                                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                        Reschedule
                                    </button>
                                @endif

                                @if ($canCancel)
                                    <button type="button"
                                            @click="openCancelModal('{{ $jadwal->id }}', '{{ addslashes($namaLapangan) }}', '{{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('l, d F Y') }}', '{{ substr((string)$jadwal->jam_mulai, 0, 5) }} - {{ substr((string)$jadwal->jam_selesai, 0, 5) }} WIB')"
                                            class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-red-200 bg-red-50/70 px-2.5 py-2 text-[11px] font-bold text-red-700 hover:bg-red-100 transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-red-600 shrink-0" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        Batalkan
                                    </button>
                                @else
                                    <button type="button"
                                            disabled
                                            class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-gray-100 bg-gray-100 px-2.5 py-2 text-[11px] font-semibold text-gray-400 cursor-not-allowed">
                                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        Batalkan
                                    </button>
                                @endif
                            </div>

                            <p class="text-[10px] text-center text-gray-400">
                                @if ($isPaidOrVerified)
                                    Tunjukkan kode QR ke gerbang turnstile
                                @elseif ($isUnpaid)
                                    Selesaikan pembayaran agar jadwal Anda terkonfirmasi
                                @else
                                    Reservasi telah dibatalkan
                                @endif
                            </p>
                        </div>

                    </div>
                </div>
{{-- POP-UP MODAL E-TICKET & QR CODE FIGMA --}}
@if (isset($isPaidOrVerified) && $isPaidOrVerified)
    <div x-show="showModal" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6" 
         style="display: none;">
        
        <div @click.away="showModal = false"
             id="printable-ticket-{{ $jadwal->id }}"
             class="relative w-full max-w-4xl rounded-3xl bg-white shadow-2xl p-4 sm:p-8 space-y-4 sm:space-y-6 max-h-[92vh] overflow-y-auto text-left border border-gray-100">

            {{-- Tombol Close X --}}
            <button @click="showModal = false"
                    type="button"
                    class="no-print absolute top-3.5 right-3.5 sm:top-5 sm:right-5 w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 font-bold transition active:scale-95 z-10 cursor-pointer">
                <x-lucide-x class="w-4 h-4" />
            </button>

            {{-- Header Status (Ringkas 1 baris di HP agar QR langsung terlihat di atas) --}}
            <div class="flex sm:flex-col items-center gap-2.5 sm:gap-2 text-left sm:text-center pr-10 sm:pr-0 pt-0.5 sm:pt-2">
                <div class="inline-flex h-9 w-9 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-full bg-[#acf847] text-[#1a146b] shadow-xs">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                </div>
                <div class="min-w-0">
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-extrabold uppercase tracking-wider">
                        ● Akses Lapangan Aktif
                    </span>
                    <h2 class="text-sm sm:text-2xl font-extrabold text-[#1a146b] sm:mt-1 leading-snug">
                        Reservasi Berhasil Dikonfirmasi!
                    </h2>
                    <p class="sm:hidden text-[11px] font-semibold text-gray-500 truncate">
                        {{ $jadwal->lapangan?->nama }}
                    </p>
                </div>
            </div>

            {{-- Boarding Pass Card (QR langsung di atas pada HP, 2 kolom pada Desktop) --}}
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12">

                {{-- Kiri (Desktop) / Bawah (Mobile): Detail Match Pass --}}
                <div class="order-2 lg:order-1 lg:col-span-8 p-4 sm:p-6 space-y-3.5 sm:space-y-5">
                    <div class="hidden sm:flex items-center justify-between border-b border-gray-100 pb-3">
                        <span class="px-3 py-1 rounded-full bg-[#1a146b] text-white text-[10px] font-extrabold uppercase tracking-wider">
                            OFFICIAL MATCH PASS
                        </span>
                    </div>

                    <div>
                        <span class="sm:hidden inline-block mb-1.5 px-2.5 py-0.5 rounded-full bg-[#1a146b] text-white text-[9px] font-extrabold uppercase tracking-wider">
                            OFFICIAL MATCH PASS
                        </span>
                        <h3 class="text-base sm:text-xl font-extrabold text-[#1a146b] leading-snug">
                            {{ $jadwal->lapangan?->nama }}
                        </h3>
                        <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#416900] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="2" y="5" width="20" height="14" rx="2" />
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <circle cx="12" cy="12" r="2.5" />
                                <path d="M2 9h3v6H2" />
                                <path d="M22 9h-3v6h3" />
                            </svg>
                            <span>{{ $jadwal->lapangan?->kategori }}</span>
                            @if($jadwal->lapangan?->tipe_lantai)
                                • <span>{{ $jadwal->lapangan?->tipe_lantai }}</span>
                            @endif
                        </p>
                    </div>

                    {{-- Pemesan & Tim (2 kolom sejajar di HP & Desktop) --}}
                    <div class="grid grid-cols-2 gap-2.5 sm:gap-3 bg-[#f8f9ff] p-3 sm:p-3.5 rounded-xl sm:rounded-2xl border border-indigo-50">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-[#1a146b] text-white flex items-center justify-center font-bold text-[10px] sm:text-xs shrink-0 uppercase">
                                {{ strtoupper(substr(Auth::user()->nama ?? Auth::user()->name ?? 'P', 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-[8px] sm:text-[9px] font-bold text-gray-400 uppercase tracking-wider">PEMESAN</p>
                                <p class="text-[11px] sm:text-xs font-bold text-gray-800 truncate">{{ Auth::user()->nama ?? Auth::user()->name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 sm:gap-2 bg-white px-2.5 sm:px-3 py-1.5 rounded-lg sm:rounded-xl border border-gray-100 min-w-0">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#1a146b] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                            <p class="text-[11px] sm:text-xs font-bold text-gray-800 truncate">{{ $jadwal->nama_tim ?? 'Pemain Umum' }}</p>
                        </div>
                    </div>

                    {{-- Schedule Grid (2 kolom di HP, 3 kolom di Desktop) --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-2.5 text-xs">
                        <div class="bg-[#f8f9ff] p-2.5 sm:p-3 rounded-xl sm:rounded-2xl border border-indigo-50">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">TANGGAL MAIN</p>
                            <p class="font-bold text-gray-800 mt-0.5 sm:mt-1 text-[11px] sm:text-xs leading-snug">{{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('d M Y') }}</p>
                        </div>
                        <div class="bg-[#f8f9ff] p-2.5 sm:p-3 rounded-xl sm:rounded-2xl border border-indigo-50">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">WAKTU KICK-OFF</p>
                            <p class="font-bold text-[#1a146b] mt-0.5 sm:mt-1 text-[11px] sm:text-xs whitespace-nowrap">{{ substr((string) $jadwal->jam_mulai, 0, 5) }} – {{ substr((string) $jadwal->jam_selesai, 0, 5) }} WIB</p>
                        </div>
                        <div class="col-span-2 sm:col-span-1 bg-[#f8f9ff] p-2.5 sm:p-3 rounded-xl sm:rounded-2xl border border-indigo-50 flex sm:block items-center justify-between">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">STATUS PEMBAYARAN</p>
                            <p class="font-bold text-emerald-600 sm:mt-1 text-[11px] sm:text-xs">
                                {{ strtolower($jadwal->status_pembayaran ?? '') === 'dp_terverifikasi' ? 'DP 50% Lunas' : 'Lunas 100%' }}
                            </p>
                        </div>
                    </div>

                    {{-- Total & Pelunasan --}}
                    @php
                        $totalHarga = (float) ($jadwal->harga_disepakati ?? 0)
                            + (float) ($jadwal->biaya_layanan ?? 0);

                        $isDP = $jadwal->status_pembayaran === 'dp_terverifikasi';

                        $onlineTerbayar = $isDP ? ($totalHarga / 2) : $totalHarga;
                        $sisaBayar = $isDP ? ($totalHarga / 2) : 0;
                    @endphp
                    <div class="flex items-center justify-between gap-2 bg-indigo-50/50 p-3 sm:p-3.5 rounded-xl sm:rounded-2xl border border-indigo-100">
                        <div class="min-w-0">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">RINCIAN PELUNASAN</p>
                            <p class="text-[11px] sm:text-xs font-semibold text-gray-600">
                                Terbayar: <span class="font-bold text-gray-800 whitespace-nowrap">Rp {{ number_format($onlineTerbayar, 0, ',', '.') }}</span>
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            @if($sisaBayar > 0)
                                <p class="text-[9px] text-gray-400 font-bold uppercase">Sisa di Kasir</p>
                                <p class="text-xs sm:text-sm font-extrabold text-[#1a146b] whitespace-nowrap">Rp {{ number_format($sisaBayar, 0, ',', '.') }}</p>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold whitespace-nowrap">
                                    <svg class="w-3 h-3 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    Lunas Tanpa Sisa
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Kanan (Desktop) / Atas Highlight (Mobile): Boarding Pass Gate (QR Code) --}}
                <div class="order-1 lg:order-2 lg:col-span-4 bg-[#f4f7ff] p-4 sm:p-6 flex flex-col items-center justify-center text-center border-b border-dashed border-gray-200 lg:border-b-0 lg:border-l lg:border-solid lg:border-gray-100">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-gray-400 mb-2.5 sm:mb-3">BOARDING PASS GATE</span>

                    {{-- QR Code Image / SimpleSoftwareIO (High-resolution, clean vector SVG) --}}
                    @php
                        $qrPayload = $jadwal->kode_qr ?: ($jadwal->kode_booking ?: ('LPNG-' . $jadwal->id));
                    @endphp
                    <div class="bg-white p-3 sm:p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center mb-2">
                        <div class="w-36 h-36 flex items-center justify-center">
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(144)->style('round', 0.7)->eye('circle')->color(26, 20, 107)->margin(1)->errorCorrection('M')->generate($qrPayload) !!}
                        </div>
                    </div>

                    <p class="text-[10px] text-gray-500 font-medium">Scan di Meja Resepsionis</p>

                    {{-- Token Manual --}}
                    <div class="mt-2.5 w-full max-w-[220px] bg-white py-2 px-3 rounded-xl border border-gray-200">
                        <p class="text-[8px] font-bold text-gray-400 uppercase">TOKEN MANUAL (6 KARAKTER)</p>
                        <p class="font-mono text-base font-extrabold text-[#1a146b] tracking-widest">
                            {{ $jadwal->token_manual ?? 'Token belum tersedia' }}
                        </p>
                    </div>
                </div>

            </div>

            {{-- Tombol Action (2 kolom sejajar di HP, 3 tombol lengkap di Desktop) --}}
            <div class="no-print grid grid-cols-2 sm:flex sm:flex-wrap items-center justify-center gap-2 sm:gap-3 pt-1">
                <a href="https://wa.me/?text={{ urlencode('E-Tiket LapangIn untuk ' . $jadwal->lapangan?->nama . ' - Kode Booking: ' . ($jadwal->kode_booking ?? $displayKode)) }}"
                   target="_blank"
                   class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-5 py-2.5 rounded-xl sm:rounded-2xl bg-[#acf847] hover:bg-[#9be338] text-[#1a146b] font-extrabold text-xs transition active:scale-[0.98] shadow-xs whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-1.074-.75 6.47 6.47 0 00.384-2.548C3.606 16.353 3 14.28 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
                    <span>Bagikan WA</span>
                </a>
                <a href="{{ route('pelanggan.booking.tiketPdf', $jadwal->id) }}"
                   target="_blank"
                   class="inline-flex items-center justify-center gap-1.5 sm:gap-2 rounded-xl sm:rounded-2xl bg-[#1a146b] hover:bg-[#25207c] px-3 sm:px-5 py-2.5 text-xs font-bold text-white transition active:scale-[0.98] shadow-xs whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    <span>Unduh PDF</span>
                </a>
                <button type="button"
                        onclick="printTiketDocument('{{ $jadwal->id }}', 'Tiket-{{ addslashes($jadwal->kode_booking ?? $jadwal->id) }}')"
                        class="hidden sm:inline-flex items-center justify-center gap-2 rounded-2xl bg-white border border-gray-200 px-5 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50 transition shadow-xs cursor-pointer">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.64-2.115-1.173-3.085m12.906 0a17.394 17.394 0 00-1.173 3.085m-10.56 0A17.917 17.917 0 0012 15c2.316 0 4.475-.436 6.44-1.171m-12.88 0C4.385 10.978 4 8.784 4 6.5 4 4.567 5.567 3 7.5 3h9C18.433 3 20 4.567 20 6.5c0 2.284-.385 4.478-1.56 7.329M6.72 13.829a17.92 17.92 0 00-1.173 3.085M6.72 13.829A17.92 17.92 0 0112 15m0 0v6m-4-3h8"/></svg>
                    <span>Cetak Tiket</span>
                </button>
            </div>

            {{-- Panduan & Protokol (Ringkas di HP, 3 kartu di Desktop) --}}
            <div class="no-print pt-3 sm:pt-4 border-t border-gray-100">
                <h4 class="text-xs font-extrabold text-[#1a146b] flex items-center gap-2 mb-2.5 sm:mb-3">
                    <svg class="w-4 h-4 text-[#416900] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    <span>Panduan &amp; Protokol Hari Pertandingan</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3 text-left">
                    <div class="bg-gray-50 p-3 sm:p-3.5 rounded-xl sm:rounded-2xl border border-gray-100 space-y-0.5 sm:space-y-1">
                        <p class="text-[9px] font-extrabold uppercase text-[#416900]">ATURAN 01 · HADIR 15 MENIT AWAL</p>
                        <p class="text-gray-500 leading-relaxed text-[10px]">Registrasi &amp; pengambilan rompi tim dapat dilakukan langsung di meja kasir sebelum kick-off.</p>
                    </div>

                    <div class="bg-gray-50 p-3 sm:p-3.5 rounded-xl sm:rounded-2xl border border-gray-100 space-y-0.5 sm:space-y-1">
                        <p class="text-[9px] font-extrabold uppercase text-[#416900]">ATURAN 02 · SEPATU SOL KARET (IC)</p>
                        <p class="text-gray-500 leading-relaxed text-[10px]">Standar lantai mewajibkan sol indoor. Dilarang keras memakai stud/pull rumput outdoor.</p>
                    </div>

                    <div class="bg-gray-50 p-3 sm:p-3.5 rounded-xl sm:rounded-2xl border border-gray-100 space-y-0.5 sm:space-y-1">
                        <p class="text-[9px] font-extrabold uppercase text-[#416900]">ATURAN 03 · PARKIR LUAS &amp; GRATIS</p>
                        <p class="text-gray-500 leading-relaxed text-[10px]">Tersedia slot aman untuk mobil dan motor tim. Cukup tunjukkan E-Tiket kepada petugas keamanan.</p>
                    </div>
                </div>
            </div>

            {{-- Petunjuk Arah Arena --}}
            @if($jadwal->lapangan?->alamat || $jadwal->lapangan?->lokasi)
                <div class="bg-indigo-50/40 p-4 rounded-2xl border border-indigo-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-indigo-100 text-[#1a146b] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-[#1a146b]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        </div>
                        <div>
                            <h5 class="text-xs font-extrabold text-gray-900">Petunjuk Arah Menuju Arena</h5>
                            <p class="text-[11px] text-gray-500">{{ $jadwal->lapangan?->alamat ?? $jadwal->lapangan?->lokasi }}</p>
                        </div>
                    </div>
                    <a href="https://maps.google.com/?q={{ urlencode(($jadwal->lapangan?->nama ?? 'Lapangan') . ' ' . ($jadwal->lapangan?->alamat ?? '')) }}" 
                       target="_blank" 
                       class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-[#1a146b] font-bold text-xs hover:bg-gray-50 transition shrink-0">
                        Buka Google Maps ↗
                    </a>
                </div>
            @endif

        </div>
    </div>
@endif
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
                <h2 class="text-xl font-bold text-gray-900">Belum Ada Pesanan</h2>
                <p class="mt-2 text-sm text-gray-600">Booking lapangan Anda akan muncul di sini setelah pesanan tersedia.</p>
            </div>
        @endforelse

        {{-- Empty state jika hasil filter atau search tidak ada data yang cocok --}}
        @if(count($jadwals) > 0)
            <div x-show="visibleCount === 0" x-cloak class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center my-6">
                <div class="mx-auto w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-[#1a146b] mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <h3 class="text-base font-bold text-gray-900">Tidak Ada Jadwal Ditemukan</h3>
                <p class="mt-1 text-xs text-gray-500">Tidak ada jadwal booking yang sesuai dengan tab filter atau pencarian Anda.</p>
                <button type="button" @click="activeTab = 'all'; searchQuery = ''" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#1a146b] text-white text-xs font-bold hover:bg-indigo-900 transition cursor-pointer shadow-xs">
                    Tampilkan Semua Booking
                </button>
            </div>
        @endif

    {{-- POP-UP MODAL SELESAIKAN PEMBAYARAN VIA MIDTRANS SNAP --}}
    <div x-show="payModalOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6"
         style="display: none;">

        <div @click.away="if (!isProcessing) payModalOpen = false"
             class="relative w-full max-w-lg rounded-3xl bg-white shadow-2xl overflow-hidden p-4 sm:p-7 text-left border border-gray-100">

            {{-- Tombol Close X --}}
            <button @click="payModalOpen = false"
                    :disabled="isProcessing"
                    type="button"
                    class="absolute top-4 right-4 sm:top-5 sm:right-5 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 font-bold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            {{-- Header Modal --}}
            <div class="flex items-start gap-3 pr-8">
                <span class="flex h-10 w-10 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-xl sm:rounded-2xl bg-[#acf847] text-[#1a146b] shadow-xs">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-6-10.5h16.5a1.5 1.5 0 011.5 1.5v10.5a1.5 1.5 0 01-1.5 1.5H3.75A1.5 1.5 0 012.25 18V7.5a1.5 1.5 0 011.5-1.5z"/></svg>
                </span>
                <div>
                    <h3 class="text-base sm:text-lg font-extrabold text-[#1a146b]">
                        Selesaikan Pembayaran Booking
                    </h3>
                    <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">
                        Pilih skema pembayaran untuk mengaktifkan kode QR dan e-tiket masuk lapangan.
                    </p>
                </div>
            </div>

            {{-- Banner Timer 10 Menit di Modal --}}
            <div class="mt-3.5 sm:mt-4 flex items-center justify-between gap-2.5 sm:gap-3 rounded-xl sm:rounded-2xl border-2 border-amber-300 bg-amber-50 p-3 sm:p-3.5 text-amber-900 shadow-2xs">
                <div class="flex items-center gap-2 sm:gap-2.5 min-w-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-amber-800 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-xs font-black uppercase tracking-wide">Batas Waktu Pembayaran:</p>
                        <p class="text-[10px] sm:text-[11px] text-amber-700 leading-tight">Slot dikunci sementara selama waktu ini</p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 rounded-xl bg-white px-2.5 py-1 sm:px-3.5 sm:py-1.5 border border-amber-200 shadow-2xs shrink-0">
                    <span class="font-mono text-sm sm:text-base font-black text-amber-900" x-text="formatTimerText(modalRemainingSec)"></span>
                </div>
            </div>

            {{-- Rincian Booking Ringkas --}}
            <div class="mt-3.5 sm:mt-4 rounded-xl sm:rounded-2xl bg-[#f8faff] border border-[#dce9ff] p-3.5 sm:p-4 text-xs space-y-2">
                <div class="flex justify-between items-start gap-2">
                    <span class="text-gray-500 font-semibold shrink-0">Lapangan:</span>
                    <span class="font-bold text-[#0b1c30] text-right" x-text="selectedLapanganNama"></span>
                </div>
                <div class="flex justify-between items-start gap-2">
                    <span class="text-gray-500 font-semibold shrink-0">Jadwal:</span>
                    <span class="font-bold text-[#1a146b] text-right">
                        <span x-text="selectedTanggal"></span>, <span x-text="selectedJamMulai"></span> – <span x-text="selectedJamSelesai"></span> WIB
                    </span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-indigo-100">
                    <span class="text-gray-600 font-bold uppercase tracking-wider text-[10px]">Total Tagihan Sewa:</span>
                    <span class="font-extrabold text-sm text-[#1a146b]" x-text="formatRupiah(selectedHarga)"></span>
                </div>
            </div>

            {{-- Pilihan Tipe Pembayaran (Radio Cards) --}}
            <div class="mt-3.5 sm:mt-4 space-y-2">
                <label class="block text-[11px] font-extrabold uppercase tracking-wider text-gray-500">
                    Pilih Skema Pembayaran:
                </label>

                <div class="grid grid-cols-2 gap-2 sm:gap-2.5">
                    {{-- Opsi 1: DP 50% --}}
                    <div @click="tipeBayar = 'dp'"
                         :class="tipeBayar === 'dp' ? 'border-[#1a146b] bg-[#eff4ff] ring-1 ring-[#1a146b]' : 'border-gray-200 hover:border-gray-300 bg-white'"
                         class="cursor-pointer rounded-xl sm:rounded-2xl border-2 p-3 sm:p-3.5 transition flex flex-col justify-between">
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-[11px] sm:text-xs font-bold" :class="tipeBayar === 'dp' ? 'text-[#1a146b]' : 'text-gray-700'">
                                Bayar DP (50%)
                            </span>
                            <span class="h-4 w-4 rounded-full border-2 flex items-center justify-center shrink-0"
                                  :class="tipeBayar === 'dp' ? 'border-[#1a146b] bg-[#1a146b]' : 'border-gray-300'">
                                <span x-show="tipeBayar === 'dp'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                            </span>
                        </div>
                        <p class="mt-1.5 sm:mt-2 text-sm sm:text-base font-black text-[#0b1c30]" x-text="formatRupiah(Math.round(selectedHarga * 0.5))"></p>
                        <p class="mt-1 text-[10px] text-gray-500 leading-tight">
                            Sisa dilunasi di meja kasir saat datang.
                        </p>
                    </div>

                    {{-- Opsi 2: Lunas 100% --}}
                    <div @click="tipeBayar = 'full'"
                         :class="tipeBayar === 'full' ? 'border-[#1a146b] bg-[#eff4ff] ring-1 ring-[#1a146b]' : 'border-gray-200 hover:border-gray-300 bg-white'"
                         class="cursor-pointer rounded-xl sm:rounded-2xl border-2 p-3 sm:p-3.5 transition flex flex-col justify-between">
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-[11px] sm:text-xs font-bold" :class="tipeBayar === 'full' ? 'text-[#1a146b]' : 'text-gray-700'">
                                Bayar Lunas (100%)
                            </span>
                            <span class="h-4 w-4 rounded-full border-2 flex items-center justify-center shrink-0"
                                  :class="tipeBayar === 'full' ? 'border-[#1a146b] bg-[#1a146b]' : 'border-gray-300'">
                                <span x-show="tipeBayar === 'full'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                            </span>
                        </div>
                        <p class="mt-1.5 sm:mt-2 text-sm sm:text-base font-black text-[#0b1c30]" x-text="formatRupiah(selectedHarga)"></p>
                        <p class="mt-1 text-[10px] text-[#416900] font-semibold leading-tight">
                            Langsung check-in tanpa antre kasir.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Error Message Alert --}}
            <div x-show="errorMessage" x-cloak class="mt-3 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold">
                <span x-text="errorMessage"></span>
            </div>

            {{-- Tombol Aksi --}}
            <div class="mt-5 space-y-2">
                <button type="button"
                        @click="processPayment()"
                        :disabled="isProcessing || isModalExpired || modalRemainingSec <= 0"
                        class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#1a146b] hover:bg-[#130d52] disabled:opacity-60 disabled:cursor-not-allowed px-5 py-3.5 text-xs sm:text-sm font-extrabold text-white shadow-md transition cursor-pointer">
                    <span x-show="!isProcessing && !isModalExpired && modalRemainingSec > 0">Bayar Sekarang (<span x-text="formatRupiah(nominalBayar())"></span>)</span>
                    <span x-show="!isProcessing && (isModalExpired || modalRemainingSec <= 0)" x-cloak>Waktu Pembayaran Telah Habis</span>
                    <span x-show="isProcessing" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Menghubungkan Midtrans Snap...
                    </span>
                </button>

                <div class="flex items-center justify-between gap-2 pt-1">
                    <a :href="checkoutUrl + '&tipe_bayar=' + tipeBayar"
                       class="text-xs font-bold text-[#1a146b] hover:underline">
                        Buka di Halaman Checkout Penuh →
                    </a>

                    <button type="button"
                            @click="payModalOpen = false"
                            :disabled="isProcessing"
                            class="text-xs font-semibold text-gray-500 hover:text-gray-800">
                        Batal
                    </button>
                </div>
            </div>

            <p class="mt-3 text-center text-[10px] text-gray-400 flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                <span>Pembayaran aman &amp; otomatis diverifikasi via Midtrans Snap (QRIS, VA, E-Wallet, CC).</span>
            </p>
        </div>
    </div>

    {{-- POP-UP MODAL PEMBATALAN SEWA & AUTO-PENALTY POLICY (SPRINT 5) --}}
    <div x-show="cancelModalOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6"
         style="display: none;">

        <div @click.away="if (!cancelSubmitting) cancelModalOpen = false"
             class="relative w-full max-w-md sm:max-w-lg max-h-[90vh] overflow-y-auto rounded-3xl bg-white shadow-2xl p-5 sm:p-7 space-y-4 text-left border border-gray-100">

            {{-- Close Button --}}
            <button @click="cancelModalOpen = false"
                    :disabled="cancelSubmitting"
                    type="button"
                    class="absolute top-4 right-4 sm:top-5 sm:right-5 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 transition active:scale-95 cursor-pointer">
                <x-lucide-x class="w-4 h-4" />
            </button>

            {{-- Modal Header (Tanpa kotak badge di belakang ikon) --}}
            <div class="flex items-start gap-3 pr-9">
                <x-lucide-ban class="w-6 h-6 text-red-600 shrink-0 mt-0.5" />
                <div class="min-w-0">
                    <h3 class="text-base sm:text-lg font-extrabold text-gray-900 leading-snug">
                        Konfirmasi Pembatalan Sewa
                    </h3>
                    <p class="text-[11px] sm:text-xs text-gray-500 leading-snug mt-0.5">
                        <span class="font-semibold text-gray-700" x-text="cancelLapanganNama"></span>
                        <span> &bull; </span>
                        <span x-text="cancelTanggal"></span>
                    </p>
                </div>
            </div>

            {{-- Loading Indicator --}}
            <div x-show="cancelLoading" class="p-7 text-center rounded-2xl bg-gray-50 border border-gray-100">
                <svg class="animate-spin h-6 w-6 text-red-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-2 text-xs font-semibold text-gray-500">Mengkalkulasi kebijakan denda penalti...</p>
            </div>

            {{-- Content Once Loaded --}}
            <div x-show="!cancelLoading && cancelPenalty" class="space-y-3.5 sm:space-y-4">

                {{-- Banner Kebijakan Penalti --}}
                <div :class="cancelPenalty?.is_h_minus_24 ? 'bg-red-50/80 border-red-200/80 text-red-900' : 'bg-amber-50 border-amber-200 text-amber-900'"
                     class="rounded-2xl border p-3.5 text-xs leading-relaxed">
                    <div class="flex items-center gap-2 font-bold text-xs sm:text-sm">
                        <template x-if="cancelPenalty?.is_h_minus_24">
                            <x-lucide-triangle-alert class="w-4 h-4 text-red-600 shrink-0" />
                        </template>
                        <template x-if="!cancelPenalty?.is_h_minus_24">
                            <x-lucide-info class="w-4 h-4 text-amber-700 shrink-0" />
                        </template>
                        <span x-text="cancelPenalty?.policy_badge"></span>
                    </div>
                    <p class="mt-1 text-[11px] sm:text-xs opacity-90" x-text="cancelPenalty?.policy_description"></p>
                </div>

                {{-- Breakdown Angka --}}
                <div class="rounded-2xl bg-gray-50/90 border border-gray-100 p-3.5 sm:p-4 space-y-2 text-xs">
                    <div class="flex items-center justify-between gap-3 text-gray-600">
                        <span>Total Harga Booking</span>
                        <span class="font-bold text-gray-900 whitespace-nowrap shrink-0" x-text="formatRupiah(cancelPenalty?.total_price)"></span>
                    </div>
                    <div class="flex items-center justify-between gap-3 text-gray-600">
                        <span>Dana Terbayar</span>
                        <span class="font-bold text-gray-900 whitespace-nowrap shrink-0" x-text="formatRupiah(cancelPenalty?.total_paid)"></span>
                    </div>
                    <div class="flex items-center justify-between gap-3 text-red-600 font-bold border-t border-gray-200/70 pt-2">
                        <span>Denda Penalti (<span x-text="cancelPenalty?.penalty_percentage"></span>%)</span>
                        <span class="whitespace-nowrap shrink-0" x-text="'- ' + formatRupiah(cancelPenalty?.penalty_amount)"></span>
                    </div>
                    <div class="flex items-center justify-between gap-3 text-emerald-700 font-extrabold text-xs sm:text-sm border-t border-gray-200/70 pt-2">
                        <span>Estimasi Pengembalian (Refund)</span>
                        <span class="text-sm sm:text-base whitespace-nowrap shrink-0" x-text="formatRupiah(cancelPenalty?.estimated_refund)"></span>
                    </div>
                </div>

                {{-- Form Pembatalan --}}
                <form :action="cancelFormUrl" method="POST" @submit="cancelSubmitting = true" class="space-y-3.5">
                    @csrf
                    <div>
                        <label for="alasan_pembatalan" class="block text-[11px] font-bold uppercase tracking-wider text-gray-600">
                            Alasan Pembatalan <span class="font-normal normal-case text-gray-400">(opsional)</span>
                        </label>
                        <textarea id="alasan_pembatalan"
                                  name="alasan_pembatalan"
                                  rows="2"
                                  maxlength="500"
                                  placeholder="Tulis alasan singkat pembatalan..."
                                  class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 placeholder-gray-400 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500 transition"></textarea>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 pt-1">
                        <button type="button"
                                @click="cancelModalOpen = false"
                                class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-xs font-bold text-gray-700 hover:bg-gray-50 active:scale-[0.98] transition text-center cursor-pointer">
                            Batal, Simpan Jadwal
                        </button>
                        <button type="submit"
                                :disabled="cancelSubmitting"
                                class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 active:scale-[0.98] text-xs font-bold text-white shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <span x-show="!cancelSubmitting">Ya, Batalkan Booking</span>
                            <span x-show="cancelSubmitting" x-cloak>Memproses...</span>
                        </button>
                    </div>
                </form>

            </div>

        </div>
    </div>

        {{-- Bagian Tabel Riwayat Transaksi Lalu ala Figma --}}
        <div class="mt-8 sm:mt-12 pt-5 sm:pt-6 border-t border-gray-200"
             x-data="{
                trxPage: 1,
                trxPerPage: 5,
                trxTotal: 0
             }">
            <div class="mb-3.5 sm:mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 sm:gap-2">
                <div>
                    <h2 class="text-xs sm:text-sm font-extrabold uppercase tracking-wider text-gray-900">
                        RIWAYAT TRANSAKSI LALU
                    </h2>
                    <p class="text-[11px] sm:text-xs text-gray-500">
                        Catatan pemakaian lapangan dan arsip invoice digital resmi.
                    </p>
                </div>

                <button type="button" onclick="printRiwayatTransaksi()" class="inline-flex items-center justify-center sm:justify-start gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 transition w-full sm:w-fit cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    Ekspor Laporan (PDF)
                </button>
            </div>

@php
    $jadwalItems = ($jadwals instanceof \Illuminate\Pagination\AbstractPaginator)
        ? $jadwals->getCollection()
        : collect($jadwals);

    $riwayatTransaksi = $jadwalItems
        ->flatMap(function ($jadwal) {
            return $jadwal->transaksis->map(function ($transaksi) use ($jadwal) {
                return [
                    'jadwal' => $jadwal,
                    'transaksi' => $transaksi,
                ];
            });
        })
        ->sortByDesc(function ($item) {
            return $item['transaksi']->dibayar_pada;
        })
        ->values();

    $totalTrxCount = $riwayatTransaksi->count();

    $riwayatPrintData = $riwayatTransaksi->map(function ($item) {
        $j = $item['jadwal'];
        $t = $item['transaksi'];
        return [
            'invoice' => $t->order_id ? '#' . $t->order_id : '#INV-' . strtoupper(substr((string) ($t->id ?? $j->id), 0, 8)),
            'lapangan' => $j->lapangan->nama ?? 'Lapangan',
            'jenis' => ucfirst($t->jenis),
            'tanggal' => $j->tanggal ? \Carbon\Carbon::parse($j->tanggal)->translatedFormat('d M Y') : '-',
            'jam' => substr((string) $j->jam_mulai, 0, 5) . ' - ' . substr((string) $j->jam_selesai, 0, 5) . ' WIB',
            'jumlah' => 'Rp ' . number_format((float) $t->jumlah, 0, ',', '.'),
            'metode' => ucfirst($t->metode),
            'status' => ucfirst($t->status),
            'status_color' => $t->status === 'berhasil' ? '#166534' : ($t->status === 'gagal' ? '#991b1b' : '#854d0e'),
            'status_bg' => $t->status === 'berhasil' ? '#dcfce7' : ($t->status === 'gagal' ? '#fee2e2' : '#fef9c3'),
        ];
    })->values();
@endphp

            {{-- Tampilan Kartu Kompak Khusus HP (tanpa scroll samping) --}}
            <div class="sm:hidden space-y-2.5">
                @forelse ($riwayatTransaksi as $index => $item)
                    @php
                        $jadwal = $item['jadwal'];
                        $transaksi = $item['transaksi'];
                        $statusClass = match ($transaksi->status) {
                            'berhasil' => 'bg-green-50 text-green-700 border-green-200/60',
                            'gagal' => 'bg-red-50 text-red-700 border-red-200/60',
                            default => 'bg-yellow-50 text-yellow-700 border-yellow-200/60',
                        };
                        $invoiceCode = $transaksi->order_id
                            ? '#' . $transaksi->order_id
                            : '#INV-' . strtoupper(substr((string) ($transaksi->id ?? $jadwal->id), 0, 8));
                    @endphp
                    <div x-show="Math.floor({{ $index }} / trxPerPage) + 1 === trxPage"
                         class="rounded-2xl border border-gray-200/90 bg-white p-3.5 shadow-2xs space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-900 truncate">{{ $jadwal->lapangan->nama ?? 'Lapangan' }}</p>
                                <span class="mt-1 inline-flex items-center px-1.5 py-0.5 rounded bg-gray-100 text-gray-700 text-[10px] font-mono font-bold">
                                    {{ $invoiceCode }}
                                </span>
                            </div>
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-bold shrink-0 {{ $statusClass }}">
                                {{ ucfirst($transaksi->status) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-gray-500 pt-2 border-t border-gray-100">
                            <div>
                                <p class="font-medium text-gray-700">
                                    {{ $jadwal->tanggal?->format('d M Y') ?? '-' }} · {{ substr((string) $jadwal->jam_mulai, 0, 5) }}–{{ substr((string) $jadwal->jam_selesai, 0, 5) }}
                                </p>
                                <p class="text-[10px] text-gray-400">
                                    {{ ucfirst($transaksi->jenis) }} · {{ ucfirst($transaksi->metode) }}
                                </p>
                            </div>
                            <p class="text-xs font-extrabold text-[#1a146b] shrink-0">
                                Rp {{ number_format((float) $transaksi->jumlah, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-8 text-center">
                        <p class="text-xs font-semibold text-gray-700">Belum ada transaksi</p>
                        <p class="mt-1 text-[11px] text-gray-500">Riwayat pembayaran akan muncul di sini setelah tersedia.</p>
                    </div>
                @endforelse
            </div>

            {{-- Container Tabel Responsive (Desktop) --}}
            <div class="hidden sm:block overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-xs">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-gray-100 bg-gray-50/80 font-bold uppercase text-gray-500">
                        <tr>
                            <th scope="col" class="px-5 py-3.5">INVOICE</th>
                            <th scope="col" class="px-5 py-3.5">LAPANGAN &amp; FASILITAS</th>
                            <th scope="col" class="px-5 py-3.5">WAKTU MAIN</th>
                            <th scope="col" class="px-5 py-3.5">TOTAL BIAYA</th>
                            <th scope="col" class="px-5 py-3.5 text-right">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">

@forelse ($riwayatTransaksi as $index => $item)
    @php
        $jadwal = $item['jadwal'];
        $transaksi = $item['transaksi'];

        $statusClass = match ($transaksi->status) {
            'berhasil' => 'bg-green-50 text-green-700 border-green-200/60',
            'gagal' => 'bg-red-50 text-red-700 border-red-200/60',
            default => 'bg-yellow-50 text-yellow-700 border-yellow-200/60',
        };

        $invoiceCode = $transaksi->order_id 
            ? '#' . $transaksi->order_id 
            : '#INV-' . strtoupper(substr((string) ($transaksi->id ?? $jadwal->id), 0, 8));
    @endphp

    <tr x-show="Math.floor({{ $index }} / trxPerPage) + 1 === trxPage"
        class="hover:bg-gray-50/60 transition">
        <td class="px-5 py-4 font-mono font-bold text-gray-900">
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-gray-100 text-gray-800 text-[11px] font-mono">
                {{ $invoiceCode }}
            </span>
        </td>

        <td class="px-5 py-4">
            <p class="font-bold text-gray-900">
                {{ $jadwal->lapangan->nama ?? 'Lapangan' }}
            </p>
            <p class="text-[11px] text-gray-400">
                {{ ucfirst($transaksi->jenis) }}
            </p>
        </td>

        <td class="px-5 py-4">
            <p class="font-medium text-gray-900">
                {{ $jadwal->tanggal?->format('d M Y') ?? '-' }}
            </p>
            <p class="text-[11px] text-gray-400">
                {{ substr((string) $jadwal->jam_mulai, 0, 5) }} – {{ substr((string) $jadwal->jam_selesai, 0, 5) }} WIB
            </p>
        </td>

        <td class="px-5 py-4">
            <p class="font-bold text-gray-900">
                Rp {{ number_format((float) $transaksi->jumlah, 0, ',', '.') }}
            </p>
            <p class="text-[11px] font-semibold text-gray-500">
                {{ ucfirst($transaksi->metode) }}
            </p>
        </td>

        <td class="px-5 py-4 text-right">
            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $statusClass }}">
                {{ ucfirst($transaksi->status) }}
            </span>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="px-5 py-10 text-center">
            <p class="font-semibold text-gray-700">Belum ada transaksi</p>
            <p class="mt-1 text-sm text-gray-500">
                Riwayat pembayaran akan muncul di sini setelah tersedia.
            </p>
        </td>
    </tr>
@endforelse
                    </tbody>
                </table>
            </div>

            {{-- Navigasi Pagination Tabel Transaksi --}}
            @if ($totalTrxCount > 5)
                <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600 px-2"
                     x-init="trxTotal = {{ $totalTrxCount }}">
                    <div>
                        Menampilkan 
                        <span class="font-bold text-gray-900" x-text="((trxPage - 1) * trxPerPage) + 1"></span>
                        -
                        <span class="font-bold text-gray-900" x-text="Math.min(trxPage * trxPerPage, {{ $totalTrxCount }})"></span>
                        dari 
                        <span class="font-bold text-gray-900">{{ $totalTrxCount }}</span> transaksi
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button type="button"
                                @click="if (trxPage > 1) trxPage--"
                                :disabled="trxPage === 1"
                                :class="trxPage === 1 ? 'opacity-40 cursor-not-allowed bg-gray-100 text-gray-400' : 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 shadow-2xs cursor-pointer'"
                                class="px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1">
                            &larr; Prev
                        </button>

                        <div class="flex items-center gap-1 px-1">
                            <template x-for="p in Math.ceil({{ $totalTrxCount }} / trxPerPage)" :key="p">
                                <button type="button"
                                        @click="trxPage = p"
                                        :class="trxPage === p ? 'bg-[#1a146b] text-white font-extrabold shadow-2xs' : 'bg-white hover:bg-gray-100 text-gray-700 border border-gray-200'"
                                        class="h-7 w-7 rounded-lg text-xs flex items-center justify-center transition cursor-pointer"
                                        x-text="p">
                                </button>
                            </template>
                        </div>

                        <button type="button"
                                @click="if (trxPage < Math.ceil({{ $totalTrxCount }} / trxPerPage)) trxPage++"
                                :disabled="trxPage === Math.ceil({{ $totalTrxCount }} / trxPerPage)"
                                :class="trxPage === Math.ceil({{ $totalTrxCount }} / trxPerPage) ? 'opacity-40 cursor-not-allowed bg-gray-100 text-gray-400' : 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 shadow-2xs cursor-pointer'"
                                class="px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1">
                            Next &rarr;
                        </button>
                    </div>
                </div>
            @endif
        </div>


{{-- Midtrans Snap SDK --}}
<script src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>


{{-- Real-time Countdown Timer untuk Kartu Booking Belum Bayar --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const timerElements = document.querySelectorAll('[data-card-timer]');
        if (!timerElements.length) return;

        const intervalId = setInterval(function () {
            let activeCount = 0;

            timerElements.forEach(function (el) {
                let remaining = parseInt(el.getAttribute('data-remaining') || '0', 10);
                const jadwalId = el.getAttribute('data-card-timer');
                const textSpan = el.querySelector('span.font-mono');

                if (remaining > 0) {
                    activeCount++;
                    remaining--;
                    el.setAttribute('data-remaining', remaining);

                    const mins = Math.floor(remaining / 60);
                    const secs = remaining % 60;
                    const formatted = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');

                    if (textSpan) {
                        textSpan.innerText = formatted;
                    }
                } else {
                    if (textSpan && textSpan.innerText !== '00:00 (Habis)') {
                        textSpan.innerText = '00:00 (Habis)';
                        if (jadwalId) {
                            fetch('/checkout/expire/' + jadwalId, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            }).then(function () {
                                setTimeout(function () {
                                    window.location.reload();
                                }, 2000);
                            }).catch(console.error);
                        }
                    }
                }
            });

            if (activeCount === 0) {
                clearInterval(intervalId);
            }
        }, 1000);
    });

    // Fungsi cetak laporan riwayat transaksi resmi A4
    window.printRiwayatTransaksi = function() {
        const rows = @json($riwayatPrintData ?? []);

        let tableHtml = '';
        rows.forEach(function(r) {
            tableHtml += `
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 10px 12px; font-family: monospace; font-weight: bold; font-size: 11px;">${r.invoice}</td>
                    <td style="padding: 10px 12px;"><strong>${r.lapangan}</strong><br><span style="font-size: 10px; color: #6b7280;">${r.jenis}</span></td>
                    <td style="padding: 10px 12px;">${r.tanggal}<br><span style="font-size: 10px; color: #6b7280;">${r.jam}</span></td>
                    <td style="padding: 10px 12px; font-weight: bold;">${r.jumlah}<br><span style="font-size: 10px; color: #6b7280;">${r.metode}</span></td>
                    <td style="padding: 10px 12px; text-align: right;">
                        <span style="display: inline-block; padding: 3px 8px; border-radius: 9999px; font-size: 10px; font-weight: bold; background: ${r.status_bg}; color: ${r.status_color};">
                            ${r.status}
                        </span>
                    </td>
                </tr>
            `;
        });

        const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Laporan Transaksi - LapangIn</title>
                <style>
                    @page { size: A4 portrait; margin: 15mm; }
                    body { font-family: 'Segoe UI', Arial, sans-serif; color: #111827; margin: 0; padding: 20px; font-size: 12px; }
                    .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #1a146b; padding-bottom: 12px; margin-bottom: 20px; }
                    .brand { font-size: 20px; font-weight: 800; color: #1a146b; }
                    .brand span { color: #416900; }
                    .title { font-size: 14px; font-weight: bold; text-transform: uppercase; color: #374151; margin-top: 4px; }
                    table { width: 100%; border-collapse: collapse; text-align: left; }
                    th { background: #f3f4f6; color: #4b5563; font-size: 10px; text-transform: uppercase; padding: 10px 12px; border-bottom: 1px solid #d1d5db; }
                    .footer { margin-top: 30px; font-size: 10px; color: #6b7280; text-align: right; border-top: 1px solid #e5e7eb; padding-top: 8px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <div>
                        <div class="brand">Lapang<span>In</span></div>
                        <div class="title">Laporan Riwayat Transaksi &amp; Invoice Resmi</div>
                    </div>
                    <div style="text-align: right; font-size: 11px; color: #6b7280;">
                        Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB<br>
                        Pengguna: {{ auth()->user()?->nama ?? 'Pelanggan LapangIn' }}
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>INVOICE</th>
                            <th>LAPANGAN &amp; FASILITAS</th>
                            <th>WAKTU MAIN</th>
                            <th>TOTAL BIAYA</th>
                            <th style="text-align: right;">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tableHtml || '<tr><td colspan="5" style="text-align: center; padding: 20px;">Belum ada data transaksi.</td></tr>'}
                    </tbody>
                </table>

                <div class="footer">
                    Dokumen ini sah dan diterbitkan secara digital oleh Sistem Reservasi LapangIn.
                </div>

                <script>
                    window.onload = function() {
                        window.print();
                        window.onafterprint = function() { window.close(); };
                    };
                <\/script>
            </body>
            </html>
        `;

        const printWindow = window.open('', '_blank');
        if (printWindow) {
            printWindow.document.open();
            printWindow.document.write(printContent);
            printWindow.document.close();
        }
    };

    // Fungsi cetak/unduh PDF tiket resmi berstandar tinggi
    window.printTiketDocument = function(targetId, docTitle) {
        let jadwalId = targetId;
        if (typeof targetId === 'string' && targetId.startsWith('printable-ticket-')) {
            jadwalId = targetId.replace('printable-ticket-', '');
        }

        const pdfUrl = '/pelanggan/booking/' + encodeURIComponent(jadwalId) + '/tiket-pdf?stream=1';
        
        // Buka tab stream PDF resmi untuk pratinjau dan pencetakan langsung berstandar A4
        const printWindow = window.open(pdfUrl, '_blank');
        if (printWindow) {
            printWindow.focus();
        } else {
            window.location.href = pdfUrl;
        }
    };
</script>

{{-- Pengaturan Cetak Fallback --}}
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .fixed.inset-0:has([id^="printable-ticket-"]),
        [id^="printable-ticket-"],
        [id^="printable-ticket-"] * {
            visibility: visible;
        }
        .fixed.inset-0:has([id^="printable-ticket-"]) {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            background: transparent !important;
        }
        .no-print {
            display: none !important;
        }
        @page {
            size: A4 portrait;
            margin: 8mm;
        }
    }
</style>
@endsection