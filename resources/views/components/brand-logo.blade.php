@props([
    // mark: icon only · inline: icon + CAMPULY word · full: the official lock-up with tagline
    'variant' => 'inline',
    'size' => 'md',
    'tagline' => false,
])

@php
    $markSize = match ($size) {
        'sm' => 'h-7 w-7',
        'lg' => 'h-12 w-12',
        'xl' => 'h-16 w-16',
        default => 'h-9 w-9',
    };
    $wordSize = match ($size) {
        'sm' => 'text-base',
        'lg' => 'text-2xl',
        'xl' => 'text-3xl',
        default => 'text-lg',
    };
    $name = (string) config('app.name', 'Campuly');
    // Colour the last two letters like the official wordmark (CAMPU + LY).
    $head = mb_strtoupper(mb_substr($name, 0, max(mb_strlen($name) - 2, 0)));
    $tail = mb_strtoupper(mb_substr($name, -2));
@endphp

@if ($variant === 'full')
    <span {{ $attributes->merge(['class' => 'inline-flex']) }}>
        <img src="{{ asset(config('app.logo_full')) }}" alt="{{ $name }} — {{ __('Learn · Interact · Excel') }}"
            class="h-auto w-full max-w-xs dark:hidden" width="1572" height="436">
        <img src="{{ asset(config('app.logo_full_dark')) }}" alt="{{ $name }} — {{ __('Learn · Interact · Excel') }}"
            class="hidden h-auto w-full max-w-xs dark:block" width="1572" height="436">
    </span>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex min-w-0 items-center gap-2']) }}>
        <img src="{{ asset(config('app.logo')) }}" alt="" class="{{ $markSize }} shrink-0 object-contain" width="470" height="436">
        @if ($variant === 'inline')
            <span class="flex min-w-0 flex-col leading-none">
                <span class="brand-wordmark {{ $wordSize }} truncate">{{ $head }}<span class="brand-wordmark-accent">{{ $tail }}</span></span>
                @if ($tagline)
                    <span class="brand-tagline mt-1 truncate text-[0.625rem] font-medium uppercase text-muted-foreground">{{ __('Learn · Interact · Excel') }}</span>
                @endif
            </span>
        @endif
    </span>
@endif
