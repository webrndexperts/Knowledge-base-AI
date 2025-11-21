<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="bg-gray-100 h-screen overflow-hidden">
        @include('partials.header')

        {{-- Sidebar and Main Content --}}
        <div class="flex h-[calc(100vh-4rem)]">
            @auth
                <livewire:components.conversation-sidebar :current-conversation-id="request()->route('encryptedId')" />
            @endauth
            
            {{-- Main Content --}}
            <main class="flex-1 {{ auth()->check() ? 'lg:ml-80' : '' }} overflow-hidden">
                {{ $slot }}
            </main>
        </div>

        @include('partials.scripts')
    </body>
</html>
