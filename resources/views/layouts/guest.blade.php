<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <x-partials.brand-head />
        <title>
            @yield('title', config('app.name', 'Campuly'))
        </title>

        <x-partials.theme-script />

        <!-- Styles -->
        @vite('resources/css/app.css')
        @vite('resources/js/app.js')
        <livewire:styles />
    </head>
    <body class="min-h-screen bg-background text-foreground" data-ui="april">
        {{-- Sign-in, password reset, invitations and the installer all use this layout. --}}
        <div class="fixed right-4 top-4 z-50">
            <x-language-switcher :shortcut="true" />
        </div>
        @yield('body')
        <livewire:display-status />
    </body>
    @livewireScriptConfig
</html>
