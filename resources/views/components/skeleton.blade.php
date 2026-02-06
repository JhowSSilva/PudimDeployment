<!-- Skeleton Loader Component -->
<div {{ $attributes->merge(['class' => 'animate-pulse']) }}>
    @if($attributes->get('type') === 'card')
        <!-- Card Skeleton -->
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <div class="h-4 bg-neutral-200 rounded w-3/4"></div>
            <div class="space-y-2">
                <div class="h-3 bg-neutral-200 rounded"></div>
                <div class="h-3 bg-neutral-200 rounded w-5/6"></div>
            </div>
            <div class="flex gap-2">
                <div class="h-8 bg-neutral-200 rounded w-20"></div>
                <div class="h-8 bg-neutral-200 rounded w-24"></div>
            </div>
        </div>
    
    @elseif($attributes->get('type') === 'table-row')
        <!-- Table Row Skeleton -->
        <tr>
            <td class="px-6 py-4"><div class="h-4 bg-neutral-200 rounded w-32"></div></td>
            <td class="px-6 py-4"><div class="h-4 bg-neutral-200 rounded w-24"></div></td>
            <td class="px-6 py-4"><div class="h-4 bg-neutral-200 rounded w-16"></div></td>
            <td class="px-6 py-4"><div class="h-4 bg-neutral-200 rounded w-20"></div></td>
        </tr>
    
    @elseif($attributes->get('type') === 'list-item')
        <!-- List Item Skeleton -->
        <div class="flex items-center gap-4 p-4 bg-white rounded-lg shadow">
            <div class="w-12 h-12 bg-neutral-200 rounded-full"></div>
            <div class="flex-1 space-y-2">
                <div class="h-4 bg-neutral-200 rounded w-1/3"></div>
                <div class="h-3 bg-neutral-200 rounded w-1/2"></div>
            </div>
        </div>
    
    @elseif($attributes->get('type') === 'avatar')
        <!-- Avatar Skeleton -->
        <div class="w-{{ $attributes->get('size') ?? '12' }} h-{{ $attributes->get('size') ?? '12' }} bg-neutral-200 rounded-full"></div>
    
    @elseif($attributes->get('type') === 'text')
        <!-- Text Line Skeleton -->
        <div class="h-{{ $attributes->get('height') ?? '4' }} bg-neutral-200 rounded {{ $attributes->get('width') ?? 'w-full' }}"></div>
    
    @else
        <!-- Custom Skeleton -->
        {{ $slot }}
    @endif
</div>
