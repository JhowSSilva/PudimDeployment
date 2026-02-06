<!-- Page Transition Wrapper -->
<div x-data="{ 
        pageLoaded: false,
        transitioning: false 
     }"
     x-init="
        // Page entry animation
        setTimeout(() => pageLoaded = true, 50);
        
        // Intercept navigation for transitions
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && link.href && !link.target && link.origin === window.location.origin && !link.hasAttribute('download')) {
                const url = new URL(link.href);
                if (url.pathname !== window.location.pathname) {
                    e.preventDefault();
                    transitioning = true;
                    setTimeout(() => {
                        window.location = link.href;
                    }, 200);
                }
            }
        });
     "
     :class="pageLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
     class="transition-all duration-300 ease-out">
    
    <!-- Loading overlay during transition -->
    <div x-show="transitioning"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="flex flex-col items-center gap-4">
            <x-loading size="lg" />
            <p class="text-sm text-neutral-600 font-medium">Loading...</p>
        </div>
    </div>
    
    {{ $slot }}
</div>
