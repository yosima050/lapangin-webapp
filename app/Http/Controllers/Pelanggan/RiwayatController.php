<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $pelanggan = $request->user();

        $jadwals = $pelanggan->jadwals()
            ->with('lapangan')
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_mulai')
            ->paginate(10);

        return view('pelanggan.riwayat', compact('jadwals'));
    }
}
