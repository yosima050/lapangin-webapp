{{-- Top Slim High-Speed Loading Bar for Page Transitions (GPU Composited) --}}
<div id="global-page-progress" class="fixed top-0 left-0 right-0 h-[3px] z-[9999] pointer-events-none opacity-0 transition-opacity duration-150">
    <div id="global-progress-bar" class="h-full w-full bg-gradient-to-r from-[#1a146b] via-[#acf847] to-[#416900] origin-left scale-x-0 transition-transform duration-200 ease-out shadow-[0_0_10px_#acf847]"></div>
</div>

{{-- GitHub-style Page Transition Shimmer Skeleton (muncul hanya jika navigasi > 180ms) --}}
<div id="global-page-skeleton"
     class="fixed inset-x-0 top-20 bottom-0 z-40 bg-[#f8f9ff] overflow-hidden opacity-0 pointer-events-none transition-opacity duration-150"
     aria-hidden="true">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-8 space-y-5 sm:space-y-6">
        {{-- Hero / Header Skeleton --}}
        <div class="rounded-2xl sm:rounded-3xl bg-white border border-gray-100 p-4 sm:p-7 shadow-2xs space-y-3">
            <div class="h-4 w-28 rounded-full skeleton-shimmer"></div>
            <div class="h-6 sm:h-8 w-3/4 sm:w-1/2 rounded-xl skeleton-shimmer"></div>
            <div class="h-3.5 w-full sm:w-2/3 rounded-lg skeleton-shimmer"></div>
        </div>

        {{-- Filter / Action Bar Skeleton --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <div class="h-9 w-20 rounded-xl skeleton-shimmer"></div>
                <div class="h-9 w-24 rounded-xl skeleton-shimmer"></div>
                <div class="h-9 w-20 rounded-xl skeleton-shimmer"></div>
            </div>
            <div class="h-9 w-full sm:w-64 rounded-xl skeleton-shimmer"></div>
        </div>

        {{-- Content Cards Skeleton --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
            @for ($i = 0; $i < 3; $i++)
                <div class="rounded-2xl bg-white border border-gray-100 p-4 shadow-2xs space-y-3.5">
                    <div class="h-36 sm:h-44 w-full rounded-xl skeleton-shimmer"></div>
                    <div class="space-y-2">
                        <div class="h-4 w-3/4 rounded-lg skeleton-shimmer"></div>
                        <div class="h-3 w-1/2 rounded-lg skeleton-shimmer"></div>
                    </div>
                    <div class="pt-2 border-t border-gray-100 flex items-center justify-between">
                        <div class="h-4 w-24 rounded-lg skeleton-shimmer"></div>
                        <div class="h-8 w-24 rounded-xl skeleton-shimmer"></div>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>

<script>
    // GitHub-style Page Progress Bar, Hover Prefetch & Smart Delayed Skeleton
    (function() {
        const progressBar = document.getElementById('global-progress-bar');
        const progressContainer = document.getElementById('global-page-progress');
        const pageSkeleton = document.getElementById('global-page-skeleton');
        const mainContent = document.getElementById('app-main-content');
        let isNavigating = false;
        let skeletonTimer = null;

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

        function triggerFastTransition(targetAnchor) {
            if (isNavigating) return;
            isNavigating = true;

            // 1. Optimistic Feedback: Pindahkan active border hijau seketika ke link menu navbar
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

            // 2. Gerakkan progress bar atas seketika (GPU-composited)
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

            // 3. Tampilkan GitHub-style skeleton hanya jika navigasi butuh > 180ms (bebas flicker saat cepat)
            skeletonTimer = setTimeout(() => {
                if (isNavigating && pageSkeleton) {
                    pageSkeleton.style.opacity = '1';
                    pageSkeleton.style.pointerEvents = 'auto';
                }
            }, 180);
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

            triggerFastTransition(anchor);
        });

        window.addEventListener('pageshow', resetTransitionState);
    })();
</script>
