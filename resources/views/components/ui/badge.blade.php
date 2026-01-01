@props([
    'variant' => 'default',
    'size' => 'md',
])

@php
    $variants = [
        'default' => 'bg-gray-700 text-gray-300',
        'primary' => 'bg-amber-500/20 text-amber-400 border border-amber-500/30',
        'success' => 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
        'danger' => 'bg-red-500/20 text-red-400 border border-red-500/30',
        'warning' => 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30',
        'info' => 'bg-blue-500/20 text-blue-400 border border-blue-500/30',
    ];
    
    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-sm',
        'lg' => 'px-3 py-1.5 text-base',
    ];
    
    $classes = 'inline-flex items-center font-medium rounded-full ' . 
               ($variants[$variant] ?? $variants['default']) . ' ' . 
               ($sizes[$size] ?? $sizes['md']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
