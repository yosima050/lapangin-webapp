<footer class="mt-auto border-t border-gray-200 bg-white pt-12 pb-8 text-xs text-gray-600">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-12">

            {{-- Kolom 1: Branding & Deskripsi --}}
            <div class="lg:col-span-4">
                <div class="flex items-center gap-2">
                    <x-application-logo />
                </div>
                <p class="mt-4 max-w-xs text-xs leading-relaxed text-gray-500">
                    Platform pemesanan dan jadwal lapangan olahraga modern berstandar turnamen. Pesan lapangan padel, badminton, futsal, dan basket tanpa repot antre.
                </p>
            </div>

            {{-- Kolom 2: Navigasi --}}
            <div class="lg:col-span-2">
                <h4 class="font-extrabold uppercase tracking-wider text-gray-900">Navigasi</h4>
                <ul class="mt-3 space-y-2 text-gray-500">
                    <li><a href="{{ route('katalog.index') }}" class="hover:text-[#1a146b] transition">Katalog Lapangan</a></li>
                    <li><a href="{{ route('jadwal.index') }}" class="hover:text-[#1a146b] transition">Cek Jadwal</a></li>
                    @auth
                        @if (!in_array(Auth::user()->role, ['admin', 'kasir']))
                            <li><a href="{{ route('pelanggan.booking.index') }}" class="hover:text-[#1a146b] transition">Riwayat Booking</a></li>
                            <li><a href="{{ route('pelanggan.dashboard') }}" class="hover:text-[#1a146b] transition">Profil &amp; Akun</a></li>
                        @endif
                    @endauth
                </ul>
            </div>

            {{-- Kolom 3: Bantuan & Kebijakan --}}
            <div class="lg:col-span-3">
                <h4 class="font-extrabold uppercase tracking-wider text-gray-900">Bantuan &amp; Kebijakan</h4>
                <ul class="mt-3 space-y-2 text-gray-500">
                    <li><a href="{{ route('home') }}#kontak" class="hover:text-[#1a146b] transition">Pusat Bantuan / FAQ</a></li>
                    <li><a href="{{ route('home') }}#syarat" class="hover:text-[#1a146b] transition">Kebijakan Reschedule</a></li>
                    <li><a href="{{ route('home') }}#syarat" class="hover:text-[#1a146b] transition">Ketentuan Layanan</a></li>
                    <li><a href="{{ route('home') }}#syarat" class="hover:text-[#1a146b] transition">Kebijakan Privasi</a></li>
                </ul>
            </div>

            {{-- Kolom 4: Kerjasama Venue --}}
            <div class="lg:col-span-3">
                <h4 class="font-extrabold uppercase tracking-wider text-gray-900">Kerjasama Venue</h4>
                <p class="mt-3 leading-relaxed text-gray-500">
                    Punya gelanggang atau lapangan olahraga? Kelola jadwal dan tiket reservasi otomatis dengan ekosistem LapangIn Partner.
                </p>
                <a href="{{ route('home') }}#kontak" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#acf847] px-4 py-2.5 font-bold text-[#1a146b] shadow-xs hover:bg-[#9be338] transition">
                    Masuk Portal Venue Owner &rarr;
                </a>
            </div>

        </div>

        {{-- Hak Cipta --}}
        <div class="mt-12 border-t border-gray-100 pt-6 text-gray-400">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'LapangIn') }} Digital Systems. Seluruh hak cipta dilindungi undang-undang.</p>
        </div>
    </div>
</footer>
