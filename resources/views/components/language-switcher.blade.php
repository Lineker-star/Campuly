@props([
    // Listen for Alt+L and the command palette. Give it to one switcher per page.
    'shortcut' => false,
    // "links" reloads the same address with ?lang=, for pages without a session (error pages).
    'mode' => 'form',
])

@php
    $current = app()->getLocale();
    $locales = (array) config('app.supported_locales', []);
    $buttonClass = 'inline-flex min-h-9 min-w-10 items-center justify-center rounded px-2.5 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring';
    $activeClass = 'bg-primary text-primary-foreground shadow-sm';
    $idleClass = 'text-muted-foreground hover:bg-muted hover:text-foreground';
@endphp

@if ($mode === 'links')
    <nav aria-label="{{ __('Language') }}"
        {{ $attributes->merge(['class' => 'inline-flex items-center rounded-md border bg-muted/40 p-0.5 text-xs font-semibold']) }}>
        @foreach ($locales as $code => $name)
            <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}" lang="{{ $code }}" hreflang="{{ $code }}"
                @if ($current === $code) aria-current="true" @endif
                class="{{ $buttonClass }} {{ $current === $code ? $activeClass : $idleClass }}">
                <span aria-hidden="true">{{ strtoupper($code) }}</span>
                <span class="sr-only">{{ $name }}</span>
            </a>
        @endforeach
    </nav>
@else
    <form method="POST" action="{{ route('locale.update') }}"
        x-data
        x-on:submit="window.campulySaveDrafts?.()"
        @if ($shortcut)
            x-on:keydown.window="if ($event.altKey && !$event.ctrlKey && !$event.metaKey && $event.code === 'KeyL') { $event.preventDefault(); $el.querySelector('[aria-pressed=false]')?.click() }"
            x-on:switch-language.window="$el.querySelector('[aria-pressed=false]')?.click()"
        @endif
        role="group"
        aria-label="{{ __('Language') }}"
        {{ $attributes->merge(['class' => 'inline-flex items-center rounded-md border bg-muted/40 p-0.5 text-xs font-semibold']) }}>
        @csrf
        @foreach ($locales as $code => $name)
            <button type="submit" name="locale" value="{{ $code }}" lang="{{ $code }}"
                aria-pressed="{{ $current === $code ? 'true' : 'false' }}"
                title="{{ $name }}{{ $shortcut ? ' (Alt+L)' : '' }}"
                class="{{ $buttonClass }} {{ $current === $code ? $activeClass : $idleClass }}">
                <span aria-hidden="true">{{ strtoupper($code) }}</span>
                <span class="sr-only">{{ $name }}</span>
            </button>
        @endforeach
    </form>
@endif
