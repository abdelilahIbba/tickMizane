@props([
    'title' => null,
    'subtitle' => null,
    'footer' => null,
    'padding' => true,
    'hover' => false,
])

@php
    $cardClasses = 'bg-gray-800 border border-gray-700 rounded-2xl shadow-xl';
    if ($hover) {
        $cardClasses .= ' hover:border-amber-500/50 hover:shadow-amber-500/10 transition-all duration-300';
    }
@endphp

<div {{ $attributes->merge(['class' => $cardClasses]) }}>
    @if($title || $subtitle)
        <div class="px-6 py-4 border-b border-gray-700">
            @if($title)
                <h3 class="text-lg font-semibold text-white">{{ $title }}</h3>
            @endif
            @if($subtitle)
                <p class="mt-1 text-sm text-gray-400">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    
    <div class="{{ $padding ? 'p-6' : '' }}">
        {{ $slot }}
    </div>
    
    @if($footer)
        <div class="px-6 py-4 border-t border-gray-700 bg-gray-800/50 rounded-b-2xl">
            {{ $footer }}
        </div>
    @endif
</div>
