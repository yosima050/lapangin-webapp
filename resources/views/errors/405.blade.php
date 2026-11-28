@include('errors.layout', [
    'code' => '405',
    'title' => 'Metode Tidak Diizinkan',
    'heading' => 'Alamat ini tidak bisa dibuka langsung',
    'message' => 'Halaman ini hanya bisa diakses lewat tombol atau formulir resmi di dalam LapangIn, bukan diketik langsung di alamat browser.',
    'ctaLabel' => 'Kembali ke halaman utama',
    'redirectUrl' => route('home'),
    'autoRedirect' => true,
    'countdownSeconds' => 5,
])
