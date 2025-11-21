<header class="w-full bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <!-- Logo/Brand -->
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Knowledge Base AI</h1>
                    <p class="text-xs text-gray-500 hidden sm:block">Intelligent Document Assistant</p>
                </div>
            </div>

            <!-- Navigation -->
            @if (Route::has('login'))
                <nav class="flex items-center space-x-6">
                    @auth
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-600 hidden md:inline">Welcome back!</span>
                            <flux:link :href="route('dashboard')" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200" 
                                wire:navigate>
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 6 4-4 4 4"></path>
                                </svg>
                                {{ __('messages.sidebar.dashboard') }}
                            </flux:link>
                        </div>
                    @else
                        <div class="flex items-center space-x-3">
                            <flux:link :href="route('login')" 
                                class="text-gray-600 hover:text-blue-600 font-medium transition-colors duration-200" 
                                wire:navigate>{{ __('messages.basic.login') }}</flux:link>

                            @if (Route::has('register'))
                                <flux:link :href="route('register')" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200" 
                                    wire:navigate>{{ __('messages.basic.register') }}</flux:link>
                            @endif
                        </div>
                    @endauth
                </nav>
            @endif
        </div>
    </div>
</header>