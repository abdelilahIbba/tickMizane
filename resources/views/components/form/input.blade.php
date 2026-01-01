@props([
    'type' => 'text',
    'name' => '',
    'label' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'value' => '',
    'error' => null,
    'hint' => null,
    'prefix' => null,
    'suffix' => null,
])

<div {{ $attributes->only('class')->merge(['class' => 'space-y-2']) }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-300">
            {{ $label }}
            @if($required)
                <span class="text-red-400">*</span>
            @endif
        </label>
    @endif
    
    <div class="relative">
        @if($prefix)
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <span class="text-gray-400">{{ $prefix }}</span>
            </div>
        @endif
        
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->except('class')->merge([
                'class' => 'w-full bg-gray-900 border rounded-xl px-4 py-3 text-white placeholder-gray-500 
                           focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent
                           disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200
                           min-h-[48px] text-base ' . 
                           ($prefix ? 'pl-10 ' : '') . 
                           ($suffix ? 'pr-10 ' : '') .
                           ($error ? 'border-red-500' : 'border-gray-700 hover:border-gray-600')
            ]) }}
        >
        
        @if($suffix)
            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                <span class="text-gray-400">{{ $suffix }}</span>
            </div>
        @endif
    </div>
    
    @if($hint && !$error)
        <p class="text-sm text-gray-500">{{ $hint }}</p>
    @endif
    
    @error($name)
        <p class="text-sm text-red-400 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
