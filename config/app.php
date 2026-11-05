<?php

return [
    'name' => env('APP_NAME', 'Campuly'),
    'logo' => env('LOGO_PATH') ?: 'img/brand/campuly-mark.png',
    'logo_full' => env('LOGO_FULL_PATH') ?: 'img/brand/campuly-logo.png',
    'logo_full_dark' => env('LOGO_FULL_DARK_PATH') ?: 'img/brand/campuly-logo-dark.png',
    'app_icon' => env('APP_ICON_PATH') ?: 'img/brand/icon-512.png',
    'theme_color' => env('APP_THEME_COLOR', '#2E2CFC'),
    'favicon' => env('FAVICON_PATH') ?: 'favicon.ico',
    'currency' => env('APP_CURRENCY', 'NGN'),

    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'asset_url' => env('ASSET_URL'),

    'timezone' => env('APP_TIMEZONE', 'UTC'),
    // The language used when nothing else decides (docs/I18N.md, section 2).
    'locale' => env('APP_DEFAULT_LOCALE', env('APP_LOCALE', 'fr')),
    // Translation keys are the English text, so English is the safe fallback.
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    'supported_locales' => [
        'en' => 'English',
        'fr' => 'Français',
    ],

    // Region used for dates and numbers in each language (Cameroon).
    'number_locales' => [
        'en' => 'en_CM',
        'fr' => 'fr_CM',
    ],

    'key' => env('APP_KEY'),
    'previous_keys' => array_filter(explode(',', (string) env('APP_PREVIOUS_KEYS', ''))),
    'cipher' => 'AES-256-CBC',
];
