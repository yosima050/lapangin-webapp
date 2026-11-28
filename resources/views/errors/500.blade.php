<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gangguan Server (500) &bull; LapangIn</title>
    <link rel="icon" type="image/png" href="/images/02_Icon_App.png">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-[#0b1c30] min-h-screen flex flex-col font-sans antialiased">
    <header class="border-b border-gray-100 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center">
            <a href="/" class="inline-flex items-center hover:opacity-90 transition" aria-label="LapangIn">
                <x-application-logo />
            </a>
        </div>
    </header>

    <main class="flex-1 flex items-center py-12 sm:py-20">
        <div class="max-w-4xl mx-auto px-5 sm:px-6 lg:px-8 w-full">
            <div class="flex flex-col md:flex-row items-center justify-center gap-8 md:gap-14">
                <div class="w-56 sm:w-64 md:w-72 shrink-0">
                    <svg viewBox="0 0 320 260" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto" aria-hidden="true">
                        <ellipse cx="165" cy="128" rx="118" ry="96" fill="#F4F7FF" />
                        <path d="M44 196C44 185 78 164 162 164C246 164 282 185 282 196C282 214 236 232 162 232C88 232 44 214 44 196Z" fill="#E9F7D4" />
                        <path d="M72 194C72 186 104 174 162 174C220 174 252 186 252 194C252 204 220 216 162 216C104 216 72 204 72 194Z" stroke="#9BD63B" stroke-width="2.5" stroke-dasharray="6 6" />
                        <rect x="114" y="88" width="9" height="106" rx="4.5" fill="#1A146B" />
                        <rect x="64" y="96" width="98" height="38" rx="10" fill="#1A146B" />
                        <text x="113" y="121" text-anchor="middle" fill="#ACF847" font-family="system-ui, sans-serif" font-size="20" font-weight="900" letter-spacing="2">500</text>
                        <path d="M123 142H172L182 153L172 164H123V142Z" fill="#ACF847" stroke="#1A146B" stroke-width="2.5" stroke-linejoin="round" />
                        <circle cx="222" cy="196" r="18" fill="white" stroke="#1A146B" stroke-width="3" />
                        <path d="M222 186L229 191L226 199H218L215 191L222 186Z" fill="#1A146B" />
                    </svg>
                </div>
                <div class="text-center md:text-left max-w-md space-y-3">
                    <h1 class="text-2xl sm:text-3xl font-headline font-black text-[#0b1c30] tracking-tight leading-snug">
                        Ups, terjadi gangguan sementara di server kami
                    </h1>
                    <p class="text-sm sm:text-base text-gray-600 leading-relaxed">
                        Tim kami sedang menangani kendala ini. Silakan muat ulang beberapa saat lagi atau kembali ke halaman utama.
                    </p>
                    <p class="text-xs font-semibold text-gray-500 pt-0.5">Kode error: 500</p>
                    <div class="pt-3 flex flex-wrap items-center justify-center md:justify-start gap-2.5">
                        <a href="/"
                           class="inline-flex items-center justify-center gap-2 rounded-full bg-[#1a146b] hover:bg-[#130f52] px-6 py-3 text-sm font-extrabold text-white shadow-md ring-2 ring-[#acf847]/70 ring-offset-2 transition">
                            Kembali ke halaman utama
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
