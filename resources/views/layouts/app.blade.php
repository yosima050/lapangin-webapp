<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@hasSection('title')@yield('title') &bull; LapangIn @else LapangIn &bull; Platform Sewa Lapangan Olahraga @endif</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f8f9ff] text-[#0b1c30] min-h-screen flex flex-col font-sans antialiased">

    {{-- Top Slim High-Speed Loading Bar for Page Transitions --}}
    <div id="global-page-progress" class="fixed top-0 left-0 right-0 h-[3px] z-[9999] pointer-events-none opacity-0 transition-opacity duration-150">
        <div id="global-progress-bar" class="h-full bg-gradient-to-r from-[#1a146b] via-[#acf847] to-[#416900] w-0 transition-all duration-200 ease-out shadow-[0_0_10px_#acf847]"></div>
    </div>

    {{-- Unified Pelanggan Navbar --}}
    @include('layouts.navigation-pelanggan')

    {{-- Main Content with Soft Cross-fade Container --}}
    <main id="app-main-content" class="flex-1 pt-20 transition-opacity duration-150 ease-out">
        @yield('content')
    </main>

    {{-- Unified Pelanggan Footer --}}
    @include('layouts.footer-pelanggan')

    <script>
        // High-Speed Instant Preload & Optimistic Navbar Feedback
        (function() {
            const progressBar = document.getElementById('global-progress-bar');
            const progressContainer = document.getElementById('global-page-progress');
            const mainContent = document.getElementById('app-main-content');
            let isNavigating = false;

            function triggerFastTransition(targetAnchor) {
                if (isNavigating) return;
                isNavigating = true;

                // 1. Optimistic Feedback: Pindahkan active border hijau seketika ke link yang diklik
                const navLinks = document.querySelectorAll('header nav a');
                navLinks.forEach(link => {
                    link.classList.remove('text-[#1a146b]', 'font-bold', 'border-b-2', 'border-[#acf847]');
                    link.classList.add('text-gray-600');
                });
                targetAnchor.classList.remove('text-gray-600');
                targetAnchor.classList.add('text-[#1a146b]', 'font-bold', 'border-b-2', 'border-[#acf847]');

                // 2. Haluskan konten lama (fade sedikit) sehingga pengguna merasa halaman langsung bertransisi
                if (mainContent) {
                    mainContent.style.opacity = '0.75';
                }

                // 3. Gerakkan progress bar dengan sangat cepat
                progressContainer.style.opacity = '1';
                progressBar.style.transition = 'width 150ms ease-out';
                progressBar.style.width = '75%';

                setTimeout(() => {
                    if (isNavigating) {
                        progressBar.style.transition = 'width 300ms ease-out';
                        progressBar.style.width = '92%';
                    }
                }, 160);
            }

            // Preload di hover (Mouseenter) agar browser sudah cache DNS & koneksi sebelum diklik
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

            // Klik link internal: picu loading bar secepat kilat
            document.addEventListener('click', function(e) {
                const anchor = e.target.closest('a');
                if (!anchor || !anchor.href) return;
                if (anchor.target === '_blank' || anchor.hasAttribute('download')) return;
                if (anchor.origin !== window.location.origin) return;
                if (anchor.href === window.location.href || anchor.href.includes('#')) return;

                triggerFastTransition(anchor);
            });

            // Restore jika user tekan back/forward button (bfcache)
            window.addEventListener('pageshow', function() {
                if (progressContainer) {
                    progressContainer.style.opacity = '0';
                    progressBar.style.width = '0%';
                }
                if (mainContent) {
                    mainContent.style.opacity = '1';
                }
                isNavigating = false;
            });
        })();
    </script>
</body>
</html>
