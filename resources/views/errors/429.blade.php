@include('errors.layout', [
    'code' => '429',
    'title' => 'Terlalu Banyak Permintaan',
    'heading' => 'Pelan-pelan dulu, terlalu banyak permintaan',
    'message' => 'Kamu mengirim terlalu banyak permintaan dalam waktu singkat. Tunggu beberapa detik sebelum mencoba lagi ya.',
    'ctaLabel' => 'Kembali ke halaman utama',
    'redirectUrl' => route('home'),
    'autoRedirect' => true,
    'countdownSeconds' => 8,
])
