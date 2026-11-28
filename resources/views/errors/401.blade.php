@include('errors.layout', [
    'code' => '401',
    'title' => 'Perlu Masuk Akun',
    'heading' => 'Silakan masuk akun terlebih dahulu',
    'message' => 'Halaman ini hanya bisa diakses setelah kamu masuk ke akun LapangIn. Yuk login dulu biar bisa lanjut.',
    'ctaLabel' => 'Masuk ke Akun',
    'redirectUrl' => route('login'),
    'autoRedirect' => true,
    'countdownSeconds' => 5,
    'secondaryUrl' => route('home'),
    'secondaryLabel' => 'Ke Beranda',
])
