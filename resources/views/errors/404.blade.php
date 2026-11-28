@include('errors.layout', [
    'code' => '404',
    'title' => 'Halaman Tidak Ditemukan',
    'heading' => 'Ups, halaman yang kamu cari tidak ditemukan',
    'message' => 'Sepertinya alamat ini sudah tidak ada atau salah ketik. Tapi jangan khawatir, masih banyak lapangan seru yang siap kamu pesan.',
    'ctaLabel' => 'Kembali ke halaman utama',
    'redirectUrl' => route('home'),
    'autoRedirect' => true,
    'countdownSeconds' => 5,
])
