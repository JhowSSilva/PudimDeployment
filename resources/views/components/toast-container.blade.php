<!-- Toast Notification System -->
<div x-data="toastManager()" 
     @toast-show.window="show($event.detail)"
     class="fixed top-4 right-4 z-50 space-y-3 pointer-events-none">
    
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible"
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transform transition ease-in duration-200"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="translate-x-full opacity-0"
             class="pointer-events-auto max-w-sm w-full bg-white rounded-lg shadow-xl border-l-4"
             :class="{
                 'border-green-500': toast.type === 'success',
                 'border-red-500': toast.type === 'error',
                 'border-amber-500': toast.type === 'warning',
                 'border-blue-500': toast.type === 'info'
             }">
            <div class="p-4 flex items-start gap-3">
                <!-- Icon -->
                <div class="flex-shrink-0">
                    <!-- Success Icon -->
                    <svg x-show="toast.type === 'success'" class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <!-- Error Icon -->
                    <svg x-show="toast.type === 'error'" class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <!-- Warning Icon -->
                    <svg x-show="toast.type === 'warning'" class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <!-- Info Icon -->
                    <svg x-show="toast.type === 'info'" class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                
                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-neutral-900" x-text="toast.title"></p>
                    <p x-show="toast.message" class="text-sm text-neutral-600 mt-1" x-text="toast.message"></p>
                </div>
                
                <!-- Close Button -->
                <button @click="remove(toast.id)" class="flex-shrink-0 text-neutral-400 hover:text-neutral-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Progress Bar -->
            <div x-show="toast.duration > 0" class="h-1 bg-neutral-100 overflow-hidden">
                <div class="h-full transition-all ease-linear"
                     :class="{
                         'bg-green-500': toast.type === 'success',
                         'bg-red-500': toast.type === 'error',
                         'bg-amber-500': toast.type === 'warning',
                         'bg-blue-500': toast.type === 'info'
                     }"
                     :style="`width: ${toast.progress}%; transition-duration: ${toast.duration}ms`">
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function toastManager() {
    return {
        toasts: [],
        nextId: 1,
        
        show({ type = 'info', title, message = '', duration = 5000 }) {
            const id = this.nextId++;
            const toast = {
                id,
                type,
                title,
                message,
                duration,
                visible: true,
                progress: 100
            };
            
            this.toasts.push(toast);
            
            if (duration > 0) {
                // Start progress animation
                setTimeout(() => {
                    toast.progress = 0;
                }, 10);
                
                // Auto remove
                setTimeout(() => {
                    this.remove(id);
                }, duration);
            }
        },
        
        remove(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index !== -1) {
                this.toasts[index].visible = false;
                setTimeout(() => {
                    this.toasts.splice(index, 1);
                }, 300);
            }
        }
    }
}

// Helper functions
window.showToast = function(type, title, message = '', duration = 5000) {
    window.dispatchEvent(new CustomEvent('toast-show', {
        detail: { type, title, message, duration }
    }));
}

window.toast = {
    success: (title, message, duration) => showToast('success', title, message, duration),
    error: (title, message, duration) => showToast('error', title, message, duration),
    warning: (title, message, duration) => showToast('warning', title, message, duration),
    info: (title, message, duration) => showToast('info', title, message, duration)
};
</script>
