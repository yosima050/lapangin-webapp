@extends('layouts.pelanggan')

@section('title', 'Dashboard Pelanggan')

@section('content')
    <div class="space-y-8">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-indigo-700">Area Pelanggan</p>
            <h1 class="mt-2 text-3xl font-bold text-gray-900">Selamat datang, {{ Auth::user()->nama }}!</h1>
            <p class="mt-2 text-gray-600">Akun pelanggan Anda sudah siap digunakan.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-gray-500">Status akun</p>
                <p class="mt-2 text-xl font-bold text-green-700">Aktif</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-gray-500">Peran</p>
                <p class="mt-2 text-xl font-bold capitalize text-gray-900">{{ Auth::user()->role }}</p>
            </div>
        </div>
    </div>
@endsection
