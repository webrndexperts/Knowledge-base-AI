<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.admin.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @include('partials.admin.sidebar')

        {{ $slot }}

        @include('partials.admin.scripts')
    </body>
</html>
