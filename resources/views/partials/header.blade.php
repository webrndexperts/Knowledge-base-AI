<header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
    @if (Route::has('login'))
        <nav class="flex items-center justify-end gap-4">
            @auth
            	<flux:link :href="route('dashboard')" class="text-green-600 font-medium hover:!underline" wire:navigate>{{ __('messages.sidebar.dashboard') }}</flux:link>
            @else
                <flux:link :href="route('login')" class="text-green-600 font-medium hover:!underline" wire:navigate>{{ __('messages.basic.login') }}</flux:link>

                @if (Route::has('register'))
                	<flux:link :href="route('register')" class="text-green-600 font-medium hover:!underline" wire:navigate>{{ __('messages.basic.register') }}</flux:link>
                @endif
            @endauth
        </nav>
    @endif
</header>