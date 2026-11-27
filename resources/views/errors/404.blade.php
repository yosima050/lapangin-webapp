@extends('layouts.app')

@section('title', 'Halaman Tidak Ditemukan (404) - LapangIn')

@section('content')
<section class="min-h-[calc(100vh-14rem)] bg-white flex items-center py-12 sm:py-20"
         x-data="{
            seconds: 5,
            timer: null,
            init() {
                this.timer = setInterval(() => {
                    if (this.seconds > 1) {
                        this.seconds--;
                    } else {
                        clearInterval(this.timer);
                        window.location.href = '{{ route('home') }}';
                    }
                }, 1000);
            }
         }">
    <div class="max-w-4xl mx-auto px-5 sm:px-6 lg:px-8 w-full">
        <div class="flex flex-col md:flex-row items-center justify-center gap-8 md:gap-14">

            {{-- Ilustrasi Custom Lapangan Olahraga 404 --}}
            <div class="w-56 sm:w-64 md:w-72 shrink-0">
                <svg viewBox="0 0 320 260" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto" aria-hidden="true">
                    {{-- Latar Langit & Awan Halus --}}
                    <ellipse cx="165" cy="128" rx="118" ry="96" fill="#F4F7FF" />
                    <path d="M88 74C88 67.4 93.4 62 100 62H122C127.5 62 132 66.5 132 72C132 72 132 74 132 74H88Z" fill="#E4ECFF" />
                    <path d="M210 58C210 52.5 214.5 48 220 48H244C249.5 48 254 52.5 254 58H210Z" fill="#E4ECFF" />

                    {{-- Permukaan Lapangan Perspektif --}}
                    <path d="M44 196C44 185 78 164 162 164C246 164 282 185 282 196C282 214 236 232 162 232C88 232 44 214 44 196Z" fill="#E9F7D4" />
                    <path d="M72 194C72 186 104 174 162 174C220 174 252 186 252 194C252 204 220 216 162 216C104 216 72 204 72 194Z" stroke="#9BD63B" stroke-width="2.5" stroke-dasharray="6 6" />

                    {{-- Gawang / Net Lapangan di Latar Belakang --}}
                    <path d="M184 124H250V174H184V124Z" fill="white" fill-opacity="0.7" stroke="#1A146B" stroke-width="3" stroke-linejoin="round" />
                    <path d="M198 124V174M212 124V174M226 124V174M240 124V174M184 138H250M184 152H250M184 164H250" stroke="#CBD5E1" stroke-width="1.5" />

                    {{-- Papan Penunjuk Arah "404" --}}
                    <rect x="114" y="88" width="9" height="106" rx="4.5" fill="#1A146B" />
                    <rect x="64" y="96" width="98" height="38" rx="10" fill="#1A146B" />
                    <text x="113" y="121" text-anchor="middle" fill="#ACF847" font-family="system-ui, sans-serif" font-size="20" font-weight="900" letter-spacing="2">404</text>
                    <path d="M123 142H172L182 153L172 164H123V142Z" fill="#ACF847" stroke="#1A146B" stroke-width="2.5" stroke-linejoin="round" />
                    <path d="M135 153H164" stroke="#1A146B" stroke-width="2.5" stroke-linecap="round" />

                    {{-- Bola Olahraga Nyasar di Samping --}}
                    <circle cx="222" cy="196" r="18" fill="white" stroke="#1A146B" stroke-width="3" />
                    <path d="M222 186L229 191L226 199H218L215 191L222 186Z" fill="#1A146B" />
                    <path d="M222 178V186M229 191L239 189M226 199L232 211M218 199L212 211M215 191L205 189" stroke="#1A146B" stroke-width="2" stroke-linecap="round" />

                    {{-- Bintang / Aksen Kecil --}}
                    <circle cx="72" cy="158" r="5" fill="#ACF847" />
                    <circle cx="264" cy="108" r="4" fill="#1A146B" fill-opacity="0.25" />
                </svg>
            </div>

            {{-- Konten Teks & Tombol Countdown (Gaya Bersih Traveloka) --}}
            <div class="text-center md:text-left max-w-md space-y-3">
                <h1 class="text-2xl sm:text-3xl font-headline font-black text-[#0b1c30] tracking-tight leading-snug">
                    Ups, halaman yang kamu cari tidak ditemukan
                </h1>
                <p class="text-sm sm:text-base text-gray-600 leading-relaxed">
                    Sepertinya alamat ini sudah tidak ada atau salah ketik. Tapi jangan khawatir, masih banyak lapangan seru yang siap kamu pesan.
                </p>
                <p class="text-xs font-semibold text-gray-400 pt-0.5">
                    Kode error: 404
                </p>

                <div class="pt-3 flex flex-col sm:flex-row items-center md:items-start gap-2.5">
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-full bg-[#1a146b] hover:bg-[#130f52] active:scale-[0.98] px-6 py-3 text-sm font-extrabold text-white shadow-md shadow-[#1a146b]/20 ring-2 ring-[#acf847]/70 ring-offset-2 transition">
                        <span>Kembali ke halaman utama</span>
                        <span class="text-[#acf847] tabular-nums" x-text="seconds + 's'">5s</span>
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection
