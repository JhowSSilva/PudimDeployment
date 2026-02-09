@props(['name', 'title' => 'Confirmar ação', 'message' => 'Tem certeza?', 'confirmText' => 'Confirmar', 'cancelText' => 'Cancelar'])

<div x-data="{ show: false, formAction: '', formMethod: 'POST' }" 
     @open-confirm-modal.window="
         if ($event.detail.name === '{{ $name }}') { 
             show = true; 
             formAction = $event.detail.action || ''; 
             formMethod = $event.detail.method || 'POST';
         }
     "
     @close-confirm-modal.window="if ($event.detail === '{{ $name }}') show = false"
     @keydown.escape.window="show = false"
     x-show="show"
     class="relative z-50" 
     style="display: none;">
    
    <div x-show="show" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity"></div>

    <div x-show="show" class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.away="show = false"
                 class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                        <h3 class="text-base font-semibold leading-6 text-neutral-900">{{ $title }}</h3>
                        <div class="mt-2">
                            <p class="text-sm text-neutral-500">{{ $message }}</p>
                        </div>
                    </div>
                </div>
                
                <form :action="formAction" method="POST" class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    @csrf
                    <input type="hidden" name="_method" :value="formMethod">
                    
                    <button type="submit" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                        {{ $confirmText }}
                    </button>
                    <button type="button" @click="show = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-neutral-900 shadow-sm ring-1 ring-inset ring-neutral-300 hover:bg-neutral-50 sm:mt-0 sm:w-auto">
                        {{ $cancelText }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
