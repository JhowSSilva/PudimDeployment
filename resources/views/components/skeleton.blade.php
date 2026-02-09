<!-- Skeleton Loader Component -->
@props(['type' => 'custom', 'height' => '4', 'width' => 'w-full', 'size' => '12'])

<div {{ $attributes->merge(['class' => 'animate-pulse']) }}>
    @if($type === 'card')
        <div class="bg-neutral-800 rounded-lg border border-neutral-700 p-6 space-y-4">
            <div class="h-4 bg-neutral-700 rounded w-3/4"></div>
            <div class="space-y-2">
                <div class="h-3 bg-neutral-700 rounded"></div>
                <div class="h-3 bg-neutral-700 rounded w-5/6"></div>
            </div>
            <div class="flex gap-2">
                <div class="h-8 bg-neutral-700 rounded w-20"></div>
                <div class="h-8 bg-neutral-700 rounded w-24"></div>
            </div>
        </div>
    
    @elseif($type === 'table-row')
        <tr>
            <td class="px-6 py-4"><div class="h-4 bg-neutral-700 rounded w-32"></div></td>
            <td class="px-6 py-4"><div class="h-4 bg-neutral-700 rounded w-24"></div></td>
            <td class="px-6 py-4"><div class="h-4 bg-neutral-700 rounded w-16"></div></td>
            <td class="px-6 py-4"><div class="h-4 bg-neutral-700 rounded w-20"></div></td>
        </tr>
    
    @elseif($type === 'list-item')
        <div class="flex items-center gap-4 p-4 bg-neutral-800 rounded-lg border border-neutral-700">
            <div class="w-12 h-12 bg-neutral-700 rounded-full"></div>
            <div class="flex-1 space-y-2">
                <div class="h-4 bg-neutral-700 rounded w-1/3"></div>
                <div class="h-3 bg-neutral-700 rounded w-1/2"></div>
            </div>
        </div>
    
    @elseif($type === 'avatar')
        <div class="w-{{ $size }} h-{{ $size }} bg-neutral-700 rounded-full"></div>
    
    @elseif($type === 'text')
        <div class="h-{{ $height }} bg-neutral-700 rounded {{ $width }}"></div>
    
    @else
        {{ $slot }}
    @endif
</div>
