@props([
    'target' => '',
    'type' => 'button',
    'disabled' => false,
    'spinnerClass' => '',
])

<button 
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => 'lorder-button flex items-center cursor-pointer disabled:opacity-50',
        'wire:loading.attr' => 'disabled',
    ]) }}
>
    <!-- Normal text shown when not loading -->
    <span>{{ $slot }}</span>

    <!-- Spinner shown when loading -->
    <span wire:loading wire:target="{{ $target }}" class="ml-2 {{ $spinnerClass }}">
        <svg class="w-4 h-4 animate-spin text-current" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
        </svg>
    </span>
</button>