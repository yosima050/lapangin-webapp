{{-- Top Slim High-Speed Loading Bar for Page Transitions (GPU Composited) --}}
<div id="global-page-progress" class="fixed top-0 left-0 right-0 h-[3px] z-[9999] pointer-events-none opacity-0 transition-opacity duration-150">
    <div id="global-progress-bar" class="h-full w-full bg-gradient-to-r from-[#1a146b] via-[#acf847] to-[#416900] origin-left scale-x-0 transition-transform duration-200 ease-out shadow-[0_0_10px_#acf847]"></div>
</div>

{{-- Route-Aware GitHub-style Shimmer Skeleton Overlay --}}
<div id="global-page-skeleton"
     class="fixed inset-x-0 top-20 bottom-0 z-40 bg-[#f8f9ff] overflow-hidden opacity-0 pointer-events-none transition-opacity duration-150"
     aria-hidden="true">

    {{-- 1. SKELETON: BERANDA (/) --}}
    <div data-skeleton="home" class="hidden max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-10 items-center">
            <div class="lg:col-span-6 space-y-4">
                <div class="h-6 w-44 rounded-full skeleton-shimmer"></div>
                <div class="h-9 sm:h-12 w-full rounded-2xl skeleton-shimmer"></div>
                <div class="h-9 sm:h-12 w-4/5 rounded-2xl skeleton-shimmer"></div>
                <div class="h-4 w-full rounded-lg skeleton-shimmer"></div>
                <div class="h-4 w-2/3 rounded-lg skeleton-shimmer"></div>
                <div class="flex items-center gap-3 pt-2">
                    <div class="h-11 w-40 rounded-xl skeleton-shimmer"></div>
                    <div class="h-11 w-32 rounded-xl skeleton-shimmer"></div>
                </div>
            </div>
            <div class="lg:col-span-6">
                <div class="h-56 sm:h-80 w-full rounded-3xl skeleton-shimmer"></div>
            </div>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
            @for ($i = 0; $i < 4; $i++)
                <div class="rounded-2xl bg-white border border-gray-100 p-4 space-y-2.5 shadow-2xs">
                    <div class="h-9 w-9 rounded-xl skeleton-shimmer"></div>
                    <div class="h-4 w-2/3 rounded-md skeleton-shimmer"></div>
                    <div class="h-3 w-full rounded-md skeleton-shimmer"></div>
                </div>
            @endfor
        </div>
    </div>

    {{-- 2. SKELETON: JELAJAHI LAPANGAN (/katalog) --}}
    <div data-skeleton="katalog" class="hidden max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-8 space-y-5 sm:space-y-6">
        <div class="space-y-2">
            <div class="h-4 w-28 rounded-full skeleton-shimmer"></div>
            <div class="h-7 sm:h-9 w-64 sm:w-80 rounded-xl skeleton-shimmer"></div>
            <div class="h-3.5 w-full sm:w-96 rounded-lg skeleton-shimmer"></div>
        </div>
        {{-- Filter Kategori + Search --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 overflow-hidden">
                <div class="h-9 w-20 rounded-full skeleton-shimmer shrink-0"></div>
                <div class="h-9 w-24 rounded-full skeleton-shimmer shrink-0"></div>
                <div class="h-9 w-24 rounded-full skeleton-shimmer shrink-0"></div>
                <div class="h-9 w-24 rounded-full skeleton-shimmer shrink-0"></div>
            </div>
            <div class="h-10 w-full sm:w-72 rounded-xl skeleton-shimmer"></div>
        </div>
        {{-- Mobile: Horizontal Compact Cards | Desktop: 3-Column Venue Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-6">
            @for ($i = 0; $i < 6; $i++)
                <div class="rounded-2xl bg-white border border-gray-100 p-3 sm:p-4 shadow-2xs grid grid-cols-[116px_1fr] sm:block gap-3 sm:space-y-3.5">
                    <div class="h-28 sm:h-44 w-full rounded-xl skeleton-shimmer"></div>
                    <div class="flex flex-col justify-between space-y-2">
                        <div class="space-y-1.5">
                            <div class="h-3 w-16 rounded-full skeleton-shimmer"></div>
                            <div class="h-4 w-5/6 rounded-md skeleton-shimmer"></div>
                            <div class="h-3 w-2/3 rounded-md skeleton-shimmer"></div>
                        </div>
                        <div class="pt-2 border-t border-gray-100 flex items-center justify-between">
                            <div class="h-4 w-24 rounded-md skeleton-shimmer"></div>
                            <div class="h-8 w-20 rounded-xl skeleton-shimmer"></div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>

    {{-- 3. SKELETON: CEK JADWAL (/jadwal) --}}
    <div data-skeleton="jadwal" class="hidden max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-8 space-y-4 sm:space-y-5">
        {{-- Header + 3 Stat Pills --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-2">
                <div class="h-4 w-32 rounded-full skeleton-shimmer"></div>
                <div class="h-7 sm:h-9 w-64 sm:w-80 rounded-xl skeleton-shimmer"></div>
                <div class="h-3.5 w-72 sm:w-96 rounded-lg skeleton-shimmer"></div>
            </div>
            <div class="grid grid-cols-3 gap-2 sm:flex sm:gap-3 w-full md:w-auto">
                @for ($i = 0; $i < 3; $i++)
                    <div class="bg-white rounded-xl sm:rounded-2xl px-3 py-2.5 sm:px-5 sm:py-3 border border-gray-100 sm:min-w-[110px] space-y-1.5">
                        <div class="h-2.5 w-14 rounded skeleton-shimmer mx-auto sm:mx-0"></div>
                        <div class="h-6 w-10 rounded-md skeleton-shimmer mx-auto sm:mx-0"></div>
                    </div>
                @endfor
            </div>
        </div>
        {{-- 7-Day Date Strip + Filter Bar --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-5 space-y-3.5 shadow-2xs">
            <div class="grid grid-cols-4 sm:grid-cols-7 gap-2">
                @for ($i = 0; $i < 7; $i++)
                    <div class="h-16 sm:h-20 rounded-xl skeleton-shimmer {{ $i >= 4 ? 'hidden sm:block' : '' }}"></div>
                @endfor
            </div>
            <div class="flex flex-col sm:flex-row justify-between gap-2.5 pt-2 border-t border-gray-100">
                <div class="flex gap-2">
                    <div class="h-8 w-20 rounded-full skeleton-shimmer"></div>
                    <div class="h-8 w-20 rounded-full skeleton-shimmer"></div>
                    <div class="h-8 w-24 rounded-full skeleton-shimmer"></div>
                </div>
                <div class="h-9 w-full sm:w-64 rounded-xl skeleton-shimmer"></div>
            </div>
        </div>
        {{-- Legend Bar --}}
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3 flex items-center justify-between gap-4">
            <div class="flex flex-wrap gap-3">
                <div class="h-4 w-20 rounded skeleton-shimmer"></div>
                <div class="h-4 w-20 rounded skeleton-shimmer"></div>
                <div class="h-4 w-24 rounded skeleton-shimmer"></div>
                <div class="h-4 w-20 rounded skeleton-shimmer"></div>
            </div>
            <div class="hidden lg:block h-3.5 w-52 rounded skeleton-shimmer"></div>
        </div>
        {{-- Cinema Matrix Cards --}}
        @for ($c = 0; $c < 2; $c++)
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-2xs">
                <div class="p-3.5 sm:p-5 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 sm:h-14 sm:w-14 rounded-xl skeleton-shimmer shrink-0"></div>
                        <div class="space-y-1.5">
                            <div class="h-4 w-44 sm:w-64 rounded-md skeleton-shimmer"></div>
                            <div class="h-3 w-32 rounded-md skeleton-shimmer"></div>
                        </div>
                    </div>
                    <div class="h-6 w-24 rounded-full skeleton-shimmer shrink-0"></div>
                </div>
                <div class="p-3 sm:p-5 grid grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-1.5 sm:gap-2.5">
                    @for ($s = 0; $s < 16; $s++)
                        <div class="py-3 px-1.5 rounded-xl border border-gray-100 bg-white flex flex-col items-center gap-1.5">
                            <div class="w-9 h-3 rounded skeleton-shimmer"></div>
                            <div class="w-11 h-2 rounded skeleton-shimmer"></div>
                            <div class="w-8 h-2.5 rounded skeleton-shimmer"></div>
                        </div>
                    @endfor
                </div>
            </div>
        @endfor
    </div>

    {{-- 4. SKELETON: DETAIL LAPANGAN (/lapangan/{id}) --}}
    <div data-skeleton="lapangan" class="hidden max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-4 sm:space-y-6">
        <div class="h-4 w-56 rounded-md skeleton-shimmer"></div>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-8 items-start">
            <div class="lg:col-span-8 space-y-4 sm:space-y-6">
                {{-- Venue Header --}}
                <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-8 border border-gray-100 flex items-center gap-3.5">
                    <div class="lg:hidden h-16 w-16 rounded-2xl skeleton-shimmer shrink-0"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-6 sm:h-8 w-3/4 rounded-xl skeleton-shimmer"></div>
                        <div class="h-3.5 w-full sm:w-2/3 rounded-md skeleton-shimmer"></div>
                    </div>
                    <div class="hidden sm:block h-10 w-36 rounded-xl skeleton-shimmer shrink-0"></div>
                </div>
                {{-- 7-Day Date Strip --}}
                <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-8 border border-gray-100 space-y-3.5">
                    <div class="flex justify-between items-center pb-2.5 border-b border-gray-100">
                        <div class="h-5 w-36 rounded-md skeleton-shimmer"></div>
                        <div class="h-4 w-24 rounded-md skeleton-shimmer"></div>
                    </div>
                    <div class="grid grid-cols-4 sm:grid-cols-7 gap-2">
                        @for ($i = 0; $i < 7; $i++)
                            <div class="h-20 rounded-xl sm:rounded-2xl skeleton-shimmer {{ $i >= 4 ? 'hidden sm:block' : '' }}"></div>
                        @endfor
                    </div>
                </div>
                {{-- Time Slots Card --}}
                <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-8 border border-gray-100 space-y-4">
                    <div class="flex justify-between">
                        <div class="h-4 w-36 rounded-md skeleton-shimmer"></div>
                        <div class="h-4 w-24 rounded-md skeleton-shimmer"></div>
                    </div>
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 sm:gap-3">
                        @for ($i = 0; $i < 8; $i++)
                            <div class="h-16 sm:h-20 rounded-xl sm:rounded-2xl skeleton-shimmer"></div>
                        @endfor
                    </div>
                </div>
            </div>
            {{-- Right Sticky Summary Sidebar --}}
            <div class="hidden lg:block lg:col-span-4 bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-5">
                <div class="flex justify-between">
                    <div class="h-4 w-32 rounded skeleton-shimmer"></div>
                    <div class="h-5 w-20 rounded-full skeleton-shimmer"></div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-xl skeleton-shimmer shrink-0"></div>
                    <div class="flex-1 space-y-1.5">
                        <div class="h-4 w-3/4 rounded skeleton-shimmer"></div>
                        <div class="h-3 w-1/2 rounded skeleton-shimmer"></div>
                    </div>
                </div>
                <div class="h-28 w-full rounded-2xl skeleton-shimmer"></div>
                <div class="h-12 w-full rounded-xl skeleton-shimmer"></div>
            </div>
        </div>
    </div>

    {{-- 5. SKELETON: CHECKOUT (/checkout) --}}
    <div data-skeleton="checkout" class="hidden">
        {{-- Top Stepper Bar --}}
        <div class="border-b border-[#e5eeff] bg-[#eff4ff] px-4 py-3.5 sm:py-5 sm:px-8">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 w-full sm:w-96">
                    <div class="h-6 w-20 rounded-full skeleton-shimmer"></div>
                    <div class="h-0.5 flex-1 skeleton-shimmer"></div>
                    <div class="h-6 w-24 rounded-full skeleton-shimmer"></div>
                    <div class="h-0.5 flex-1 skeleton-shimmer"></div>
                    <div class="h-6 w-20 rounded-full skeleton-shimmer"></div>
                </div>
                <div class="hidden md:block h-7 w-44 rounded-full skeleton-shimmer"></div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 py-5 sm:px-8 sm:py-8 space-y-5">
            <div class="space-y-2">
                <div class="h-4 w-32 rounded skeleton-shimmer"></div>
                <div class="h-7 sm:h-9 w-72 sm:w-96 rounded-xl skeleton-shimmer"></div>
                <div class="h-3.5 w-64 sm:w-80 rounded skeleton-shimmer"></div>
            </div>
            {{-- Countdown Banner --}}
            <div class="h-20 w-full rounded-2xl bg-amber-50/70 border border-amber-200/70 p-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 flex-1">
                    <div class="h-10 w-10 rounded-xl skeleton-shimmer shrink-0"></div>
                    <div class="space-y-1.5 flex-1">
                        <div class="h-3.5 w-40 rounded skeleton-shimmer"></div>
                        <div class="h-3 w-2/3 rounded skeleton-shimmer"></div>
                    </div>
                </div>
                <div class="h-10 w-24 rounded-xl skeleton-shimmer shrink-0"></div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-8">
                <div class="lg:col-span-7 space-y-5">
                    <div class="bg-white rounded-2xl border border-gray-100 p-4 sm:p-6 space-y-4">
                        <div class="h-5 w-40 rounded-md skeleton-shimmer"></div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="h-16 rounded-xl skeleton-shimmer"></div>
                            <div class="h-16 rounded-xl skeleton-shimmer"></div>
                            <div class="h-16 col-span-2 rounded-xl skeleton-shimmer"></div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 p-4 sm:p-6 space-y-3">
                        <div class="h-5 w-48 rounded-md skeleton-shimmer"></div>
                        <div class="h-16 rounded-2xl skeleton-shimmer"></div>
                        <div class="h-16 rounded-2xl skeleton-shimmer"></div>
                        <div class="h-16 rounded-2xl skeleton-shimmer"></div>
                    </div>
                </div>
                <div class="lg:col-span-5 bg-white rounded-2xl border border-gray-100 p-5 sm:p-6 space-y-4">
                    <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                        <div class="h-14 w-14 rounded-xl skeleton-shimmer shrink-0"></div>
                        <div class="flex-1 space-y-1.5">
                            <div class="h-4 w-3/4 rounded skeleton-shimmer"></div>
                            <div class="h-3 w-1/2 rounded skeleton-shimmer"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="h-20 rounded-xl skeleton-shimmer"></div>
                        <div class="h-20 rounded-xl skeleton-shimmer"></div>
                    </div>
                    <div class="space-y-2.5 pt-2">
                        <div class="flex justify-between"><div class="h-3 w-24 rounded skeleton-shimmer"></div><div class="h-3 w-28 rounded skeleton-shimmer"></div></div>
                        <div class="flex justify-between"><div class="h-3 w-28 rounded skeleton-shimmer"></div><div class="h-3 w-20 rounded skeleton-shimmer"></div></div>
                        <div class="flex justify-between"><div class="h-3 w-20 rounded skeleton-shimmer"></div><div class="h-3 w-16 rounded skeleton-shimmer"></div></div>
                    </div>
                    <div class="h-12 w-full rounded-xl skeleton-shimmer mt-2"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- 6. SKELETON: RIWAYAT BOOKING (/pelanggan/booking) --}}
    <div data-skeleton="riwayat" class="hidden max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 sm:pt-8 space-y-4 sm:space-y-6">
        {{-- Indigo Hero Banner Silhouette --}}
        <div class="rounded-2xl sm:rounded-3xl bg-[#1a146b]/15 border border-indigo-100 p-4 sm:p-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-2 flex-1">
                <div class="h-6 sm:h-9 w-64 sm:w-80 rounded-xl skeleton-shimmer"></div>
                <div class="h-3.5 w-full sm:w-96 rounded-lg skeleton-shimmer"></div>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:w-64">
                <div class="h-12 sm:h-16 rounded-xl skeleton-shimmer"></div>
                <div class="h-12 sm:h-16 rounded-xl skeleton-shimmer"></div>
            </div>
        </div>
        {{-- 4 Filter Tabs + Search --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="grid grid-cols-4 sm:flex gap-1.5 bg-gray-200/60 p-1 rounded-xl sm:rounded-2xl">
                @for ($i = 0; $i < 4; $i++)
                    <div class="h-8 sm:w-28 rounded-lg sm:rounded-xl skeleton-shimmer"></div>
                @endfor
            </div>
            <div class="h-10 w-full sm:w-72 rounded-xl sm:rounded-2xl skeleton-shimmer"></div>
        </div>
        {{-- Booking Cards --}}
        @for ($c = 0; $c < 2; $c++)
            <div class="rounded-2xl border border-gray-100 bg-white overflow-hidden shadow-2xs">
                <div class="px-4 py-3 border-b border-gray-100 bg-indigo-50/30 flex items-center justify-between gap-2">
                    <div class="h-3.5 w-36 rounded skeleton-shimmer"></div>
                    <div class="h-6 w-28 rounded-full skeleton-shimmer"></div>
                </div>
                <div class="p-4 sm:p-5 grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
                    <div class="lg:col-span-4 flex items-center gap-3">
                        <div class="h-20 w-20 sm:h-24 sm:w-28 rounded-xl skeleton-shimmer shrink-0"></div>
                        <div class="flex-1 space-y-2">
                            <div class="h-3 w-20 rounded skeleton-shimmer"></div>
                            <div class="h-4 w-full rounded skeleton-shimmer"></div>
                            <div class="h-3 w-24 rounded skeleton-shimmer"></div>
                        </div>
                    </div>
                    <div class="lg:col-span-5 rounded-xl bg-indigo-50/40 p-3.5 space-y-2">
                        <div class="h-3 w-24 rounded skeleton-shimmer"></div>
                        <div class="h-4 w-44 rounded skeleton-shimmer"></div>
                        <div class="h-4 w-32 rounded skeleton-shimmer"></div>
                    </div>
                    <div class="lg:col-span-3 space-y-2">
                        <div class="h-10 w-full rounded-xl skeleton-shimmer"></div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="h-9 rounded-xl skeleton-shimmer"></div>
                            <div class="h-9 rounded-xl skeleton-shimmer"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endfor
    </div>

    {{-- 7. SKELETON: SUKSES BAYAR / E-TIKET (/checkout/success/...) --}}
    <div data-skeleton="success" class="hidden max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-10 space-y-4 sm:space-y-6">
        <div class="rounded-2xl sm:rounded-3xl bg-emerald-50/70 border border-emerald-100 p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5 flex-1">
                <div class="h-12 w-12 rounded-full skeleton-shimmer shrink-0"></div>
                <div class="space-y-2 flex-1">
                    <div class="h-5 w-56 rounded-lg skeleton-shimmer"></div>
                    <div class="h-3.5 w-full sm:w-72 rounded skeleton-shimmer"></div>
                </div>
            </div>
            <div class="h-10 w-full sm:w-40 rounded-xl skeleton-shimmer shrink-0"></div>
        </div>
        <div class="rounded-2xl sm:rounded-3xl bg-white border border-gray-100 overflow-hidden shadow-sm">
            <div class="bg-[#1a146b]/15 p-5 sm:p-7 flex items-center gap-3.5">
                <div class="h-10 w-10 rounded-xl skeleton-shimmer shrink-0"></div>
                <div class="space-y-1.5 flex-1">
                    <div class="h-3 w-28 rounded skeleton-shimmer"></div>
                    <div class="h-6 w-56 rounded-lg skeleton-shimmer"></div>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-12">
                <div class="order-1 lg:order-2 lg:col-span-4 p-6 bg-[#f4f7ff] flex flex-col items-center gap-3">
                    <div class="h-44 w-44 rounded-2xl skeleton-shimmer"></div>
                    <div class="h-8 w-28 rounded-xl skeleton-shimmer"></div>
                </div>
                <div class="order-2 lg:order-1 lg:col-span-8 p-5 sm:p-8 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="h-16 rounded-xl skeleton-shimmer"></div>
                        <div class="h-16 rounded-xl skeleton-shimmer"></div>
                    </div>
                    <div class="h-24 rounded-xl skeleton-shimmer"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- 8. SKELETON: DEFAULT (/bantuan, /pelanggan/dashboard, /kontak, dll.) --}}
    <div data-skeleton="default" class="hidden max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-8 space-y-5">
        <div class="rounded-2xl sm:rounded-3xl bg-white border border-gray-100 p-5 sm:p-8 space-y-3 shadow-2xs">
            <div class="h-4 w-28 rounded-full skeleton-shimmer"></div>
            <div class="h-7 sm:h-9 w-2/3 sm:w-1/2 rounded-xl skeleton-shimmer"></div>
            <div class="h-3.5 w-full sm:w-2/3 rounded-lg skeleton-shimmer"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
            @for ($i = 0; $i < 4; $i++)
                <div class="rounded-2xl bg-white border border-gray-100 p-5 space-y-3 shadow-2xs">
                    <div class="h-4 w-1/2 rounded-md skeleton-shimmer"></div>
                    <div class="h-3 w-full rounded skeleton-shimmer"></div>
                    <div class="h-3 w-4/5 rounded skeleton-shimmer"></div>
                </div>
            @endfor
        </div>
    </div>

</div>

<script>
    // GitHub-style Route-Aware Page Progress Bar, Hover Prefetch & Smart Delayed Skeleton
    (function() {
        const progressBar = document.getElementById('global-progress-bar');
        const progressContainer = document.getElementById('global-page-progress');
        const pageSkeleton = document.getElementById('global-page-skeleton');
        const mainContent = document.getElementById('app-main-content');
        let isNavigating = false;
        let skeletonTimer = null;

        function resolveSkeletonType(urlStr) {
            try {
                const path = new URL(urlStr, window.location.origin).pathname.replace(/\/+$/, '') || '/';
                if (path === '/') return 'home';
                if (path === '/katalog') return 'katalog';
                if (path === '/jadwal') return 'jadwal';
                if (path.startsWith('/lapangan/')) return 'lapangan';
                if (path.startsWith('/checkout/success')) return 'success';
                if (path === '/checkout' || path.startsWith('/checkout')) return 'checkout';
                if (path.startsWith('/pelanggan/booking') || path.startsWith('/pelanggan/riwayat')) return 'riwayat';
                return 'default';
            } catch (e) {
                return 'default';
            }
        }

        function activateSkeletonVariant(type) {
            if (!pageSkeleton) return;
            const variants = pageSkeleton.querySelectorAll('[data-skeleton]');
            let matched = false;
            variants.forEach(el => {
                if (el.getAttribute('data-skeleton') === type) {
                    el.classList.remove('hidden');
                    matched = true;
                } else {
                    el.classList.add('hidden');
                }
            });
            if (!matched) {
                const fallback = pageSkeleton.querySelector('[data-skeleton="default"]');
                if (fallback) fallback.classList.remove('hidden');
            }
        }

        function resetTransitionState() {
            isNavigating = false;
            if (skeletonTimer) {
                clearTimeout(skeletonTimer);
                skeletonTimer = null;
            }
            if (progressContainer) {
                progressContainer.style.opacity = '0';
            }
            if (progressBar) {
                progressBar.style.transition = 'none';
                progressBar.style.transform = 'scaleX(0)';
            }
            if (pageSkeleton) {
                pageSkeleton.style.opacity = '0';
                pageSkeleton.style.pointerEvents = 'none';
            }
            if (mainContent) {
                mainContent.style.opacity = '1';
            }
        }

        function triggerFastTransition(targetUrl, targetAnchor) {
            if (isNavigating) return;
            isNavigating = true;

            // 1. Optimistic Feedback: Pindahkan active border hijau seketika ke link menu navbar
            if (targetAnchor) {
                const isBrandLogo = targetAnchor.getAttribute('aria-label') === 'LapangIn' || targetAnchor.closest('a[aria-label="LapangIn"]');
                const isNavLink = targetAnchor.matches('header nav div a');
                if (isNavLink && !isBrandLogo) {
                    const navLinks = document.querySelectorAll('header nav a');
                    navLinks.forEach(link => {
                        link.classList.remove('text-[#1a146b]', 'font-bold', 'border-b-2', 'border-[#acf847]');
                        link.classList.add('text-gray-600');
                    });
                    targetAnchor.classList.remove('text-gray-600');
                    targetAnchor.classList.add('text-[#1a146b]', 'font-bold', 'border-b-2', 'border-[#acf847]');
                }
            }

            // 2. Siapkan skeleton sesuai halaman tujuan
            const skeletonType = resolveSkeletonType(targetUrl);
            activateSkeletonVariant(skeletonType);

            // 3. Gerakkan progress bar atas seketika (GPU-composited)
            if (progressContainer && progressBar) {
                progressContainer.style.opacity = '1';
                progressBar.style.transition = 'transform 150ms ease-out';
                progressBar.style.transform = 'scaleX(0.75)';

                setTimeout(() => {
                    if (isNavigating) {
                        progressBar.style.transition = 'transform 320ms ease-out';
                        progressBar.style.transform = 'scaleX(0.92)';
                    }
                }, 160);
            }

            // 4. Tampilkan route-aware skeleton hanya jika navigasi butuh > 150ms
            skeletonTimer = setTimeout(() => {
                if (isNavigating && pageSkeleton) {
                    pageSkeleton.style.opacity = '1';
                    pageSkeleton.style.pointerEvents = 'auto';
                }
            }, 150);
        }

        // Preload di hover agar navigasi secepat mungkin
        const prefetchedUrls = new Set();
        function prefetchPage(url) {
            if (!url || prefetchedUrls.has(url) || url.startsWith('#') || url.startsWith('javascript:')) return;
            prefetchedUrls.add(url);
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = url;
            document.head.appendChild(link);
        }

        document.addEventListener('mouseover', function(e) {
            const anchor = e.target.closest('a');
            if (anchor && anchor.href && anchor.origin === window.location.origin) {
                prefetchPage(anchor.href);
            }
        }, { passive: true });

        document.addEventListener('click', function(e) {
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
            const anchor = e.target.closest('a');
            if (!anchor || !anchor.href) return;
            if (anchor.target === '_blank' || anchor.hasAttribute('download') || anchor.hasAttribute('data-no-progress')) return;
            if (anchor.origin !== window.location.origin) return;
            if (anchor.href === window.location.href || anchor.href.includes('#')) return;

            triggerFastTransition(anchor.href, anchor);
        });

        // Juga tangkap submit form GET (seperti tombol "Lanjut" dari Detail Lapangan ke Checkout)
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form || form.method.toLowerCase() !== 'get') return;
            if (form.target === '_blank' || form.hasAttribute('data-no-progress')) return;
            if (!form.action) return;
            try {
                const actionUrl = new URL(form.action, window.location.origin);
                if (actionUrl.origin === window.location.origin) {
                    triggerFastTransition(actionUrl.href, null);
                }
            } catch (err) {}
        });

        window.addEventListener('pageshow', resetTransitionState);
    })();
</script>
