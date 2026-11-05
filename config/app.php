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
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    'supported_locales' => [
        'ar' => 'العربية',
        'bn' => 'বাংলা',
        'br' => 'Português (Brasil)',
        'de' => 'Deutsch',
        'en' => 'English',
        'es' => 'Español',
        'fr' => 'Français',
        'ja' => '日本語',
        'kr' => '한국어',
        'nl' => 'Nederlands',
        'pl' => 'Polski',
        'pt' => 'Português',
        'ro' => 'Română',
        'ru' => 'Русский',
        'zh' => '中文',
    ],

    'key' => env('APP_KEY'),
    'previous_keys' => array_filter(explode(',', (string) env('APP_PREVIOUS_KEYS', ''))),
    'cipher' => 'AES-256-CBC',
];
