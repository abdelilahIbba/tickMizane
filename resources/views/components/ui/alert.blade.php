@props([
    'type' => 'info',
    'message' => null,
    'dismissible' => true,
])

@php
    $types = [
        'success' => [
            'bg' => 'bg-emerald-500/10 border-emerald-500/50',
            'text' => 'text-emerald-400',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ],
        'error' => [
            'bg' => 'bg-red-500/10 border-red-500/50',
            'text' => 'text-red-400',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ],
        'warning' => [
            'bg' => 'bg-amber-500/10 border-amber-500/50',
            'text' => 'text-amber-400',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
        ],
        'info' => [
            'bg' => 'bg-blue-500/10 border-blue-500/50',
            'text' => 'text-blue-400',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ],
    ];
    $config = $types[$type] ?? $types['info'];
@endphp

@if($message || $slot->isNotEmpty())
<div 
    x-data="{ show: true }" 
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform -translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform -translate-y-2"
    {{ $attributes->merge(['class' => "flex items-start gap-3 p-4 rounded-xl border {$config['bg']}"]) }}
    role="alert"
>
    <svg class="w-6 h-6 flex-shrink-0 {{ $config['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        {!! $config['icon'] !!}
    </svg>
    
    <div class="flex-1 {{ $config['text'] }}">
        @if($message)
            {{ $message }}
        @else
            {{ $slot }}
        @endif
    </div>
    
    @if($dismissible)
        <button 
            @click="show = false"
            class="p-1 {{ $config['text'] }} hover:opacity-70 transition-opacity"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif
</div>
@endif
