@include('errors.layout', [
    'code' => '403',
    'title' => 'Akses Ditolak',
    'heading' => 'Maaf, kamu tidak punya akses ke halaman ini',
    'message' => 'Halaman ini khusus untuk peran atau akun tertentu. Tenang, kamu bisa kembali ke beranda untuk mencari dan memesan lapangan.',
    'ctaLabel' => 'Kembali ke halaman utama',
    'redirectUrl' => route('home'),
    'autoRedirect' => true,
    'countdownSeconds' => 5,
])
