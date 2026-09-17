<?php

declare(strict_types=1);

/**
 * Local/demo quick-login accounts (shown on /login when APP_ENV=local).
 * Passwords match Password::min(8)->mixedCase()->numbers()->symbols().
 *
 * Temporarily restores password-in-DOM quick login for local convenience.
 * Disable in production: APICS_DEMO_QUICK_LOGIN=false (or non-local APP_ENV).
 */
return [
    'enabled' => filter_var(
        env('APICS_DEMO_QUICK_LOGIN', env('APP_ENV') === 'local'),
        FILTER_VALIDATE_BOOL
    ) && env('APP_ENV') === 'local',

    'users' => [
        [
            'label' => 'Administrator',
            'role' => 'admin',
            'email' => 'admin@csfp.local',
            'password' => 'Admin@12345',
            'redirect' => '/admin',
        ],
        [
            'label' => 'Building Official',
            'role' => 'building_official',
            'email' => 'official@csfp.local',
            'password' => 'Official@123',
            'redirect' => '/admin',
        ],
        [
            'label' => 'Receiving / Front desk',
            'role' => 'receiving',
            'email' => 'receiving@csfp.local',
            'password' => 'Receive@123',
            'redirect' => '/admin/evaluation-queue',
        ],
        [
            'label' => 'Evaluator',
            'role' => 'evaluator',
            'email' => 'evaluator@csfp.local',
            'password' => 'Evaluate@123',
            'redirect' => '/admin/evaluation-queue',
        ],
        [
            'label' => 'Inspector',
            'role' => 'inspector',
            'email' => 'inspector@csfp.local',
            'password' => 'Inspect@123',
            'redirect' => '/admin/inspections',
        ],
        [
            'label' => 'Assessor / Cashier',
            'role' => 'assessor',
            'email' => 'assessor@csfp.local',
            'password' => 'Assess@1234',
            'redirect' => '/admin/orders-of-payment',
        ],
        [
            'label' => 'Compliance officer',
            'role' => 'compliance',
            'email' => 'compliance@csfp.local',
            'password' => 'Comply@1234',
            'redirect' => '/admin/compliance-notices',
        ],
        [
            'label' => 'Records / Archivist',
            'role' => 'records',
            'email' => 'records@csfp.local',
            'password' => 'Records@123',
            'redirect' => '/admin/logbooks',
        ],
        [
            'label' => 'Applicant (approved)',
            'role' => 'applicant',
            'email' => 'applicant@csfp.local',
            'password' => 'Applicant@123',
            'redirect' => '/applications',
        ],
        [
            'label' => 'Applicant 2 (approved)',
            'role' => 'applicant',
            'email' => 'applicant2@csfp.local',
            'password' => 'Applicant@123',
            'redirect' => '/applications',
        ],
    ],
];
