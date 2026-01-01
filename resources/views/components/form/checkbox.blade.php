@props([
    'name' => '',
    'label' => '',
    'checked' => false,
    'disabled' => false,
    'value' => '1',
])

<label class="flex items-center gap-3 cursor-pointer group">
    <div class="relative">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ $value }}"
            {{ old($name, $checked) ? 'checked' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            class="sr-only peer"
        >
        <div class="w-6 h-6 bg-gray-900 border-2 border-gray-600 rounded-lg 
                    peer-checked:bg-amber-500 peer-checked:border-amber-500
                    peer-focus:ring-2 peer-focus:ring-amber-500 peer-focus:ring-offset-2 peer-focus:ring-offset-gray-900
                    peer-disabled:opacity-50 peer-disabled:cursor-not-allowed
                    group-hover:border-gray-500 transition-all duration-200">
        </div>
        <svg class="absolute top-1 left-1 w-4 h-4 text-gray-900 opacity-0 peer-checked:opacity-100 transition-opacity" 
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    
    @if($label)
        <span class="text-gray-300 group-hover:text-white transition-colors">{{ $label }}</span>
    @endif
</label>
