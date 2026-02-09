<!-- Loading States Component -->
<div x-data="{ loading: false }" x-cloak>
    <!-- Loading Overlay -->
    <div x-show="loading" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm mx-4 text-center">
            <!-- Spinner -->
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mb-4"></div>
            
            <!-- Loading text -->
            <h3 class="text-lg font-semibold text-neutral-900 mb-2">Validando Credenciais</h3>
            <p class="text-neutral-600 text-sm">Aguarde enquanto verificamos suas credenciais...</p>
        </div>
    </div>

    <!-- Form with loading state -->
    <form @submit="loading = true" method="POST" class="space-y-6">
        @csrf
        
        {{ $slot }}
        
        <!-- Submit Button with Loading State -->
        <div class="flex justify-end gap-4 pt-6">
            <a href="{{ $cancelRoute ?? '#' }}" class="px-6 py-2 border border-neutral-300 text-neutral-700 rounded-lg hover:bg-neutral-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" 
                    :disabled="loading"
                    :class="loading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-primary-600'"
                    class="px-6 py-2 bg-primary-500 text-white rounded-lg font-medium transition-colors">
                <span x-show="!loading">{{ $submitText ?? 'Salvar' }}</span>
                <span x-show="loading" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Validando...
                </span>
            </button>
        </div>
    </form>
</div>