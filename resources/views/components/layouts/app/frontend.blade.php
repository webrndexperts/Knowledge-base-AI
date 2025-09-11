<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="bg-gray-100">
        @include('partials.header')

        {{-- Main Content --}}
        <main class="container mx-auto py-6">
            {{ $slot }}
        </main>

        @include('partials.footer')

        @include('partials.scripts')
    </body>
</html>
