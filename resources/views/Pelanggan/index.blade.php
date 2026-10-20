@extends('layouts.pelanggan')

@section('title', 'Kelola Profil & Pengaturan Akun - LapangIn')

@section('content')
<div class="space-y-6" style="font-family: 'Plus Jakarta Sans', sans-serif;">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col gap-2">
        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#777682]">
            <a href="{{ route('home') }}" class="hover:text-[#1a146b] transition">BERANDA</a>
            <span>›</span>
            <span class="text-[#1a146b]">PROFIL SAYA</span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#0b1c30]">
            Pengaturan Akun &amp; Profil Pelanggan
        </h1>

        <p class="text-xs sm:text-sm text-[#474651] max-w-2xl leading-relaxed">
            Kelola identitas tanding, sinkronisasi WhatsApp OTP, dan amankan akun reservasi gelanggang olahraga Anda secara terpusat.
        </p>
    </div>

    {{-- Alert Notifikasi Status --}}
    @if (session('status') === 'profile-updated')
        <div class="flex items-center gap-3 p-4 rounded-2xl bg-[#dcfce7] border border-[#bbf7d0] text-[#166534] text-xs sm:text-sm font-bold shadow-xs">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#22c55e] text-white">✓</span>
            Profil Anda berhasil diperbarui dan disinkronisasikan ke sistem.
        </div>
    @elseif (session('status') === 'password-updated')
        <div class="flex items-center gap-3 p-4 rounded-2xl bg-[#dcfce7] border border-[#bbf7d0] text-[#166534] text-xs sm:text-sm font-bold shadow-xs">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#22c55e] text-white">✓</span>
            Kata sandi Anda berhasil diperbarui dengan enkripsi Bcrypt.
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-xs sm:text-sm font-semibold space-y-1">
            <p class="font-bold">Terjadi kesalahan pada data yang dimasukkan:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Grid Layout Utama: Kolom Kiri & Kolom Kanan ala Figma --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- ==================== KOLOM KIRI ==================== --}}
        <div class="lg:col-span-4 space-y-5">

            {{-- 1. Kartu Member Player ID ala Figma --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#1a146b] via-[#211a7a] to-[#120d4f] p-6 text-white shadow-lg border border-[#30269c]">
                {{-- Decorative background glow --}}
                <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-[#acf847]/10 blur-2xl"></div>

                <div class="relative z-10 flex items-center justify-between gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#acf847] px-3 py-1 text-[11px] font-black uppercase tracking-wider text-[#1a146b] shadow-xs">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#1a146b]"></span>
                        PRO PLAYER ID
                    </span>

                    <span class="text-lg opacity-80" title="Identitas Terverifikasi LapangIn">
                        🛡️
                    </span>
                </div>

                <div class="relative z-10 mt-6 flex items-center gap-4">
                    <div class="relative flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-[#eff4ff] border-2 border-[#acf847] text-2xl font-black text-[#1a146b] shadow-md overflow-hidden">
                        {{ strtoupper(substr(Auth::user()->nama ?? Auth::user()->name ?? 'P', 0, 2)) }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <h3 class="truncate text-base font-extrabold text-white">
                            {{ Auth::user()->nama ?? Auth::user()->name }}
                        </h3>
                        <p class="font-mono text-xs font-semibold text-indigo-200 mt-0.5 tracking-wider">
                            LP - {{ substr(strtoupper(md5(Auth::user()->id ?? '12345')), 0, 5) }} - ID
                        </p>
                    </div>
                </div>

                <div class="relative z-10 mt-6 pt-4 border-t border-white/10">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-300">
                        SPORT FAVORIT
                    </p>
                    <p class="mt-1 text-xs font-bold text-[#acf847]">
                        Futsal, Badminton &amp; Mini Soccer
                    </p>
                </div>
            </div>

            {{-- 2. Sidebar Menu Navigasi Tabs --}}
            <nav class="rounded-2xl border border-[#e8edfa] bg-white p-2 shadow-xs space-y-1">
                {{-- Item 1: Informasi Profil (Aktif) --}}
                <a href="#informasi-pribadi"
                   class="flex items-center justify-between gap-3 rounded-xl bg-[#eff4ff] px-4 py-3 text-xs font-bold text-[#1a146b] shadow-2xs transition">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#dce9ff] text-base text-[#1a146b]">
                            👤
                        </span>
                        <span>Informasi Profil</span>
                    </div>
                    <span class="text-sm font-bold text-[#1a146b]">›</span>
                </a>

                {{-- Item 2: Keamanan & Kata Sandi --}}
                <a href="#keamanan-kata-sandi"
                   class="flex items-center justify-between gap-3 rounded-xl px-4 py-3 text-xs font-semibold text-[#474651] hover:bg-gray-50 hover:text-[#0b1c30] transition">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-base text-gray-600">
                            🔒
                        </span>
                        <span>Keamanan &amp; Kata Sandi</span>
                    </div>
                    <span class="text-sm font-bold text-gray-400">›</span>
                </a>

                {{-- Item 3: Preferensi Notifikasi WhatsApp --}}
                <div class="flex items-center justify-between gap-3 rounded-xl px-4 py-3 text-xs font-semibold text-[#474651] hover:bg-gray-50 transition cursor-default">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-50 text-base text-green-600">
                            💬
                        </span>
                        <span>Preferensi Notifikasi WhatsApp</span>
                    </div>
                    <span class="text-sm font-bold text-gray-400">›</span>
                </div>

                {{-- Item 4: Sewa Terhubung --}}
                <a href="{{ route('pelanggan.booking.index') }}"
                   class="flex items-center justify-between gap-3 rounded-xl px-4 py-3 text-xs font-semibold text-[#474651] hover:bg-gray-50 hover:text-[#0b1c30] transition">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-base text-indigo-600">
                            🔄
                        </span>
                        <span>Sewa Terhubung</span>
                    </div>
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#acf847] text-[10px] font-black text-[#1a146b]">
                        {{ Auth::user()->jadwals()->count() }}
                    </span>
                </a>
            </nav>

            {{-- 3. Proteksi Identitas Box ala Figma --}}
            <div class="rounded-2xl border border-[#dce9ff] bg-[#f8faff] p-4 text-[#474651]">
                <div class="flex items-start gap-3">
                    <span class="text-xl">🛡️</span>
                    <div>
                        <h4 class="text-xs font-bold text-[#1a146b]">Proteksi Identitas LapangIn</h4>
                        <p class="mt-1 text-[11px] leading-relaxed text-[#777682]">
                            Data identitas Anda tersimpan dengan perlindungan enkripsi berstandar perbankan dan tidak diperjualbelikan kepada pihak ketiga.
                        </p>
                    </div>
                </div>
            </div>

        </div>

        {{-- ==================== KOLOM KANAN ==================== --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- 1. Kartu Form Informasi Pribadi --}}
            <section id="informasi-pribadi" class="rounded-3xl border border-[#e8edfa] bg-white p-6 sm:p-8 shadow-xs">
                <div class="flex items-start gap-3 mb-6">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#eff4ff] text-xl text-[#1a146b] shadow-2xs">
                        👤
                    </span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-[#0b1c30]">
                            Informasi Pribadi
                        </h2>
                        <p class="mt-0.5 text-xs text-[#777682]">
                            Data ini digunakan untuk verifikasi fisik di lapangan dan daftar tunggu pertandingan.
                        </p>
                    </div>
                </div>

                {{-- Banner Foto Profil --}}
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-2xl bg-[#f8faff] border border-[#e5eeff] p-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-[#1a146b] text-xl font-black text-white shadow-sm border-2 border-white">
                            {{ strtoupper(substr(Auth::user()->nama ?? Auth::user()->name ?? 'P', 0, 2)) }}
                        </div>
                        <div>
                            <p class="text-xs font-bold text-[#0b1c30]">Foto Profil</p>
                            <p class="mt-0.5 text-[11px] text-[#777682] max-w-sm">
                                Format didukung JPG, PNG, atau WEBP. Maksimal 2MB beresolusi minimal 400x400 px.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" class="inline-flex items-center gap-1.5 rounded-xl bg-[#1a146b] hover:bg-[#130d52] px-3.5 py-2 text-xs font-bold text-white shadow-2xs transition">
                            <span>📤</span> Unggah Baru
                        </button>
                        <button type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-600 transition">
                            <span>🗑️</span> Hapus
                        </button>
                    </div>
                </div>

                {{-- Form Update Profil --}}
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                    @csrf
                    @method('patch')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Nama Lengkap --}}
                        <div>
                            <label for="name" class="block text-[11px] font-extrabold uppercase tracking-wider text-[#474651] mb-1.5">
                                Nama Lengkap
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 pointer-events-none text-sm">
                                    👤
                                </span>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', Auth::user()->nama ?? Auth::user()->name) }}"
                                       required
                                       class="w-full rounded-xl border border-[#dce9ff] bg-[#fdfdff] py-2.5 pl-10 pr-4 text-xs sm:text-sm font-semibold text-[#0b1c30] shadow-2xs transition focus:border-[#1a146b] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#1a146b]">
                            </div>
                            @error('name')
                                <p class="mt-1 text-[11px] text-red-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Alamat Email --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="email" class="block text-[11px] font-extrabold uppercase tracking-wider text-[#474651]">
                                    Alamat Email
                                </label>
                                <span class="inline-flex items-center gap-1 rounded-full bg-[#dcfce7] px-2 py-0.5 text-[10px] font-extrabold text-[#166534]">
                                    ✓ TERVERIFIKASI
                                </span>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 pointer-events-none text-sm">
                                    ✉️
                                </span>
                                <input type="email"
                                       id="email"
                                       name="email"
                                       value="{{ old('email', Auth::user()->email) }}"
                                       required
                                       class="w-full rounded-xl border border-[#dce9ff] bg-[#fdfdff] py-2.5 pl-10 pr-4 text-xs sm:text-sm font-semibold text-[#0b1c30] shadow-2xs transition focus:border-[#1a146b] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#1a146b]">
                            </div>
                            @error('email')
                                <p class="mt-1 text-[11px] text-red-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Nomor WhatsApp / HP --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="no_hp" class="block text-[11px] font-extrabold uppercase tracking-wider text-[#474651]">
                                Nomor WhatsApp / HP
                            </label>
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-green-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                Auto-Sync Aktif
                            </span>
                        </div>
                        <div class="relative flex rounded-xl border border-[#dce9ff] bg-[#fdfdff] shadow-2xs overflow-hidden focus-within:border-[#1a146b] focus-within:ring-1 focus-within:ring-[#1a146b]">
                            <span class="inline-flex items-center border-r border-[#dce9ff] bg-[#eff4ff] px-3.5 text-xs font-bold text-[#1a146b]">
                                +62
                            </span>
                            @php
                                $rawHp = Auth::user()->no_hp ?? '';
                                $cleanHp = preg_replace('/^(\+62|62|0)/', '', $rawHp);
                            @endphp
                            <input type="text"
                                   id="no_hp"
                                   name="no_hp"
                                   value="{{ old('no_hp', $cleanHp) }}"
                                   placeholder="8123456789"
                                   class="w-full border-0 bg-transparent py-2.5 px-3.5 text-xs sm:text-sm font-semibold text-[#0b1c30] focus:outline-none">
                        </div>
                        <p class="mt-1.5 text-[11px] text-[#777682] flex items-center gap-1">
                            <span>ⓘ</span> Nomor ini digunakan untuk verifikasi e-tiket di meja turnstile gelanggang.
                        </p>
                        @error('no_hp')
                            <p class="mt-1 text-[11px] text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-[#1a146b] hover:bg-[#130d52] px-6 py-3 text-xs sm:text-sm font-extrabold text-white shadow-md transition cursor-pointer">
                            <span>💾</span> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </section>

            {{-- 2. Kartu Form Ubah Kata Sandi ala Figma --}}
            <section id="keamanan-kata-sandi" class="rounded-3xl border border-[#e8edfa] bg-white p-6 sm:p-8 shadow-xs">
                <div class="flex items-center justify-between gap-3 mb-6">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#eff4ff] text-xl text-[#1a146b] shadow-2xs">
                            🔒
                        </span>
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-[#0b1c30]">
                                Ubah Kata Sandi
                            </h2>
                            <p class="mt-0.5 text-xs text-[#777682]">
                                Pastikan keamanan akun Anda selalu diperbarui untuk mencegah akses tanpa izin.
                            </p>
                        </div>
                    </div>

                    <span class="text-xl text-gray-300">🔐</span>
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                    @csrf
                    @method('put')

                    {{-- Kata Sandi Saat Ini --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="current_password" class="block text-[11px] font-extrabold uppercase tracking-wider text-[#474651]">
                                Kata Sandi Saat Ini (Old Password)
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-[11px] font-bold text-[#1a146b] hover:underline">
                                    Lupa Kata Sandi?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 pointer-events-none text-sm">
                                🔑
                            </span>
                            <input type="password"
                                   id="current_password"
                                   name="current_password"
                                   placeholder="Masukkan kata sandi lama Anda"
                                   autocomplete="current-password"
                                   required
                                   class="w-full rounded-xl border border-[#dce9ff] bg-[#fdfdff] py-2.5 pl-10 pr-10 text-xs sm:text-sm font-semibold text-[#0b1c30] shadow-2xs transition focus:border-[#1a146b] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#1a146b]">
                        </div>
                        @error('current_password', 'updatePassword')
                            <p class="mt-1 text-[11px] text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Grid 2 Kolom: Kata Sandi Baru & Konfirmasi --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-[11px] font-extrabold uppercase tracking-wider text-[#474651] mb-1.5">
                                Kata Sandi Baru
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 pointer-events-none text-sm">
                                    🔒
                                </span>
                                <input type="password"
                                       id="password"
                                       name="password"
                                       placeholder="Minimal 8 karakter baru"
                                       autocomplete="new-password"
                                       required
                                       class="w-full rounded-xl border border-[#dce9ff] bg-[#fdfdff] py-2.5 pl-10 pr-10 text-xs sm:text-sm font-semibold text-[#0b1c30] shadow-2xs transition focus:border-[#1a146b] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#1a146b]">
                            </div>
                            @error('password', 'updatePassword')
                                <p class="mt-1 text-[11px] text-red-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-[11px] font-extrabold uppercase tracking-wider text-[#474651] mb-1.5">
                                Konfirmasi Kata Sandi Baru
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 pointer-events-none text-sm">
                                    🔐
                                </span>
                                <input type="password"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       placeholder="Ketik ulang kata sandi baru"
                                       autocomplete="new-password"
                                       required
                                       class="w-full rounded-xl border border-[#dce9ff] bg-[#fdfdff] py-2.5 pl-10 pr-10 text-xs sm:text-sm font-semibold text-[#0b1c30] shadow-2xs transition focus:border-[#1a146b] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#1a146b]">
                            </div>
                            @error('password_confirmation', 'updatePassword')
                                <p class="mt-1 text-[11px] text-red-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Box Kekuatan Sandi ala Figma --}}
                    <div class="rounded-2xl border border-[#dce9ff] bg-[#f4f7ff] p-4 text-[#474651]">
                        <div class="flex items-center justify-between text-xs font-bold mb-2">
                            <span class="flex items-center gap-1.5 text-[#1a146b]">
                                🛡️ Kekuatan Sandi
                            </span>
                            <span class="text-[#416900] font-black">
                                Kuat
                            </span>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="h-2 w-full rounded-full bg-gray-200 overflow-hidden mb-3">
                            <div class="h-full rounded-full bg-gradient-to-r from-[#acf847] to-[#22c55e]" style="width: 100%;"></div>
                        </div>

                        {{-- Checklist Kriteria --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                            <div class="flex items-center gap-1.5 text-[#166534]">
                                <span>✓</span>
                                <span>Minimal 8 karakter</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-[#166534]">
                                <span>✓</span>
                                <span>Kombinasi huruf &amp; angka</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-[#166534]">
                                <span>✓</span>
                                <span>Disimpan menggunakan Hashing Bcrypt</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-[#166534]">
                                <span>✓</span>
                                <span>Sesi aktif di perangkat lain akan direset</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-[#1a146b] hover:bg-[#130d52] px-6 py-3 text-xs sm:text-sm font-extrabold text-white shadow-md transition cursor-pointer">
                            <span>🔄</span> Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </section>

        </div>

    </div>

</div>
@endsection
