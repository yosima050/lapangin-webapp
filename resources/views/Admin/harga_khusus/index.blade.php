@extends('layouts.admin')

@section('title', 'Pengaturan Harga Khusus - Admin LapangIn')

@section('content')
<div class="w-full px-8 py-8 flex flex-col gap-6"
    x-data="{
        editModalOpen: false,
        activeTab: 'aktif',
        editData: {
            id: '',
            lapangan_id: '',
            tanggal: '',
            jam_mulai: '',
            jam_selesai: '',
            harga: '',
            keterangan: '',
            is_active: true
        },
        calendarYear: new Date().getFullYear(),
        calendarMonth: new Date().getMonth(),
        calendarEvents: {{ Js::from($calendarEvents) }},
        todayStr: '{{ $today }}',
        selectedCalendarDate: null,
        selectedDateEvents: [],
        monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
        dayNames: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],

        get currentMonthLabel() {
            return this.monthNames[this.calendarMonth] + ' ' + this.calendarYear;
        },

        prevMonth() {
            if (this.calendarMonth === 0) {
                this.calendarMonth = 11;
                this.calendarYear--;
            } else {
                this.calendarMonth--;
            }
            this.selectedCalendarDate = null;
        },

        nextMonth() {
            if (this.calendarMonth === 11) {
                this.calendarMonth = 0;
                this.calendarYear++;
            } else {
                this.calendarMonth++;
            }
            this.selectedCalendarDate = null;
        },

        goToToday() {
            const now = new Date();
            this.calendarYear = now.getFullYear();
            this.calendarMonth = now.getMonth();
            this.selectedCalendarDate = null;
        },

        get daysInMonth() {
            const year = this.calendarYear;
            const month = this.calendarMonth;
            const firstDayIndex = new Date(year, month, 1).getDay();
            const totalDays = new Date(year, month + 1, 0).getDate();
            
            let days = [];
            for (let i = 0; i < firstDayIndex; i++) {
                days.push({ day: null, dateStr: '', events: [], isToday: false, isPast: false });
            }
            for (let d = 1; d <= totalDays; d++) {
                const mStr = String(month + 1).padStart(2, '0');
                const dStr = String(d).padStart(2, '0');
                const dateStr = `${year}-${mStr}-${dStr}`;
                const events = this.calendarEvents[dateStr] || [];
                days.push({
                    day: d,
                    dateStr: dateStr,
                    events: events,
                    hasEvents: events.length > 0,
                    isToday: dateStr === this.todayStr,
                    isPast: dateStr < this.todayStr
                });
            }
            return days;
        },

        selectDate(dateObj) {
            if (!dateObj || !dateObj.dateStr) return;
            this.selectedCalendarDate = dateObj.dateStr;
            this.selectedDateEvents = dateObj.events || [];
            
            const dateInput = document.getElementById('tanggal');
            if (dateInput) {
                dateInput.value = dateObj.dateStr;
            }
        }
    }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-semibold text-[#416900]">
                PENGATURAN TARIF
            </span>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-[#0b1c30] mt-1">
                Pengaturan Harga Khusus
            </h1>
            <p class="text-sm text-[#474651] mt-1">
                Atur tarif khusus berdasarkan tanggal dan rentang jam tanpa mengubah harga master.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="#tambah-harga-khusus"
               class="inline-flex items-center gap-2 rounded-xl bg-[#1a146b] hover:bg-[#312e81] px-4 py-2.5 text-xs font-extrabold text-white shadow-sm transition">
                <span class="material-symbols-outlined text-[#acf847] text-lg">add_circle</span>
                <span>Tambah Harga Khusus</span>
            </a>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 flex items-center gap-2 text-sm font-semibold">
            <span class="material-symbols-outlined text-green-600">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
            <p class="font-bold mb-2 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-red-600 text-base">error</span>
                <span>Periksa kembali data berikut:</span>
            </p>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Kalender Harga Khusus --}}
    <div class="bg-white rounded-2xl shadow-sm border border-[#e5eeff] p-6 flex flex-col gap-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#e5eeff] pb-4">
            <div>
                <h2 class="text-lg font-bold text-[#0b1c30] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#1a146b]">calendar_month</span>
                    <span>Kalender Tanggal Harga Khusus</span>
                </h2>
                <p class="text-xs text-[#777682] mt-0.5">
                    Lihat jadwal hari libur atau tanggal khusus yang memiliki tarif berbeda.
                </p>
            </div>

            {{-- Navigasi Bulan & Legend --}}
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 bg-[#eff4ff] p-1 rounded-xl border border-[#dce9ff]">
                    <button type="button" @click="prevMonth()"
                            class="p-1.5 rounded-lg hover:bg-white text-[#1a146b] transition cursor-pointer"
                            title="Bulan Sebelumnya">
                        <span class="material-symbols-outlined text-lg leading-none">chevron_left</span>
                    </button>
                    <span class="px-3 text-xs font-bold text-[#0b1c30] min-w-[130px] text-center"
                          x-text="currentMonthLabel"></span>
                    <button type="button" @click="nextMonth()"
                            class="p-1.5 rounded-lg hover:bg-white text-[#1a146b] transition cursor-pointer"
                            title="Bulan Berikutnya">
                        <span class="material-symbols-outlined text-lg leading-none">chevron_right</span>
                    </button>
                </div>

                <button type="button" @click="goToToday()"
                        class="px-3 py-2 rounded-xl bg-white border border-[#d8deeb] hover:bg-[#eff4ff] text-[#1a146b] text-xs font-bold transition cursor-pointer">
                    Hari Ini
                </button>
            </div>
        </div>

        {{-- Legend Petunjuk --}}
        <div class="flex flex-wrap items-center gap-4 text-xs text-[#474651]">
            <span class="font-bold text-[#0b1c30]">Keterangan:</span>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-[#acf847] border border-[#7eb828]"></span>
                <span>Tarif Khusus Aktif / Mendatang</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-slate-200 border border-slate-300"></span>
                <span>Selesai (Sudah Terlewat)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-md border-2 border-[#1a146b]"></span>
                <span>Hari Ini</span>
            </div>
        </div>

        {{-- Kalender Grid --}}
        <div class="overflow-x-auto">
            <div class="min-w-[680px]">
                {{-- Nama Hari --}}
                <div class="grid grid-cols-7 gap-2 mb-2 text-center text-xs font-bold text-[#777682] uppercase tracking-wider">
                    <template x-for="dname in dayNames" :key="dname">
                        <div class="py-1" x-text="dname"></div>
                    </template>
                </div>

                {{-- Kotak Tanggal --}}
                <div class="grid grid-cols-7 gap-2">
                    <template x-for="(cell, idx) in daysInMonth" :key="idx">
                        <div class="min-h-[86px] rounded-xl border p-2 flex flex-col justify-between transition relative text-xs"
                             :class="{
                                 'bg-gray-50/50 border-dashed border-gray-200 opacity-40': !cell.day,
                                 'bg-white border-[#e5eeff] hover:border-[#1a146b] hover:shadow-xs cursor-pointer': cell.day && !cell.hasEvents,
                                 'bg-emerald-50/60 border-emerald-300 hover:border-emerald-500 cursor-pointer shadow-xs': cell.day && cell.hasEvents && !cell.isPast,
                                 'bg-slate-50 border-slate-200 hover:border-slate-400 cursor-pointer': cell.day && cell.hasEvents && cell.isPast,
                                 'ring-2 ring-[#1a146b]': cell.isToday,
                                 'ring-2 ring-emerald-600': selectedCalendarDate === cell.dateStr
                             }"
                             @click="cell.day ? selectDate(cell) : null">
                            
                            {{-- Tanggal Number & Badge Hari Ini --}}
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs"
                                      :class="{
                                          'text-[#0b1c30]': cell.day && !cell.isToday,
                                          'text-[#1a146b] font-extrabold': cell.isToday
                                      }"
                                      x-text="cell.day || ''"></span>

                                <template x-if="cell.isToday">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-[#1a146b] text-[#acf847]">
                                        HARI INI
                                    </span>
                                </template>
                            </div>

                            {{-- Indikator Event Harga Khusus --}}
                            <div class="mt-1 flex flex-col gap-1">
                                <template x-for="(ev, eidx) in cell.events.slice(0, 2)" :key="eidx">
                                    <div class="px-1.5 py-0.5 rounded text-[10px] font-bold truncate flex items-center gap-1"
                                         :class="cell.isPast ? 'bg-slate-200 text-slate-700' : 'bg-[#acf847] text-[#102000]'"
                                         :title="ev.keterangan + ' (' + ev.lapangan + ') Rp ' + Number(ev.harga).toLocaleString('id-ID')">
                                        <span class="truncate" x-text="ev.keterangan || ev.lapangan"></span>
                                    </div>
                                </template>

                                <template x-if="cell.events.length > 2">
                                    <span class="text-[9px] font-bold text-[#1a146b]"
                                          x-text="'+' + (cell.events.length - 2) + ' tarif lagi'"></span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Detail Tanggal Terpilih --}}
        <div x-show="selectedCalendarDate" x-cloak
             class="p-4 rounded-xl bg-[#eff4ff] border border-[#dce9ff] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-xl text-[#1a146b] shrink-0 mt-0.5">event</span>
                <div>
                    <h4 class="text-xs font-bold text-[#0b1c30]">
                        Detail Tanggal: <span class="text-[#1a146b]" x-text="selectedCalendarDate"></span>
                    </h4>
                    <template x-if="selectedDateEvents.length > 0">
                        <div class="mt-1 flex flex-wrap gap-2">
                            <template x-for="(ev, idx) in selectedDateEvents" :key="idx">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold"
                                      :class="ev.is_past ? 'bg-slate-200 text-slate-700' : 'bg-[#acf847] text-[#102000]'">
                                    <span x-text="ev.lapangan + ': Rp ' + Number(ev.harga).toLocaleString('id-ID') + ' (' + ev.jam_mulai + ' - ' + ev.jam_selesai + ')'"></span>
                                </span>
                            </template>
                        </div>
                    </template>
                    <template x-if="selectedDateEvents.length === 0">
                        <p class="text-xs text-[#777682] mt-0.5">
                            Belum ada tarif khusus pada tanggal ini. Klik tombol di kanan untuk mengisi tanggal ke form di bawah.
                        </p>
                    </template>
                </div>
            </div>

            <a href="#tambah-harga-khusus"
               class="px-4 py-2 rounded-xl bg-[#1a146b] text-white text-xs font-bold hover:bg-[#312e81] transition shrink-0 inline-flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm text-[#acf847]">add</span>
                <span>Pilih untuk Tambah Tarif</span>
            </a>
        </div>
    </div>

    {{-- Form tambah harga khusus --}}
    <div id="tambah-harga-khusus" class="bg-white rounded-2xl shadow-sm border border-[#e5eeff] overflow-hidden scroll-mt-6">
        <div class="px-6 py-5 border-b border-[#e5eeff]">
            <h2 class="text-lg font-bold text-[#0b1c30]">
                Tambah Harga Khusus Baru
            </h2>
            <p class="text-sm text-[#777682] mt-1">
                Tentukan lapangan, tanggal, tarif, dan jam berlakunya harga khusus.
            </p>
        </div>

        <form action="{{ route('admin.harga-khusus.store') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">

                <div>
                    <label for="lapangan_id" class="block text-sm font-semibold text-[#474651] mb-2">
                        Lapangan
                    </label>
                    <select name="lapangan_id" id="lapangan_id" required
                        class="w-full rounded-xl border border-[#d8deeb] bg-white px-4 py-3 text-sm text-[#0b1c30] focus:outline-none focus:ring-2 focus:ring-[#1a146b]">
                        <option value="">Pilih lapangan</option>
                        <option value="all" class="font-bold text-[#1a146b]" @selected(old('lapangan_id') == 'all')>⭐ Semua Lapangan (Terapkan ke Semua)</option>
                        @foreach ($lapangans as $lapangan)
                            <option value="{{ $lapangan->id }}"
                                @selected(old('lapangan_id') == $lapangan->id)>
                                {{ $lapangan->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tanggal" class="block text-sm font-semibold text-[#474651] mb-2">
                        Tanggal
                    </label>
                    <input type="date" name="tanggal" id="tanggal"
                        value="{{ old('tanggal') }}" required
                        class="w-full rounded-xl border border-[#d8deeb] bg-white px-4 py-3 text-sm text-[#0b1c30] focus:outline-none focus:ring-2 focus:ring-[#1a146b]">
                </div>

                <div>
                    <label for="harga" class="block text-sm font-semibold text-[#474651] mb-2">
                        Tarif (Rp)
                    </label>
                    <input type="number" name="harga" id="harga"
                        min="0" max="9999999999.99" step="0.01"
                        value="{{ old('harga') }}"
                        placeholder="Contoh: 175000" required
                        class="w-full rounded-xl border border-[#d8deeb] bg-white px-4 py-3 text-sm text-[#0b1c30] focus:outline-none focus:ring-2 focus:ring-[#1a146b]">
                </div>

                <div>
                    <label for="jam_mulai" class="block text-sm font-semibold text-[#474651] mb-2">
                        Jam Mulai
                    </label>
                    <input type="time" name="jam_mulai" id="jam_mulai"
                        value="{{ old('jam_mulai', '07:00') }}" required
                        class="w-full rounded-xl border border-[#d8deeb] bg-white px-4 py-3 text-sm text-[#0b1c30] focus:outline-none focus:ring-2 focus:ring-[#1a146b]">
                </div>

                <div>
                    <label for="jam_selesai" class="block text-sm font-semibold text-[#474651] mb-2">
                        Jam Selesai
                    </label>
                    <input type="time" name="jam_selesai" id="jam_selesai"
                        value="{{ old('jam_selesai', '23:00') }}" required
                        class="w-full rounded-xl border border-[#d8deeb] bg-white px-4 py-3 text-sm text-[#0b1c30] focus:outline-none focus:ring-2 focus:ring-[#1a146b]">
                </div>

                <div>
                    <label for="keterangan" class="block text-sm font-semibold text-[#474651] mb-2">
                        Keterangan (opsional)
                    </label>
                    <input type="text" name="keterangan" id="keterangan"
                        maxlength="150" value="{{ old('keterangan') }}"
                        placeholder="Contoh: Hari libur nasional / HUT RI"
                        class="w-full rounded-xl border border-[#d8deeb] bg-white px-4 py-3 text-sm text-[#0b1c30] focus:outline-none focus:ring-2 focus:ring-[#1a146b]">
                </div>

            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#1a146b] px-5 py-3 text-sm font-bold text-white hover:bg-[#312e81] transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[#acf847] text-xl">save</span>
                    Simpan Harga Khusus
                </button>
            </div>
        </form>
    </div>

    {{-- Daftar aturan harga with Tabs: Aktif & Mendatang vs Selesai --}}
    <div class="bg-white rounded-2xl shadow-sm border border-[#e5eeff] overflow-hidden">
        {{-- Tab Navigation Bar --}}
        <div class="px-6 pt-5 pb-0 border-b border-[#e5eeff] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-[#0b1c30]">
                    Daftar Aturan Harga Khusus
                </h2>
                <p class="text-sm text-[#777682] mt-0.5">
                    Kelola tarif khusus aktif, mendatang, dan riwayat yang telah selesai.
                </p>
            </div>

            <div class="flex items-center gap-2 border-b sm:border-b-0 border-[#e5eeff] pb-3 sm:pb-0">
                <button type="button"
                    @click="activeTab = 'aktif'"
                    :class="activeTab === 'aktif' ? 'bg-[#1a146b] text-white font-extrabold shadow-sm' : 'bg-gray-100 text-[#474651] font-semibold hover:bg-gray-200'"
                    class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition cursor-pointer">
                    <span class="material-symbols-outlined text-sm" :class="activeTab === 'aktif' ? 'text-[#acf847]' : 'text-gray-500'">event_available</span>
                    <span>Tarif Aktif &amp; Mendatang</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold"
                          :class="activeTab === 'aktif' ? 'bg-white/20 text-[#acf847]' : 'bg-white text-gray-700'">
                        {{ $hargaKhususAktif->count() }}
                    </span>
                </button>

                <button type="button"
                    @click="activeTab = 'selesai'"
                    :class="activeTab === 'selesai' ? 'bg-[#1a146b] text-white font-extrabold shadow-sm' : 'bg-gray-100 text-[#474651] font-semibold hover:bg-gray-200'"
                    class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition cursor-pointer">
                    <span class="material-symbols-outlined text-sm" :class="activeTab === 'selesai' ? 'text-[#acf847]' : 'text-gray-500'">history</span>
                    <span>Selesai (Riwayat)</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold"
                          :class="activeTab === 'selesai' ? 'bg-white/20 text-[#acf847]' : 'bg-white text-gray-700'">
                        {{ $hargaKhususSelesai->count() }}
                    </span>
                </button>
            </div>
        </div>

        {{-- TAB 1: TARIF AKTIF & MENDATANG --}}
        <div x-show="activeTab === 'aktif'" class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-[#eff4ff] text-[#474651]">
                    <tr>
                        <th class="px-5 py-4 font-bold">Lapangan</th>
                        <th class="px-5 py-4 font-bold">Tanggal</th>
                        <th class="px-5 py-4 font-bold">Rentang Jam</th>
                        <th class="px-5 py-4 font-bold">Tarif</th>
                        <th class="px-5 py-4 font-bold">Keterangan</th>
                        <th class="px-5 py-4 font-bold">Status</th>
                        <th class="px-5 py-4 font-bold">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#e5eeff]">
                    @forelse ($hargaKhususAktif as $item)
                        <tr class="hover:bg-[#f8faff]">
                            <td class="px-5 py-4 font-semibold text-[#0b1c30]">
                                {{ $item->lapangan?->nama ?? 'Semua Lapangan' }}
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap font-medium text-[#0b1c30]">
                                {{ $item->tanggal->format('d-m-Y') }}
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                {{ substr($item->jam_mulai, 0, 5) }}
                                – {{ substr($item->jam_selesai, 0, 5) }}
                            </td>

                            <td class="px-5 py-4 font-bold text-[#0b1c30] whitespace-nowrap">
                                Rp {{ number_format((float) $item->harga, 0, ',', '.') }}
                            </td>

                            <td class="px-5 py-4 text-[#777682]">
                                {{ $item->keterangan ?: '—' }}
                            </td>

                            <td class="px-5 py-4">
                                @if ($item->is_active)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-[#acf847] text-[#102000] text-xs font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#102000]"></span>
                                        <span>Aktif</span>
                                    </span>
                                @else
                                    <span class="inline-flex px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                        @click="editData = {
                                            id: '{{ $item->id }}',
                                            lapangan_id: '{{ $item->lapangan_id }}',
                                            tanggal: '{{ $item->tanggal->format('Y-m-d') }}',
                                            jam_mulai: '{{ substr($item->jam_mulai, 0, 5) }}',
                                            jam_selesai: '{{ substr($item->jam_selesai, 0, 5) }}',
                                            harga: '{{ (float) $item->harga }}',
                                            keterangan: '{{ addslashes($item->keterangan ?? '') }}',
                                            is_active: {{ $item->is_active ? 'true' : 'false' }}
                                        }; editModalOpen = true;"
                                        class="whitespace-nowrap rounded-lg bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-100 transition-colors">
                                        Edit
                                    </button>

                                    <form
                                        action="{{ route('admin.harga-khusus.toggle-status', $item->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                            class="whitespace-nowrap rounded-lg px-3 py-2 text-xs font-bold transition-colors {{ $item->is_active ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                                            {{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    <form
                                        action="{{ route('admin.harga-khusus.destroy', $item->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus aturan harga ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            class="whitespace-nowrap rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100 transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-[#777682]">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-3xl text-gray-400">event_available</span>
                                    <p class="font-bold text-sm text-[#0b1c30]">Belum ada jadwal tarif khusus mendatang</p>
                                    <p class="text-xs text-[#777682]">Tarif khusus yang tanggalnya sudah lewat otomatis dipindahkan ke tab Riwayat "Selesai".</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TAB 2: SELESAI (RIWAYAT) --}}
        <div x-show="activeTab === 'selesai'" x-cloak class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-[#eff4ff] text-[#474651]">
                    <tr>
                        <th class="px-5 py-4 font-bold">Lapangan</th>
                        <th class="px-5 py-4 font-bold">Tanggal</th>
                        <th class="px-5 py-4 font-bold">Rentang Jam</th>
                        <th class="px-5 py-4 font-bold">Tarif</th>
                        <th class="px-5 py-4 font-bold">Keterangan</th>
                        <th class="px-5 py-4 font-bold">Status</th>
                        <th class="px-5 py-4 font-bold">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#e5eeff]">
                    @forelse ($hargaKhususSelesai as $item)
                        <tr class="hover:bg-[#f8faff] opacity-90">
                            <td class="px-5 py-4 font-semibold text-[#0b1c30]">
                                {{ $item->lapangan?->nama ?? 'Semua Lapangan' }}
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap text-slate-700 font-medium">
                                {{ $item->tanggal->format('d-m-Y') }}
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap text-slate-600">
                                {{ substr($item->jam_mulai, 0, 5) }}
                                – {{ substr($item->jam_selesai, 0, 5) }}
                            </td>

                            <td class="px-5 py-4 font-bold text-[#0b1c30] whitespace-nowrap">
                                Rp {{ number_format((float) $item->harga, 0, ',', '.') }}
                            </td>

                            <td class="px-5 py-4 text-[#777682]">
                                <span>{{ $item->keterangan ?: 'Tarif Khusus Tanggal' }}</span>
                                <span class="text-[11px] text-slate-400 block font-normal">(Telah Berakhir)</span>
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200">
                                    <span class="material-symbols-outlined text-xs text-slate-500">check_circle</span>
                                    <span>Selesai</span>
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <form
                                        action="{{ route('admin.harga-khusus.destroy', $item->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Hapus permanen riwayat harga khusus ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            class="whitespace-nowrap rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100 transition-colors">
                                            Hapus Riwayat
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-[#777682]">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-3xl text-gray-400">history</span>
                                    <p class="font-bold text-sm text-[#0b1c30]">Belum ada riwayat tarif khusus yang selesai</p>
                                    <p class="text-xs text-[#777682]">Tarif khusus yang telah lewat tanggalnya akan otomatis masuk ke sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Edit Harga Khusus --}}
    <div x-show="editModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
        <div @click.away="editModalOpen = false"
             class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-2xl w-full p-6 transition-all transform">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-[#0b1c30]">
                    Edit Aturan Harga Khusus
                </h3>
                <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form :action="'{{ url('admin/harga-khusus') }}/' + editData.id" method="POST" class="mt-5 space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#474651] mb-1.5">Lapangan</label>
                        <select name="lapangan_id" x-model="editData.lapangan_id" required
                                class="w-full rounded-xl border border-[#d8deeb] bg-white px-3 py-2.5 text-sm text-[#0b1c30] focus:ring-2 focus:ring-[#1a146b] outline-none">
                            <option value="">Pilih lapangan</option>
                            @foreach ($lapangans as $lapangan)
                                <option value="{{ $lapangan->id }}">{{ $lapangan->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#474651] mb-1.5">Tanggal</label>
                        <input type="date" name="tanggal" x-model="editData.tanggal" required
                               class="w-full rounded-xl border border-[#d8deeb] bg-white px-3 py-2.5 text-sm text-[#0b1c30] focus:ring-2 focus:ring-[#1a146b] outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#474651] mb-1.5">Jam Mulai</label>
                        <input type="time" name="jam_mulai" x-model="editData.jam_mulai" required
                               class="w-full rounded-xl border border-[#d8deeb] bg-white px-3 py-2.5 text-sm text-[#0b1c30] focus:ring-2 focus:ring-[#1a146b] outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#474651] mb-1.5">Jam Selesai</label>
                        <input type="time" name="jam_selesai" x-model="editData.jam_selesai" required
                               class="w-full rounded-xl border border-[#d8deeb] bg-white px-3 py-2.5 text-sm text-[#0b1c30] focus:ring-2 focus:ring-[#1a146b] outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#474651] mb-1.5">Tarif (Rp)</label>
                        <input type="number" name="harga" x-model="editData.harga" min="0" step="0.01" required
                               class="w-full rounded-xl border border-[#d8deeb] bg-white px-3 py-2.5 text-sm text-[#0b1c30] focus:ring-2 focus:ring-[#1a146b] outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#474651] mb-1.5">Keterangan (opsional)</label>
                        <input type="text" name="keterangan" x-model="editData.keterangan" maxlength="150"
                               class="w-full rounded-xl border border-[#d8deeb] bg-white px-3 py-2.5 text-sm text-[#0b1c30] focus:ring-2 focus:ring-[#1a146b] outline-none">
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="editModalOpen = false"
                            class="px-4 py-2.5 rounded-xl border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-[#1a146b] text-xs font-bold text-white hover:bg-[#312e81] transition-colors inline-flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-[#acf847]">check</span>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
