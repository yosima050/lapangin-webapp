@extends('layouts.app')

@section('title', 'Pengaturan Akun & Profil - ' . config('app.name', 'LapangIn'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6" style="font-family: 'Plus Jakarta Sans', sans-serif;">

    {{-- Breadcrumb & Judul --}}
    <div class="flex flex-col gap-2">
        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#777682]">
            <a href="{{ route('home') }}" class="hover:text-[#1a146b] transition">BERANDA</a>
            <span>›</span>
            <span class="text-[#1a146b]">PENGATURAN AKUN</span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#0b1c30]">
            Pengaturan Akun &amp; Keamanan
        </h1>

        <p class="text-xs sm:text-sm text-[#474651] max-w-2xl leading-relaxed">
            Perbarui informasi akun, email login, kata sandi, dan kelola keamanan akun LapangIn Anda.
        </p>
    </div>

    {{-- Alert Notifikasi Status --}}
    @if (session('status') === 'profile-updated')
        <div class="flex items-center gap-3 p-4 rounded-2xl bg-[#dcfce7] border border-[#bbf7d0] text-[#166534] text-xs sm:text-sm font-bold shadow-xs">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#22c55e] text-white">
                <svg class="w-3.5 h-3.5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </span>
            Profil Anda berhasil diperbarui.
        </div>
    @elseif (session('status') === 'password-updated')
        <div class="flex items-center gap-3 p-4 rounded-2xl bg-[#dcfce7] border border-[#bbf7d0] text-[#166534] text-xs sm:text-sm font-bold shadow-xs">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#22c55e] text-white">
                <svg class="w-3.5 h-3.5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </span>
            Kata sandi Anda berhasil diperbarui.
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6">
        <div class="p-5 sm:p-8 bg-white shadow-xs rounded-3xl border border-[#e8edfa]">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="p-5 sm:p-8 bg-white shadow-xs rounded-3xl border border-[#e8edfa]">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="p-5 sm:p-8 bg-white shadow-xs rounded-3xl border border-[#e8edfa]">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection
