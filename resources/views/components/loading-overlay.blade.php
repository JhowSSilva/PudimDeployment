<!-- Loading Overlay Component -->
<div x-data="{ show: false }"
     @loading-start.window="show = true"
     @loading-end.window="show = false"
     x-show="show"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
     style="display: none;">
    
    <div class="bg-white rounded-lg shadow-2xl p-8 max-w-sm w-full mx-4">
        <div class="flex flex-col items-center gap-4">
            <x-loading size="lg" type="dots" />
            
            <div class="text-center">
                <h3 class="text-lg font-semibold text-neutral-900 mb-1">
                    {{ $title ?? 'Processing...' }}
                </h3>
                <p class="text-sm text-neutral-600">
                    {{ $message ?? 'Please wait while we process your request.' }}
                </p>
            </div>
            
            @if($attributes->get('cancellable'))
                <button @click="show = false; $dispatch('loading-cancelled')"
                        class="mt-2 px-4 py-2 text-sm text-neutral-600 hover:text-neutral-900 transition">
                    Cancel
                </button>
            @endif
        </div>
    </div>
</div>

<script>
// Helper functions
window.showLoading = function() {
    window.dispatchEvent(new CustomEvent('loading-start'));
}

window.hideLoading = function() {
    window.dispatchEvent(new CustomEvent('loading-end'));
}
</script>
