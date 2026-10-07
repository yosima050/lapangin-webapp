@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Admin')

@section('content')
    <div class="space-y-6">

        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Selamat Datang, Admin!
            </h2>
            <p class="text-gray-500 mt-1">
                Kelola sistem Lapangin melalui dashboard admin.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Total Lapangan</p>
                <p class="text-3xl font-bold mt-2">8</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Booking Hari Ini</p>
                <p class="text-3xl font-bold mt-2">12</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Total Pegawai</p>
                <p class="text-3xl font-bold mt-2">5</p>
            </div>

        </div>

    </div>
@endsection