@extends('layouts.admin')

@section('title', 'Kelola Akun Pegawai & Kasir - Admin LapangIn')

@section('content')
<div class="w-full px-8 py-8 flex flex-col gap-8" x-data="{
    searchKasir: '',
    tabKasir: 'all',
    newKasirName: '',
    newKasirEmail: '',
    newKasirPassword: 'KasirLapangIn{{ date('Y') }}!',
    newKasirTerminal: 'Kasir POS - Meja Resepsionis #01',
    showAddModal: false,
    saveKasir() {
        alert('Akun kasir ' + this.newKasirName + ' berhasil dibuat! Kredensial telah disimpan ke database.');
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
                Kelola Akun Pegawai &amp; Kasir
            </h1>
            <p class="text-sm text-[#474651] mt-1">
                Kelola otorisasi akun kasir POS operasional, pengaturan shift meja kasir, dan kredensial staf venue.
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
                Role Pegawai
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
                    <span class="material-symbols-outlined text-2xl">admin_panel_settings</span>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#777682]">ADMINISTRATOR SISTEM</span>
                    <p class="text-xl font-headline font-extrabold text-[#0b1c30] mt-0.5">{{ $totalAdmin }} Admin Utama</p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#e2dfff] text-[#100563]">
                Full Access
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

                <tr
                    class="hover:bg-[#eff4ff]/50 transition-colors"
                    x-show="(tabKasir === 'all' || (tabKasir === 'kasir' && @js($pegawai->role) === 'kasir'))
                        && (@js(strtolower($pegawai->nama)) + ' ' + @js(strtolower($pegawai->email)))
                            .includes(searchKasir.toLowerCase().trim())"
                >

                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full {{ $isAdmin ? 'bg-[#1a146b] text-white' : 'bg-[#dce9ff] text-[#1a146b]' }} flex items-center justify-center font-bold text-xs">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <p class="font-bold text-[#0b1c30]">{{ $pegawai->nama }}</p>
                                            @if($isAdmin)
                                                <span class="inline-flex items-center text-[#416900]" title="Administrator">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                                    </svg>
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-[#777682]">{{ $pegawai->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if ((string) auth()->id() !== (string) $pegawai->id)
                                    <form
                                        action="{{ route('admin.pegawai.update-role', $pegawai->id) }}"
                                        method="POST"
                                        class="flex items-center gap-2"
                                        onsubmit="return confirm('Yakin ingin mengubah role pegawai ini?')"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <select
                                            name="role"
                                            aria-label="Role {{ $pegawai->nama }}"
                                            class="max-w-[190px] px-2.5 py-2 rounded-lg border border-[#e5eeff] bg-white text-[11px] font-bold text-[#1a146b]"
                                        >
                                            <option value="kasir" @selected($pegawai->role === 'kasir')>
                                                Kasir / Staf Operasional
                                            </option>
                                            <option value="admin" @selected($pegawai->role === 'admin')>
                                                Pemilik / Super Admin
                                            </option>
                                        </select>

                                        <button
                                            type="submit"
                                            class="px-3 py-2 rounded-lg bg-[#eff4ff] hover:bg-[#dce9ff] text-[#1a146b] text-[11px] font-bold"
                                        >
                                            Simpan
                                        </button>
                                    </form>
                                @else
                                    <span class="px-2.5 py-1 rounded-md bg-[#e2dfff] text-[#100563] font-bold text-[11px]">
                                        Pemilik / Super Admin
                                    </span>
                                    <p class="mt-1 text-[10px] text-[#777682]">Role akun sendiri terkunci</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-[#474651] font-medium">
                                <span class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-sm text-[#777682]">{{ $isAdmin ? 'shield' : 'desktop_windows' }}</span>
                                    {{ $terminal }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                @if ($isAdmin)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#acf847]/40 text-[#102000] text-[11px] font-bold">
                                        <span class="w-2 h-2 rounded-full bg-[#416900]"></span>
                                        Akun Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full {{ $pegawai->is_active ? 'bg-[#acf847]/40 text-[#102000]' : 'bg-[#ffdad6] text-[#ba1a1a]' }} text-[11px] font-bold">
                                        <span class="w-2 h-2 rounded-full {{ $pegawai->is_active ? 'bg-[#416900]' : 'bg-[#ba1a1a]' }}"></span>
                                        {{ $pegawai->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                @endif
                            </td>


                            <td class="px-5 py-4 text-right">
                                @if ($isAdmin)
                                    <span class="text-[#777682] text-[11px] font-bold">
                                        Akses Utama
                                    </span>
                                @else
                                    <form
                                        action="{{ route('admin.pegawai.toggle-status', $pegawai->id) }}"
                                        method="POST"
                                        class="flex justify-end"
                                        onsubmit="return confirm('Yakin ingin {{ $pegawai->is_active ? 'menonaktifkan' : 'mengaktifkan' }} akun kasir ini?')"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="px-3 py-1.5 rounded-lg text-[11px] font-bold transition-colors {{ $pegawai->is_active ? 'bg-[#ffdad6] text-[#ba1a1a] hover:bg-[#ffb4ab]' : 'bg-[#acf847] text-[#102000] hover:bg-[#92dc32]' }}"
                                        >
                                            {{ $pegawai->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
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

    <!-- Section 2: Form Tambah Kasir Baru -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5eeff] flex flex-col gap-5" id="form-tambah-kasir">
        <div class="flex items-center justify-between pb-3 border-b border-[#eff4ff]">
            <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-xl text-[#1a146b]">person_add</span>
                <h2 class="text-base font-bold text-[#0b1c30]">Form Tambah Kasir Baru</h2>
            </div>
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


            <div class="flex flex-col gap-1.5">
                <label class="font-bold text-[#0b1c30]">Password Sementara *</label>
                <div class="relative">
                    <input
                        type="password"
                        name="password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        placeholder="Masukkan password sementara"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-[#eff4ff] text-[#0b1c30] font-mono font-bold border border-[#e5eeff] focus:bg-white focus:ring-2 focus:ring-[#acf847] outline-none transition-all"
                    >
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
</div>
@endsection
