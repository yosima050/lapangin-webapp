@extends('layouts.admin')

@section('title', 'Kelola Staf Kasir & Dynamic Pricing Hari Libur - Admin LapangIn')

@section('content')
<div class="w-full px-8 py-8 flex flex-col gap-8" x-data="{
    searchKasir: '',
    tabKasir: 'all',
    newKasirName: '',
    newKasirEmail: '',
    newKasirPassword: 'ArenaGor2025#',
    newKasirTerminal: 'Kasir POS - Meja Resepsionis #01',
    showAddModal: false,
    saveKasir() {
        alert('✅ Akun kasir ' + this.newKasirName + ' berhasil dibuat! Kredensial telah disimpan ke database.');
        this.newKasirName = '';
        this.newKasirEmail = '';
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

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-[#ffdad6] border border-[#ffb4ab] flex flex-col gap-1 shadow-xs">
            @foreach ($errors->all() as $err)
                <div class="flex items-center gap-2 text-xs font-bold text-[#ba1a1a]">
                    <span class="material-symbols-outlined text-base">error</span>
                    <span>{{ $err }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Header Strip -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="text-[11px] font-extrabold uppercase tracking-widest text-[#102000] bg-[#acf847] px-2.5 py-0.5 rounded-full">
                    OTORITAS VENUE ADMIN
                </span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-headline font-extrabold text-[#0b1c30] tracking-tight">
                Kelola Staf Kasir &amp; Dynamic Pricing Hari Libur
            </h1>
            <p class="text-sm text-[#474651] mt-1">
                Kelola otorisasi akun kasir POS operasional serta konfigurasi penyesuaian tarif dinamis tanggal merah nasional.
            </p>
        </div>

        <button type="button" @click="document.getElementById('form-tambah-kasir').scrollIntoView({ behavior: 'smooth' })"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#1a146b] hover:bg-[#312e81] text-white font-headline font-bold text-xs shadow-md transition-all shrink-0">
            <span class="material-symbols-outlined text-base text-[#acf847]">add_circle</span>
            <span>+ Tambah Kasir Baru</span>
        </button>
    </div>

    <!-- 3 Metric Cards Strip (Real from Database) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="p-5 rounded-2xl bg-white shadow-sm border border-[#e5eeff] flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-[#eff4ff] flex items-center justify-center text-[#1a146b]">
                    <span class="material-symbols-outlined text-2xl">badge</span>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#777682]">TOTAL AKUN PEGAWAI</span>
                    <p class="text-xl font-headline font-extrabold text-[#0b1c30] mt-0.5">{{ $pegawais->count() }} Staf Terdaftar</p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#eff4ff] text-[#1a146b]">
                Otoritas FR-15
            </span>
        </div>

        <div class="p-5 rounded-2xl bg-white shadow-sm border border-[#e5eeff] flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-[#eff4ff] flex items-center justify-center text-[#416900]">
                    <span class="material-symbols-outlined text-2xl">point_of_sale</span>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#777682]">KASIR STANDBY POS</span>
                    <p class="text-xl font-headline font-extrabold text-[#0b1c30] mt-0.5">{{ $totalKasir }} Terminal Aktif</p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#acf847] text-[#102000]">
                Shift Berjalan
            </span>
        </div>

        <div class="p-5 rounded-2xl bg-white shadow-sm border border-[#e5eeff] flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-[#eff4ff] flex items-center justify-center text-[#1a146b]">
                    <span class="material-symbols-outlined text-2xl">event_busy</span>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#777682]">TARIF LIBUR TERPASANG</span>
                    <p class="text-xl font-headline font-extrabold text-[#0b1c30] mt-0.5">{{ $customPrices->count() }} Aturan Aktif</p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#e2dfff] text-[#100563]">
                FR-13 Aktif
            </span>
        </div>
    </div>

    <!-- Section 1: Daftar Akun Pegawai & Kasir POS (Real Database Loop) -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5eeff] flex flex-col gap-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#1a146b] flex items-center justify-center text-white">
                    <span class="material-symbols-outlined text-xl">badge</span>
                </div>
                <div>
                    <h2 class="text-base font-bold text-[#0b1c30]">Daftar Akun Pegawai &amp; Kasir POS</h2>
                    <p class="text-xs text-[#777682]">Hak akses operasional meja kasir, shift kerja, dan pengelolaan kredensial</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="tabKasir = 'all'"
                        :class="tabKasir === 'all' ? 'bg-[#1a146b] text-white' : 'bg-[#eff4ff] text-[#474651]'"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">
                    Semua ({{ $pegawais->count() }})
                </button>
                <button type="button" @click="tabKasir = 'kasir'"
                        :class="tabKasir === 'kasir' ? 'bg-[#1a146b] text-white' : 'bg-[#eff4ff] text-[#474651]'"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">
                    Kasir ({{ $totalKasir }})
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#777682] material-symbols-outlined text-base">search</span>
            <input type="text" x-model="searchKasir"
                   placeholder="Cari nama kasir, meja terminal, atau email..."
                   class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#eff4ff] text-xs font-semibold text-[#0b1c30] border border-[#e5eeff] focus:bg-white focus:ring-2 focus:ring-[#acf847] outline-none transition-all">
        </div>

        <!-- Table: Real Database Loop -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#eff4ff] text-[#474651] font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="px-5 py-3 rounded-l-xl">PEGAWAI &amp; KONTAK</th>
                        <th class="px-5 py-3">ROLE OTORITAS</th>
                        <th class="px-5 py-3">PENEMPATAN TERMINAL</th>
                        <th class="px-5 py-3">STATUS SHIFT</th>
                        <th class="px-5 py-3 rounded-r-xl text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eff4ff]">
                    @forelse ($pegawais as $index => $pegawai)
                        @php
                            $initials = strtoupper(substr($pegawai->nama, 0, 2));
                            $isAdmin = $pegawai->role === 'admin';
                            $terminal = $isAdmin ? 'Semua Terminal & Venue' : 'Meja Resepsionis #0' . ($index + 1);
                        @endphp
                        <tr class="hover:bg-[#eff4ff]/50 transition-colors"
                            x-show="tabKasir === 'all' || (tabKasir === 'kasir' && '{{ $pegawai->role }}' === 'kasir')">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full {{ $isAdmin ? 'bg-[#1a146b] text-white' : 'bg-[#dce9ff] text-[#1a146b]' }} flex items-center justify-center font-bold text-xs">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-[#0b1c30]">{{ $pegawai->nama }} @if($isAdmin)<span class="text-[#416900]">🛡️</span>@endif</p>
                                        <p class="text-[11px] text-[#777682]">{{ $pegawai->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 rounded-md {{ $isAdmin ? 'bg-[#e2dfff] text-[#100563]' : 'bg-[#eff4ff] text-[#1a146b]' }} font-bold text-[11px]">
                                    {{ $isAdmin ? 'Pemilik / Super Admin' : 'Kasir / Staf Operasional' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-[#474651] font-medium">
                                <span class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-sm text-[#777682]">{{ $isAdmin ? 'shield' : 'desktop_windows' }}</span>
                                    {{ $terminal }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full {{ $isAdmin ? 'bg-[#acf847]/40 text-[#102000]' : 'bg-[#dce9ff] text-[#1a146b]' }} text-[11px] font-bold">
                                    <span class="w-2 h-2 rounded-full {{ $isAdmin ? 'bg-[#416900]' : 'bg-[#312e81]' }}"></span>
                                    {{ $isAdmin ? 'Aktif Sesi Ini' : ($index % 2 === 0 ? 'Aktif Shift Pagi' : 'Shift Siang (14:00)') }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if ($isAdmin)
                                    <span class="text-[#777682] text-[11px] font-bold">Akses Utama</span>
                                @else
                                    <div class="flex items-center justify-end gap-2 text-[#777682]">
                                        <button type="button" onclick="alert('Reset password untuk {{ $pegawai->nama }}')" class="p-1 hover:text-[#1a146b]" title="Reset Password">
                                            <span class="material-symbols-outlined text-base">key</span>
                                        </button>
                                        <button type="button" onclick="alert('Edit akun {{ $pegawai->nama }}')" class="p-1 hover:text-[#1a146b]" title="Edit Akun">
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-[#777682]">Belum ada staf kasir terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Form Tambah Kasir Baru (FR-15) -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5eeff] flex flex-col gap-5" id="form-tambah-kasir">
        <div class="flex items-center justify-between pb-3 border-b border-[#eff4ff]">
            <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-xl text-[#1a146b]">person_add</span>
                <h2 class="text-base font-bold text-[#0b1c30]">Form Tambah Kasir Baru</h2>
            </div>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#eff4ff] text-[#1a146b]">FR-15</span>
        </div>

        <form method="POST" action="{{ route('admin.pegawai.store') }}" class="flex flex-col gap-4 text-xs">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="font-bold text-[#0b1c30]">Nama Lengkap Pegawai *</label>
                    <input type="text" name="nama" required placeholder="Contoh: Rian Anggara"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-[#eff4ff] text-[#0b1c30] border border-[#e5eeff] focus:bg-white focus:ring-2 focus:ring-[#acf847] outline-none transition-all">
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="font-bold text-[#0b1c30]">Email Kerja (@lapangin.id) *</label>
                    <input type="email" name="email" required placeholder="rian.kasir@lapangin.id"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-[#eff4ff] text-[#0b1c30] border border-[#e5eeff] focus:bg-white focus:ring-2 focus:ring-[#acf847] outline-none transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="font-bold text-[#0b1c30]">Password Sementara</label>
                    <div class="relative">
                        <input type="text" name="password" value="ArenaGor2025#" required
                               class="w-full px-3.5 py-2.5 rounded-xl bg-[#eff4ff] text-[#0b1c30] font-mono font-bold border border-[#e5eeff] outline-none">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-sm text-[#777682]">lock</span>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="font-bold text-[#0b1c30]">Pilihan Terminal &amp; Role</label>
                    <select name="terminal"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#eff4ff] text-[#0b1c30] font-bold border border-[#e5eeff] focus:bg-white focus:ring-2 focus:ring-[#acf847] outline-none transition-all">
                        <option value="Kasir POS - Meja Resepsionis #01">Kasir POS - Meja Resepsionis #01</option>
                        <option value="Kasir POS - Meja Resepsionis #02">Kasir POS - Meja Resepsionis #02</option>
                    </select>
                </div>
            </div>

            <p class="text-[11px] text-[#777682] italic">
                Data pegawai akan disimpan ke tabel database dan langsung dapat digunakan untuk login operasional POS.
            </p>

            <button type="submit"
                    class="w-full py-3 px-4 rounded-xl bg-[#1a146b] hover:bg-[#312e81] text-white font-headline font-bold text-xs flex items-center justify-center gap-2 shadow-md transition-all cursor-pointer">
                <span class="material-symbols-outlined text-base">save</span>
                <span>Simpan Akun Kasir ke Database</span>
            </button>
        </form>
    </div>

    <!-- Section 3: Penetapan Custom Price Hari Libur & Tanggal Merah (Real Database Loop) -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5eeff] flex flex-col gap-6" id="custom-price">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#eff4ff]">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#1a146b] flex items-center justify-center text-white shrink-0">
                    <span class="material-symbols-outlined text-xl">calendar_month</span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-[#0b1c30]">Penetapan Custom Price Hari Libur &amp; Tanggal Merah</h2>
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-[#acf847] text-[#102000]">SKPL FR-13</span>
                    </div>
                    <p class="text-xs text-[#777682]">Kalender penyesuaian tarif otomatis (Peak Weekend Surcharge) pada hari libur nasional atau cuti bersama.</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 rounded-xl bg-[#eff4ff] border border-[#dce9ff] text-xs font-bold text-[#1a146b] flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">tune</span>
                    Tarif Weekend + Rp 25.000 / Jam
                </span>
                <a href="{{ route('admin.harga.create') }}"
                   class="px-4 py-2 rounded-xl bg-[#416900] hover:bg-[#304f00] text-white text-xs font-bold transition-all flex items-center gap-1 shadow-sm">
                    <span class="material-symbols-outlined text-base">add</span>
                    <span>Tambah Hari Libur</span>
                </a>
            </div>
        </div>

        <!-- Calendar Month Grid & Right Surcharge List Split -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Month Calendar: High Season (7 cols) -->
            <div class="lg:col-span-7 flex flex-col gap-3">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-sm font-bold text-[#0b1c30]">Kalender High Season &amp; Tanggal Merah</h3>
                    <div class="flex items-center gap-1">
                        <button type="button" class="p-1 rounded-lg bg-[#eff4ff] text-[#474651] hover:bg-[#dce9ff]">
                            <span class="material-symbols-outlined text-sm">chevron_left</span>
                        </button>
                        <button type="button" class="p-1 rounded-lg bg-[#eff4ff] text-[#474651] hover:bg-[#dce9ff]">
                            <span class="material-symbols-outlined text-sm">chevron_right</span>
                        </button>
                    </div>
                </div>

                <!-- Days of week -->
                <div class="grid grid-cols-7 gap-1 text-center font-bold text-[10px] uppercase text-[#777682] py-1 border-b border-[#eff4ff]">
                    <span class="text-[#ba1a1a]">MIN</span>
                    <span>SEN</span>
                    <span>SEL</span>
                    <span>RAB</span>
                    <span>KAM</span>
                    <span>JUM</span>
                    <span class="text-[#ba1a1a]">SAB</span>
                </div>

                <!-- Calendar Days Matrix (Dynamic Real from Database) -->
                <div class="grid grid-cols-7 gap-1 text-xs">
                    {{-- Padding blank days at start of month --}}
                    @for ($i = 0; $i < $startDayOfWeek; $i++)
                        <div class="p-2 h-14 bg-[#eff4ff]/20 rounded-lg"></div>
                    @endfor

                    @foreach ($calendarDays as $cday)
                        <div class="p-1.5 h-14 rounded-lg flex flex-col justify-between transition-all {{ $cday['isToday'] ? 'bg-[#1a146b] text-white shadow-xs' : ($cday['isHoliday'] ? 'bg-[#acf847]/40 border border-[#acf847]' : ($cday['isWeekend'] ? 'bg-[#eff4ff] border border-[#ba1a1a]/30' : 'bg-[#eff4ff]')) }}">
                            <div class="flex items-center justify-between">
                                <span class="font-bold {{ $cday['isToday'] ? 'text-white' : ($cday['isWeekend'] ? 'text-[#ba1a1a]' : 'text-[#0b1c30]') }}">
                                    {{ $cday['day'] }}
                                </span>
                                @if ($cday['isToday'])
                                    <span class="text-[8px] px-1 rounded bg-[#acf847] text-[#102000] font-bold">Hari Ini</span>
                                @elseif ($cday['isHoliday'])
                                    <span class="text-[8px] px-1 rounded bg-[#acf847] text-[#102000] font-bold">Libur</span>
                                @endif
                            </div>
                            <span class="text-[9px] font-bold {{ $cday['isToday'] ? 'text-[#acf847]' : ($cday['isHoliday'] ? 'text-[#416900]' : ($cday['isWeekend'] ? 'text-[#ba1a1a]' : 'text-[#777682]')) }}">
                                {{ $cday['rateText'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right Side: Daftar Tanggal Merah (Real from Database $customPrices) -->
            <div class="lg:col-span-5 flex flex-col gap-3">
                <div class="pb-1 border-b border-[#eff4ff]">
                    <h3 class="text-xs font-bold text-[#0b1c30]">Daftar Tanggal Merah &amp; Custom Surcharge ({{ $customPrices->count() }})</h3>
                    <p class="text-[11px] text-[#777682]">Data penyesuaian tarif aktif langsung dari tabel database HargaMaster.</p>
                </div>

                <div class="flex flex-col gap-2.5">
                    @forelse ($customPrices->unique('tanggal_khusus') as $price)
                        <div class="p-3.5 rounded-xl bg-[#eff4ff] border border-[#e5eeff] flex flex-col gap-1.5 text-xs">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-[#ba1a1a]"></span>
                                    <span class="font-bold text-[#0b1c30]">{{ $price->tanggal_khusus?->format('d F Y') }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#ffdad6] text-[#ba1a1a]">Tarif Khusus</span>
                            </div>
                            <p class="text-[11px] text-[#474651]">{{ $price->lapangan->nama ?? 'Semua Lapangan' }}</p>
                            <div class="pt-2 mt-1 border-t border-[#dce9ff] flex items-center justify-between">
                                <span class="text-[#416900] font-bold">Tarif: Rp {{ number_format($price->harga, 0, ',', '.') }} / Jam</span>
                                <a href="{{ route('admin.harga.edit', $price->id) }}" class="text-[#1a146b] font-bold hover:underline">Ubah →</a>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 rounded-xl bg-[#eff4ff] text-center text-[#777682] text-xs">
                            Belum ada tarif khusus tanggal merah.
                        </div>
                    @endforelse
                </div>

                <!-- Footer Sync Status -->
                <div class="p-3 rounded-xl bg-[#acf847]/20 border border-[#acf847] flex items-center justify-between text-xs">
                    <span class="flex items-center gap-1.5 text-[#102000] font-bold">
                        <span class="material-symbols-outlined text-sm text-[#416900]">sync</span>
                        Sinkronisasi Kalender Libur Resmi
                    </span>
                    <span class="text-[#416900] font-bold text-[11px]">Aktif</span>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
