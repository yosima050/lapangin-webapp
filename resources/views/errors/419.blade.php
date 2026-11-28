@include('errors.layout', [
    'code' => '419',
    'title' => 'Sesi Kedaluwarsa',
    'heading' => 'Sesi halaman sudah kedaluwarsa',
    'message' => 'Halaman terlalu lama dibiarkan terbuka sehingga token keamanannya habis. Muat ulang halaman atau kembali ke beranda untuk melanjutkan.',
    'ctaLabel' => 'Kembali ke halaman utama',
    'redirectUrl' => route('home'),
    'autoRedirect' => true,
    'countdownSeconds' => 5,
])
