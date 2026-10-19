<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\View\View;

class TransaksiController extends Controller
{
    public function index(): View
    {
        $transaksis = Transaksi::with([
            'jadwal.pelanggan',
            'jadwal.lapangan',
        ])->paginate(10);

        return view('admin.transaksi.index', compact('transaksis'));
    }
}