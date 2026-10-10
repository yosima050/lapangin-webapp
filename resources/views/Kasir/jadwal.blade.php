@extends('layouts.kasir')

@section('title', 'Jadwal Lapangan & Booking Walk-in - LapangIn')

@section('content')
<div class="w-full px-8 py-8 flex flex-col gap-6" x-data="{
    filterCourt: '{{ $selectedCourtId }}',
    customerName: '',
    whatsapp: '',
    selectedCourtId: '{{ $lapangans->first()?->id }}',
    selectedCourtName: '{{ $lapangans->first()?->nama }}',
    startTime: '{{ $availableSlots[0] ?? '14:00' }}',
    duration: 2,
    ratePerHour: {{ (int) ($lapangans->first()?->hargaMasters->whereNull('tanggal_khusus')->where('jenis_hari', 'weekday')->first()?->harga ?? 120000) }},
    payType: 'full',
    printReceipt: true,
    get totalFee() {
        return this.ratePerHour * this.duration;
    },
    get amountToPay() {
        return this.payType === 'dp' ? this.totalFee * 0.5 : this.totalFee;
    },
    formatRupiah(val) {
        return new Intl.NumberFormat('id-ID').format(val);
    },
    selectCourt(courtId, courtName, rate) {
        this.selectedCourtId = courtId;
        this.selectedCourtName = courtName;
        this.ratePerHour = rate;
    },
    selectSlot(courtId, courtName, time, rate) {
        this.selectedCourtId = courtId;
        this.selectedCourtName = courtName;
        this.startTime = time;
        this.ratePerHour = rate;
        document.getElementById('walkin-form-card').scrollIntoView({ behavior: 'smooth' });
    }
}">
    <!-- Flash Notifications -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-[#acf847]/30 border border-[#acf847] flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-2xl text-[#416900]">check_circle</span>
                <span class="text-xs font-bold text-[#102000]">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-[#416900] hover:text-[#102000]">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
    @endif

    <!-- Subheader Strip -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-[#acf847] text-[#102000] text-[11px] font-extrabold uppercase tracking-wider">
                    POS OPERASIONAL MEJA DEPAN
                </span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-headline font-extrabold text-[#0b1c30] tracking-tight">
                Jadwal Lapangan &amp; Booking Walk-in
            </h1>
            <p class="text-sm text-[#474651] mt-1">
                Pantau ketersediaan slot per lapangan hari ini dan catat penyewa yang datang langsung ke meja kasir.
            </p>
        </div>

        <!-- Date Selector & Quick Action Button -->
        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" action="{{ route('kasir.jadwal') }}" class="flex items-center bg-[#eff4ff] px-2 py-1.5 rounded-2xl border border-[#dce9ff] gap-1 shadow-sm">
                <input type="hidden" name="lapangan_id" value="{{ $selectedCourtId }}">
                <div class="flex items-center gap-2 px-3 py-1 bg-white rounded-xl shadow-xs">
                    <span class="material-symbols-outlined text-[#1a146b] text-base">calendar_today</span>
                    <input type="date" name="tanggal" value="{{ $selectedDate }}" onchange="this.form.submit()"
                           class="text-xs font-bold text-[#0b1c30] border-none bg-transparent p-0 focus:ring-0">
                    @if ($selectedDate === now()->toDateString())
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-[#acf847] text-[#102000]">HARI INI</span>
                    @endif
                </div>
            </form>

            <button type="button" @click="document.getElementById('input-pemesan').focus(); document.getElementById('walkin-form-card').scrollIntoView({ behavior: 'smooth' });"
                    class="flex items-center gap-2 px-5 py-3 rounded-2xl bg-[#acf847] text-[#102000] hover:bg-[#91db2a] font-headline font-extrabold text-xs shadow-md transition-all active:scale-95 cursor-pointer">
                <span class="material-symbols-outlined text-lg font-bold">add_circle</span>
                <span>+ Buat Booking Walk-in Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter Bar & Occupancy Metric (Loaded from database) -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-[#e5eeff] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
            <a href="{{ route('kasir.jadwal', ['tanggal' => $selectedDate, 'lapangan_id' => 'all']) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $selectedCourtId === 'all' ? 'bg-[#1a146b] text-white shadow-sm' : 'bg-[#eff4ff] text-[#474651] hover:bg-[#e5eeff]' }}">
                Semua Lapangan ({{ $lapangans->count() }})
            </a>
            @foreach ($lapangans as $lap)
                <a href="{{ route('kasir.jadwal', ['tanggal' => $selectedDate, 'lapangan_id' => $lap->id]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all {{ $selectedCourtId == $lap->id ? 'bg-[#1a146b] text-white shadow-sm' : 'bg-[#eff4ff] text-[#474651] hover:bg-[#e5eeff]' }}">
                    {{ $lap->nama }}
                </a>
            @endforeach
        </div>

        <div class="flex items-center gap-3 px-4 py-2 bg-[#eff4ff] rounded-xl border border-[#dce9ff] shrink-0">
            <div class="flex flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#777682]">OKUPANSI HARI INI</span>
                <span class="text-xs font-extrabold text-[#1a146b]">{{ $occupancyRate }}% Terisi ({{ $occupancyCount }} Slot)</span>
            </div>
            <div class="w-16 h-2 bg-[#dce9ff] rounded-full overflow-hidden">
                <div class="bg-[#acf847] h-full" style="width: {{ min(100, $occupancyRate) }}%;"></div>
            </div>
        </div>
    </div>

    <!-- Legend Bar -->
    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 px-4 py-3 bg-[#eff4ff] rounded-xl text-xs text-[#474651] border border-[#dce9ff]">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-[#acf847] shadow-[0_0_8px_#acf847]"></span>
            <span class="font-medium text-[#0b1c30]">Slot Kosong / Tersedia</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-[#1a146b]"></span>
            <span class="font-medium text-[#0b1c30]">Lunas / Siap Main</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-[#f59e0b]"></span>
            <span class="font-medium text-[#0b1c30]">Booking DP (Perlu Pelunasan)</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-[#777682]"></span>
            <span class="font-medium text-[#777682]">Selesai Main</span>
        </div>
    </div>

    <!-- Main Workspace Split: Left Schedule Timeline (7 cols) / Right Walk-in Form (5 cols) -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

        <!-- LEFT: Real Schedule Timeline from Database (7 cols) -->
        <div class="xl:col-span-7 flex flex-col gap-4">

            @forelse ($jadwals as $jadwal)
                @php
                    $isLunas = $jadwal->status_pembayaran === 'lunas_online' || $jadwal->status_pembayaran === 'lunas_kasir';
                    $isDp = $jadwal->status_pembayaran === 'dp_terverifikasi';
                    $dpPaid = (float) $jadwal->transaksis->where('status', 'berhasil')->sum('jumlah');
                    $sisaTagihan = max(0, (float)$jadwal->harga_disepakati - $dpPaid);
                    $namaPenyewa = $jadwal->pelanggan->nama ?? $jadwal->nama_tamu ?? 'Penyewa Terdaftar';
                @endphp
                <div class="bg-white p-5 rounded-2xl shadow-sm border {{ $isDp ? 'border-2 border-[#f59e0b]/40' : 'border-[#e5eeff]' }} flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col items-center justify-center w-24 py-2 px-1 bg-[#eff4ff] rounded-xl shrink-0">
                            <span class="text-lg font-headline font-extrabold text-[#0b1c30] tracking-tight">
                                {{ substr($jadwal->jam_mulai, 0, 5) }}
                            </span>
                            <span class="text-[10px] font-bold text-[#777682] uppercase">
                                s/d {{ substr($jadwal->jam_selesai, 0, 5) }}
                            </span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                @if ($isLunas)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#1a146b] text-white">
                                        {{ $jadwal->status_pembayaran === 'lunas_kasir' ? 'LUNAS KASIR' : 'LUNAS ONLINE' }}
                                    </span>
                                @elseif ($isDp)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#f59e0b] text-white">
                                        BOOKING DP 50%
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#dce9ff] text-[#1a146b]">
                                        DRAFT PESANAN
                                    </span>
                                @endif
                                <span class="text-xs text-[#777682]">{{ $jadwal->lapangan->nama ?? 'Lapangan' }}</span>
                            </div>
                            <h3 class="text-sm font-bold text-[#0b1c30]">{{ $namaPenyewa }}</h3>
                            <p class="text-xs text-[#474651]">
                                {{ $jadwal->pelanggan->no_hp ?? $jadwal->no_wa_tamu ?: 'Kontak: -' }}
                            </p>
                            @if ($sisaTagihan > 0)
                                <p class="text-xs font-bold text-[#ba1a1a]">Sisa Tagihan: Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</p>
                            @endif
                        </div>
                    </div>

                    @if ($sisaTagihan > 0)
                        <a href="{{ route('kasir.index', ['code' => $jadwal->kode_qr ?? $jadwal->id]) }}"
                           class="px-4 py-2.5 rounded-xl bg-[#f59e0b] hover:bg-[#d97706] text-white text-xs font-extrabold transition-all shadow-sm shrink-0 flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-base">payments</span>
                            <span>Proses Pelunasan Kasir</span>
                        </a>
                    @else
                        <a href="{{ route('kasir.index', ['code' => $jadwal->kode_qr ?? $jadwal->id]) }}"
                           class="px-4 py-2.5 rounded-xl bg-[#eff4ff] hover:bg-[#dce9ff] text-[#0b1c30] text-xs font-bold transition-colors shrink-0 flex items-center justify-center gap-1.5 border border-[#dce9ff]">
                            <span class="material-symbols-outlined text-base">receipt</span>
                            <span>Lihat Detail Tiket</span>
                        </a>
                    @endif
                </div>
            @empty
                <div class="bg-white p-8 rounded-2xl border border-[#e5eeff] text-center text-[#777682]">
                    <span class="material-symbols-outlined text-4xl text-[#acf847] mb-2">event_available</span>
                    <p class="font-bold text-[#0b1c30]">Belum ada jadwal booking pada tanggal ini.</p>
                    <p class="text-xs text-[#474651] mt-1">Gunakan formulir di samping untuk membuat booking walk-in baru langsung di meja kasir.</p>
                </div>
            @endforelse

            <!-- Dynamic Available Slots Pickers from Database -->
            @if (!empty($availableSlots))
                <div class="mt-2 flex flex-col gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#777682]">SLOT KOSONG TERSEDIA HARI INI:</span>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach ($availableSlots as $slotTime)
                            @php
                                $firstCourt = $lapangans->first();
                                $firstRate = $firstCourt?->hargaMasters->whereNull('tanggal_khusus')->where('jenis_hari', 'weekday')->first()?->harga ?? 120000;
                            @endphp
                            <button type="button" @click="selectSlot('{{ $firstCourt?->id }}', '{{ $firstCourt?->nama }}', '{{ $slotTime }}', {{ (int)$firstRate }})"
                                    class="p-3 rounded-xl bg-white hover:bg-[#acf847]/20 border border-[#e5eeff] hover:border-[#acf847] text-left transition-all group cursor-pointer shadow-xs">
                                <span class="text-[10px] font-bold text-[#416900] block">TERSEDIA</span>
                                <span class="text-sm font-headline font-black text-[#0b1c30] group-hover:text-[#1a146b]">{{ $slotTime }}</span>
                                <span class="text-[10px] text-[#777682] block mt-0.5">Pilih Slot →</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <!-- RIGHT: Real Form Booking Tamu Walk-in (5 cols) -->
        <div class="xl:col-span-5" id="walkin-form-card">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5eeff] flex flex-col gap-5 sticky top-20">
                <div class="flex items-start gap-3 pb-3 border-b border-[#eff4ff]">
                    <div class="w-10 h-10 rounded-xl bg-[#eff4ff] flex items-center justify-center text-[#1a146b] shrink-0">
                        <span class="material-symbols-outlined text-2xl">receipt_long</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-[#0b1c30] leading-tight">Form Booking Tamu Walk-in</h2>
                        <p class="text-xs text-[#777682]">Input Langsung dari Meja Resepsionis ke PostgreSQL</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('kasir.walkin.store') }}" class="flex flex-col gap-4">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ $selectedDate }}">
                    <input type="hidden" name="durasi" :value="duration">
                    <input type="hidden" name="payType" :value="payType">

                    <!-- Nama Pemesan / Tim -->
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-[#0b1c30]">Nama Pemesan / Tim *</label>
                            <span class="text-[10px] text-[#777682]">Wajib diisi</span>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#777682] material-symbols-outlined text-lg">groups</span>
                            <input type="text" name="nama_tamu" id="input-pemesan" x-model="customerName" required
                                   placeholder="Contoh: Spartan Futsal Club"
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#eff4ff] text-[#0b1c30] text-xs font-semibold border border-[#e5eeff] focus:bg-white focus:ring-2 focus:ring-[#acf847] outline-none transition-all">
                        </div>
                    </div>

                    <!-- No WhatsApp Pemesan -->
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-[#0b1c30]">No. WhatsApp Pemesan *</label>
                            <span class="text-[10px] text-[#777682]">Konfirmasi struk digital</span>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#777682] material-symbols-outlined text-lg">chat</span>
                            <input type="tel" name="no_wa_tamu" x-model="whatsapp" required
                                   placeholder="0812xxxxxxx"
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#eff4ff] text-[#0b1c30] text-xs font-semibold border border-[#e5eeff] focus:bg-white focus:ring-2 focus:ring-[#acf847] outline-none transition-all">
                        </div>
                    </div>

                    <!-- Pilihan Lapangan (Real from Database) -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-[#0b1c30]">Pilihan Lapangan</label>
                        <select name="lapangan_id" x-model="selectedCourtId"
                                @change="
                                    let opt = $event.target.selectedOptions[0];
                                    selectedCourtName = opt.getAttribute('data-name');
                                    ratePerHour = parseInt(opt.getAttribute('data-rate'));
                                "
                                class="w-full px-3.5 py-2.5 rounded-xl bg-[#eff4ff] text-[#0b1c30] text-xs font-semibold border border-[#e5eeff] focus:bg-white focus:ring-2 focus:ring-[#acf847] outline-none transition-all">
                            @foreach ($lapangans as $lap)
                                @php
                                    $courtRate = $lap->hargaMasters->whereNull('tanggal_khusus')->where('jenis_hari', 'weekday')->first()?->harga ?? 120000;
                                @endphp
                                <option value="{{ $lap->id }}" data-name="{{ $lap->nama }}" data-rate="{{ (int)$courtRate }}">
                                    {{ $lap->nama }} - Rp {{ number_format($courtRate, 0, ',', '.') }}/jam
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Jam Mulai & Pilihan Durasi -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-[#0b1c30]">Jam Mulai</label>
                            <div class="relative">
                                <input type="time" name="jam_mulai" x-model="startTime" required
                                       class="w-full px-3 py-2 rounded-xl bg-[#eff4ff] text-[#0b1c30] text-xs font-bold border border-[#e5eeff] outline-none">
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-[#0b1c30]">Pilih Durasi</label>
                            <div class="grid grid-cols-3 gap-1.5">
                                <button type="button" @click="duration = 1"
                                        :class="duration === 1 ? 'bg-[#1a146b] text-white font-bold' : 'bg-[#eff4ff] text-[#0b1c30]'"
                                        class="py-2 rounded-lg text-xs transition-colors">1 Jam</button>
                                <button type="button" @click="duration = 2"
                                        :class="duration === 2 ? 'bg-[#1a146b] text-white font-bold' : 'bg-[#eff4ff] text-[#0b1c30]'"
                                        class="py-2 rounded-lg text-xs transition-colors">2 Jam</button>
                                <button type="button" @click="duration = 3"
                                        :class="duration === 3 ? 'bg-[#1a146b] text-white font-bold' : 'bg-[#eff4ff] text-[#0b1c30]'"
                                        class="py-2 rounded-lg text-xs transition-colors">3 Jam</button>
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Pembayaran Langsung -->
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold text-[#0b1c30]">Pilihan Pembayaran di Kasir</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-start gap-2.5 p-3 rounded-xl border border-[#e5eeff] cursor-pointer transition-all"
                                   :class="payType === 'full' ? 'bg-[#eff4ff] border-[#1a146b]' : 'bg-white'">
                                <input type="radio" value="full" x-model="payType" class="mt-0.5 text-[#1a146b]">
                                <div>
                                    <p class="text-xs font-bold text-[#0b1c30]">Bayar Lunas (100%)</p>
                                    <p class="text-[10px] text-[#777682]">Langsung beres di kasir</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-2.5 p-3 rounded-xl border border-[#e5eeff] cursor-pointer transition-all"
                                   :class="payType === 'dp' ? 'bg-[#eff4ff] border-[#1a146b]' : 'bg-white'">
                                <input type="radio" value="dp" x-model="payType" class="mt-0.5 text-[#1a146b]">
                                <div>
                                    <p class="text-xs font-bold text-[#0b1c30]">Bayar DP 50%</p>
                                    <p class="text-[10px] text-[#777682]">Sisa dilunasi sebelum main</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Cost Summary Box -->
                    <div class="rounded-xl bg-[#eff4ff] p-4 border border-[#dce9ff] flex flex-col gap-2">
                        <div class="flex items-center justify-between text-xs text-[#474651]">
                            <span>Biaya Sewa Lapangan (<span x-text="duration">2</span> Jam):</span>
                            <span class="font-bold">Rp <span x-text="formatRupiah(totalFee)"></span></span>
                        </div>
                        <div class="pt-2 border-t border-[#dce9ff] flex items-baseline justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#1a146b]">NOMINAL PEMBAYARAN DI KASIR</p>
                                <p class="text-[10px] text-[#416900] font-semibold" x-text="payType === 'full' ? '(Pelunasan Penuh 100%)' : '(DP 50% di Meja Kasir)'"></p>
                            </div>
                            <span class="text-2xl font-headline font-black text-[#1a146b]">
                                Rp <span x-text="formatRupiah(amountToPay)"></span>
                            </span>
                        </div>
                    </div>

                    <!-- CTA Submit Button -->
                    <button type="submit"
                            class="w-full py-3.5 px-5 rounded-xl bg-[#1a146b] hover:bg-[#312e81] text-white font-headline font-bold text-xs flex items-center justify-center gap-2 shadow-md transition-all active:scale-[0.99] cursor-pointer">
                        <span class="material-symbols-outlined text-lg">check_circle</span>
                        <span>Simpan Booking Walk-in ke Database</span>
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
