@extends('layouts.pelanggan')

@section('title', 'Reschedule Jadwal - ' . ($lapangan->nama ?? 'Lapangan') . ' - LapangIn')

@section('content')
<div class="space-y-6" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    {{-- Breadcrumb & Back --}}
    <div class="flex items-center gap-2 text-xs font-semibold text-gray-500">
        <a href="{{ route('pelanggan.riwayat') }}" class="inline-flex items-center gap-1.5 text-[#1a146b] hover:underline">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Riwayat Booking
        </a>
        <span>/</span>
        <span class="text-gray-400">Pengajuan Reschedule</span>
    </div>

    {{-- Header Banner --}}
    <div class="overflow-hidden rounded-2xl bg-[#1a146b] text-white shadow-lg sm:rounded-3xl p-6 sm:p-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-[#acf847]">
                    <span class="h-2 w-2 rounded-full bg-[#acf847] animate-pulse"></span>
                    Penjadwalan Ulang Mandiri (Self-Service)
                </div>
                <h1 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl text-white">
                    Reschedule Jadwal Bermain
                </h1>
                <p class="mt-1 text-sm text-indigo-100 max-w-xl">
                    Pilih tanggal dan jam bermain baru yang tersedia. Jadwal lama Anda tetap aman hingga kasir arena memvalidasi permohonan.
                </p>
            </div>

            <div class="flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-3 backdrop-blur-md border border-white/10 shrink-0">
                <div class="h-10 w-10 rounded-xl bg-[#acf847] text-[#1a146b] flex items-center justify-center font-black text-lg">
                    ⏱️
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-200">Kebijakan Venue</p>
                    <p class="text-xs font-extrabold text-white">Minimal H-1 (24 Jam)</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Alert Flash Messages --}}
    @if (session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-xs font-semibold text-red-800 flex items-start gap-3">
            <span class="text-base">⚠️</span>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-xs font-semibold text-red-800">
            <p class="font-bold">Mohon periksa kembali formulir Anda:</p>
            <ul class="mt-1.5 list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {{-- Kolom Kiri: Ringkasan Jadwal Saat Ini & Aturan (Col 4) --}}
        <div class="lg:col-span-4 space-y-6">
            
            {{-- Kartu Info Jadwal Saat Ini --}}
            <div class="rounded-2xl border border-gray-200/90 bg-white p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Jadwal Asli Saat Ini</span>
                    <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-[10px] font-bold text-[#1a146b]">
                        #BK-{{ strtoupper(substr($jadwal->id, 0, 8)) }}
                    </span>
                </div>

                {{-- Detail Lapangan --}}
                <div class="flex items-center gap-3">
                    @php
                        $fotoUrl = $lapangan?->foto_url ?? asset('storage/' . $lapangan?->foto);
                        $fallbackAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($lapangan?->nama ?? 'Lapangan') . '&background=1a146b&color=acf847&size=256';
                    @endphp
                    <img src="{{ $lapangan?->foto ? $fotoUrl : $fallbackAvatar }}"
                         alt="{{ $lapangan?->nama }}"
                         class="h-14 w-14 rounded-xl object-cover border border-gray-100 shrink-0"
                         onerror="this.onerror=null; this.src='{{ $fallbackAvatar }}';">
                    <div class="min-w-0 flex-1">
                        <span class="rounded bg-[#1a146b]/10 px-1.5 py-0.5 text-[9px] font-bold text-[#1a146b] uppercase">
                            {{ $lapangan?->kategori ?? 'ARENA' }}
                        </span>
                        <h3 class="text-sm font-bold text-gray-900 truncate mt-0.5">
                            {{ $lapangan?->nama }}
                        </h3>
                        <p class="text-[11px] text-gray-500 truncate">
                            {{ $lapangan?->tipe_lantai ?? 'Lantai Standar' }}
                        </p>
                    </div>
                </div>

                {{-- Waktu & Durasi --}}
                <div class="rounded-xl bg-gray-50 p-3 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Tanggal Asli:</span>
                        <span class="font-bold text-gray-900">
                            {{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('l, d M Y') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Jam Kick-off:</span>
                        <span class="font-extrabold text-[#1a146b]">
                            {{ substr((string) $jadwal->jam_mulai, 0, 5) }} – {{ substr((string) $jadwal->jam_selesai, 0, 5) }} WIB
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Durasi Sesi:</span>
                        <span class="font-semibold text-gray-700">{{ $durasiJam }} Jam</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-200/60 pt-2">
                        <span class="text-gray-500">Total Harga:</span>
                        <span class="font-bold text-gray-900">Rp{{ number_format((float) $jadwal->harga_disepakati, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Kartu Ketentuan & Kebijakan Reschedule --}}
            <div class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50/50 to-white p-5 shadow-xs space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-[#1a146b] flex items-center gap-1.5">
                    <span>📌</span> Ketentuan Reschedule
                </h4>
                <ul class="text-xs text-gray-600 space-y-2 leading-relaxed">
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Pengajuan hanya berlaku untuk lapangan yang sama (<strong>{{ $lapangan?->nama }}</strong>).</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Durasi waktu sesi reschedule otomatis disamakan (<strong>{{ $durasiJam }} Jam</strong>).</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Maksimal pengajuan adalah <strong>H-1 (24 jam sebelum kickoff)</strong>.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-indigo-600 font-bold">ℹ️</span>
                        <span>Setelah diajukan, status jadwal akan berstatus <strong>Menunggu Persetujuan Kasir</strong>.</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Kolom Kanan: Formulir Pemilihan Tanggal & Slot Baru (Col 8) --}}
        <div class="lg:col-span-8">
            <div class="rounded-2xl border border-gray-200/90 bg-white p-6 sm:p-8 shadow-sm"
                 x-data="{
                    tanggal: '{{ old('tanggal_baru', $minDate) }}',
                    jamMulai: '{{ old('jam_mulai_baru', '') }}',
                    jamSelesai: '{{ old('jam_selesai_baru', '') }}',
                    selectedSlotLabel: '',
                    slots: [],
                    isLoadingSlots: false,
                    slotError: '',

                    init() {
                        this.fetchSlots();
                    },

                    fetchSlots() {
                        if (!this.tanggal) return;
                        this.isLoadingSlots = true;
                        this.slotError = '';
                        this.jamMulai = '';
                        this.jamSelesai = '';
                        this.selectedSlotLabel = '';

                        const url = '{{ route('pelanggan.booking.reschedule.slots', $jadwal->id) }}?tanggal=' + encodeURIComponent(this.tanggal);

                        fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => {
                            if (!res.ok) throw new Error('Gagal memuat slot jam');
                            return res.json();
                        })
                        .then(data => {
                            this.slots = data.slots || [];
                            this.isLoadingSlots = false;
                        })
                        .catch(err => {
                            this.slotError = err.message || 'Terjadi kesalahan saat memuat jadwal ketersediaan.';
                            this.isLoadingSlots = false;
                        });
                    },

                    selectSlot(slot) {
                        if (!slot.is_available) return;
                        this.jamMulai = slot.jam_mulai;
                        this.jamSelesai = slot.jam_selesai;
                        this.selectedSlotLabel = slot.label;
                    }
                 }">

                <h2 class="text-lg font-extrabold text-gray-900">
                    Pilih Jadwal Pengganti
                </h2>
                <p class="mt-1 text-xs text-gray-500">
                    Tentukan tanggal dan pilih slot jam bermain baru yang masih kosong.
                </p>

                <form action="{{ route('pelanggan.booking.reschedule.store', $jadwal->id) }}" method="POST" class="mt-6 space-y-6">
                    @csrf

                    {{-- 1. Input Tanggal Baru --}}
                    <div>
                        <label for="tanggal_baru" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                            1. Tanggal Pengganti <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-2 relative">
                            <input type="date"
                                   id="tanggal_baru"
                                   name="tanggal_baru"
                                   x-model="tanggal"
                                   @change="fetchSlots()"
                                   min="{{ $minDate }}"
                                   max="{{ $maxDate }}"
                                   required
                                   class="w-full sm:w-80 rounded-xl border-gray-300 shadow-xs text-sm font-semibold focus:border-[#1a146b] focus:ring-[#1a146b]">
                            <p class="mt-1.5 text-[11px] text-gray-400">
                                Tersedia untuk tanggal {{ \Carbon\Carbon::parse($minDate)->translatedFormat('d M Y') }} s.d. {{ \Carbon\Carbon::parse($maxDate)->translatedFormat('d M Y') }} (14 hari ke depan).
                            </p>
                        </div>
                    </div>

                    {{-- 2. Pemilihan Slot Jam Baru --}}
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                                2. Pilih Slot Jam Baru (Durasi: {{ $durasiJam }} Jam) <span class="text-red-500">*</span>
                            </label>
                            
                            {{-- Legenda Slot --}}
                            <div class="flex items-center gap-3 text-[11px]">
                                <span class="inline-flex items-center gap-1 text-emerald-700">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Tersedia
                                </span>
                                <span class="inline-flex items-center gap-1 text-gray-400">
                                    <span class="h-2 w-2 rounded-full bg-gray-300"></span> Terisi / Tidak Tersedia
                                </span>
                            </div>
                        </div>

                        {{-- Hidden Form Inputs --}}
                        <input type="hidden" name="jam_mulai_baru" :value="jamMulai" required>
                        <input type="hidden" name="jam_selesai_baru" :value="jamSelesai" required>

                        {{-- Slot Loader / Error / Grid --}}
                        <div class="mt-3">
                            <div x-show="isLoadingSlots" class="p-8 text-center rounded-2xl bg-gray-50 border border-gray-100">
                                <svg class="animate-spin h-6 w-6 text-[#1a146b] mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <p class="mt-2 text-xs font-semibold text-gray-500">Mengecek ketersediaan lapangan...</p>
                            </div>

                            <div x-show="slotError" x-cloak class="p-4 rounded-xl bg-red-50 text-red-700 text-xs font-semibold">
                                <span x-text="slotError"></span>
                            </div>

                            <div x-show="!isLoadingSlots && !slotError" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <template x-for="slot in slots" :key="slot.jam_mulai">
                                    <button type="button"
                                            @click="selectSlot(slot)"
                                            :disabled="!slot.is_available"
                                            :class="{
                                                'border-[#1a146b] bg-[#1a146b] text-white ring-2 ring-[#acf847] shadow-md': jamMulai === slot.jam_mulai,
                                                'border-emerald-200 bg-emerald-50/50 hover:bg-emerald-100/60 text-emerald-950': slot.is_available && jamMulai !== slot.jam_mulai,
                                                'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed opacity-60': !slot.is_available
                                            }"
                                            class="relative flex flex-col items-center justify-center p-3 rounded-xl border text-center transition cursor-pointer">
                                        
                                        <span class="text-xs font-extrabold" x-text="slot.jam_mulai + ' - ' + slot.jam_selesai"></span>
                                        <span class="text-[10px] mt-1 font-semibold"
                                              :class="jamMulai === slot.jam_mulai ? 'text-[#acf847]' : (slot.is_available ? 'text-emerald-700' : 'text-gray-400')"
                                              x-text="slot.is_available ? (jamMulai === slot.jam_mulai ? '✓ Terpilih' : 'Tersedia') : 'Terisi'">
                                        </span>
                                    </button>
                                </template>
                            </div>

                            {{-- Selected Slot Banner --}}
                            <div x-show="jamMulai && jamSelesai" x-cloak class="mt-4 p-3.5 rounded-xl bg-indigo-50 border border-indigo-200 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="text-base">📅</span>
                                    <div>
                                        <p class="font-bold text-[#1a146b]">Slot Baru Terpilih:</p>
                                        <p class="text-gray-700 font-semibold" x-text="selectedSlotLabel"></p>
                                    </div>
                                </div>
                                <span class="rounded-md bg-[#1a146b] text-[#acf847] px-2.5 py-1 text-[11px] font-black uppercase tracking-wider">
                                    Siap Diajukan
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Alasan Pengajuan Reschedule --}}
                    <div>
                        <label for="alasan_pengajuan" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                            3. Alasan Pengajuan Reschedule <span class="text-red-500">*</span>
                        </label>
                        <p class="mt-0.5 text-[11px] text-gray-400">
                            Berikan keterangan ringkas kepada pengelola venue (misal: kendala anggota tim, cuaca buruk, dinas, dll).
                        </p>
                        <div class="mt-2">
                            <textarea id="alasan_pengajuan"
                                      name="alasan_pengajuan"
                                      rows="3"
                                      required
                                      minlength="5"
                                      maxlength="1000"
                                      placeholder="Contoh: Kami berhalangan hadir karena bentrok turnamen internal tim, ingin mengajukan jadwal pengganti pada tanggal tersebut."
                                      class="w-full rounded-xl border-gray-300 shadow-xs text-sm focus:border-[#1a146b] focus:ring-[#1a146b]">{{ old('alasan_pengajuan') }}</textarea>
                        </div>
                    </div>

                    {{-- Tombol Aksi Submit --}}
                    <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-end gap-3">
                        <a href="{{ route('pelanggan.riwayat') }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50 transition">
                            Batal
                        </a>
                        <button type="submit"
                                :disabled="!jamMulai || !tanggal"
                                :class="(!jamMulai || !tanggal) ? 'opacity-50 cursor-not-allowed bg-gray-400' : 'bg-[#1a146b] hover:bg-indigo-900 cursor-pointer'"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl px-6 py-2.5 text-xs font-bold text-white shadow-md transition">
                            <span>🔄</span>
                            <span>Kirim Permohonan Reschedule</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>
@endsection
