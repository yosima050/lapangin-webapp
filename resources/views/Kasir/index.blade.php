@extends('layouts.kasir')

@section('title', 'Dashboard Kasir')
@section('page-title', 'Dashboard Kasir')

@section('content')
    <div class="space-y-6">

        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Selamat Datang, Kasir!
            </h2>

            <p class="text-gray-500 mt-1">
                Kelola transaksi dan booking Lapangin.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <p class="text-sm text-gray-500">
                    Booking Hari Ini
                </p>

                <p class="text-3xl font-bold mt-2">
                    12
                </p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <p class="text-sm text-gray-500">
                    Menunggu Check-in
                </p>

                <p class="text-3xl font-bold mt-2">
                    5
                </p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <p class="text-sm text-gray-500">
                    Transaksi Hari Ini
                </p>

                <p class="text-3xl font-bold mt-2">
                    18
                </p>
            </div>

        </div>

    </div>
@endsection