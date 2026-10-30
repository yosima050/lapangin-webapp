<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Tampilkan Halaman Kontak & Informasi Venue.
     */
    public function kontak(): View
    {
        return view('pages.kontak');
    }

    /**
     * Tampilkan Halaman Pusat Bantuan & FAQ.
     */
    public function bantuan(): View
    {
        return view('pages.bantuan');
    }

    /**
     * Tampilkan Halaman Kebijakan Reschedule & Denda Pembatalan.
     */
    public function kebijakanReschedule(): View
    {
        return view('pages.kebijakan-reschedule');
    }

    /**
     * Tampilkan Halaman Syarat & Ketentuan Layanan.
     */
    public function syaratKetentuan(): View
    {
        return view('pages.syarat-ketentuan');
    }

    /**
     * Tampilkan Halaman Kebijakan Privasi Pengguna.
     */
    public function kebijakanPrivasi(): View
    {
        return view('pages.kebijakan-privasi');
    }
}
