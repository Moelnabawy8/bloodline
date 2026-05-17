<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Donor Match Radius (km)
    |--------------------------------------------------------------------------
    |
    | Default geo radius (kilometres) for matching donors to a blood request
    | when the hospital has coordinates. If the geo search returns no donors,
    | the matcher falls back to governorate/city text matching.
    |
    | Override per environment via the BLOODLINE_MATCH_RADIUS_KM env var.
    |
    */

    'match_radius_km' => (int) env('BLOODLINE_MATCH_RADIUS_KM', 100),

    /*
    |--------------------------------------------------------------------------
    | Max Donors Notified per Request
    |--------------------------------------------------------------------------
    |
    | Hard cap on how many donors get a notification for a single request.
    | Protects against accidental mass-mail when a popular blood type has
    | many matches. Override via BLOODLINE_MAX_NOTIFY.
    |
    */

    'max_notify' => (int) env('BLOODLINE_MAX_NOTIFY', 50),

];
