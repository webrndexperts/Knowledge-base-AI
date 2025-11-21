@props([
    'url' => null,
])

@if($url)
    <flux:link href="{{ route($url) }}" class="flex md:ml-auto px-4 py-2 rounded !text-white !bg-gray-200  hover:!bg-gray-400 cursor-pointer !no-underline">
        <i class="fa-solid fa-arrow-left"></i> {{ __('messages.basic.back') }}
    </flux:link>
@endif