<?php

declare(strict_types=1);

/**
 * Public / GWTD contact details for OCBO landing chrome.
 * Override via .env when City Hall confirms official numbers.
 */
return [
    'agency_name' => env('APICS_AGENCY_NAME', 'Office of the City Building Official'),
    'lgu_name' => env('APICS_LGU_NAME', 'City Government of San Fernando, Pampanga'),
    'address' => env('APICS_OFFICE_ADDRESS', 'City Hall Complex, City of San Fernando, Pampanga, Philippines'),
    'phone' => env('APICS_OFFICE_PHONE', '(045) 961-XXXX'),
    'email' => env('APICS_OFFICE_EMAIL', 'ocbo@cityofsanfernando.gov.ph'),
    'office_hours' => env('APICS_OFFICE_HOURS', 'Monday–Friday, 8:00 AM – 5:00 PM (except holidays)'),
];
