<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Recovery targets
    |--------------------------------------------------------------------------
    |
    | These are the pilot release targets. A deployment must not claim release
    | readiness until the owner has approved the retention policy below.
    |
    */
    'rpo_hours' => (int) env('RELEASE_RPO_HOURS', 24),
    'rto_minutes' => (int) env('RELEASE_RTO_MINUTES', 240),

    /*
    |--------------------------------------------------------------------------
    | Data retention approval
    |--------------------------------------------------------------------------
    */
    'retention_policy_version' => (string) env('RELEASE_RETENTION_POLICY_VERSION', '2026-08'),
    'retention_policy_approved' => filter_var(
        env('RELEASE_RETENTION_POLICY_APPROVED', false),
        FILTER_VALIDATE_BOOL,
    ),

    /*
    |--------------------------------------------------------------------------
    | Pilot report set
    |--------------------------------------------------------------------------
    */
    'pilot_report_keys' => [
        'class-list',
        'student-balances',
        'report-cards',
        'transcripts',
    ],

    /*
    |--------------------------------------------------------------------------
    | Update source
    |--------------------------------------------------------------------------
    |
    | The GitHub repository whose releases `campuly:update` installs. It points
    | at the Campuly fork so an update never brings upstream Skuul over the brand.
    | SKUUL_UPDATE_REPOSITORY is still read for installs that already set it.
    |
    */
    'update_repository' => (string) env('CAMPULY_UPDATE_REPOSITORY', env('SKUUL_UPDATE_REPOSITORY', 'Lineker-star/Campuly')),
];
