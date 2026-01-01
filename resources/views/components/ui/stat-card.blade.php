@props([
    'title' => '',
    'value' => '',
    'icon' => null,
    'trend' => null,
    'trendUp' => true,
    'color' => 'amber',
])

@php
    $colors = [
        'amber' => 'from-amber-500 to-amber-600',
        'blue' => 'from-blue-500 to-blue-600',
        'emerald' => 'from-emerald-500 to-emerald-600',
        'red' => 'from-red-500 to-red-600',
        'purple' => 'from-purple-500 to-purple-600',
    ];
    $gradient = $colors[$color] ?? $colors['amber'];
@endphp

<div class="bg-gray-800 border border-gray-700 rounded-2xl p-6 hover:border-gray-600 transition-colors">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-400 uppercase tracking-wide">{{ $title }}</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $value }}</p>
            
            @if($trend)
                <div class="mt-2 flex items-center gap-1">
                    @if($trendUp)
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                        <span class="text-sm text-emerald-400">{{ $trend }}</span>
                    @else
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                        <span class="text-sm text-red-400">{{ $trend }}</span>
                    @endif
                </div>
            @endif
        </div>
        
        @if($icon)
            <div class="p-3 bg-gradient-to-br {{ $gradient }} rounded-xl shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $icon !!}
                </svg>
            </div>
        @endif
    </div>
</div>
