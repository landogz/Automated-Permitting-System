<?php

declare(strict_types=1);

/**
 * Public privilege / role documentation for APICS (English + Tagalog).
 * Keep in sync with database/seeders/RolesAndPermissionsSeeder.php.
 */
return [
    'intro' => [
        'en' => [
            'title' => 'Privilege & role documentation',
            'lede' => 'This page explains every system privilege (permission) in APICS and which office functions each one unlocks. Staff accounts receive privileges through roles. Applicants do not receive office privileges; they use the public applicant portal after registration is approved.',
            'note' => 'Privileges are enforced on the server (API / policies). Hiding a menu item is not security by itself.',
            'flowchart_title' => 'How privileges map to the permit pipeline',
            'flowchart_hint' => 'Click any box to open a short explanation of that function. Use the language switch for Tagalog.',
            'modal_screens' => 'Screens / modules',
            'modal_functions' => 'Functions covered',
            'modal_close' => 'Close',
            'modal_read_more' => 'Read full details below',
        ],
        'tl' => [
            'title' => 'Dokumentasyon ng mga pribilehiyo at tungkulin',
            'lede' => 'Ipinapaliwanag ng pahinang ito ang bawat pribilehiyo (permission) sa APICS at kung anong mga tungkulin sa opisina ang nabubuksan ng bawat isa. Ang mga account ng kawani ay may pribilehiyo ayon sa kanilang tungkulin (role). Ang mga aplikante ay walang pribilehiyo ng opisina; ginagamit nila ang portal ng aplikante pagkatapos maaprubahan ang pagpaparehistro.',
            'note' => 'Ipinapatupad ang mga pribilehiyo sa server (API / policies). Ang pagtatago lang ng menu ay hindi sapat na seguridad.',
            'flowchart_title' => 'Paano nakaugnay ang mga pribilehiyo sa pipeline ng permit',
            'flowchart_hint' => 'I-click ang kahon upang makita ang maikling paliwanag. Gamitin ang language switch para sa English.',
            'modal_screens' => 'Mga screen / module',
            'modal_functions' => 'Mga function na saklaw',
            'modal_close' => 'Isara',
            'modal_read_more' => 'Basahin ang buong detalye sa ibaba',
        ],
    ],

    /**
     * Interactive flowchart nodes (top of documentation page).
     * `key` is privilege code or special `applicant`.
     *
     * @var list<array{key: string, lane: string, icon: string, en: array{label: string, short: string}, tl: array{label: string, short: string}}>
     */
    'flowchart' => [
        [
            'key' => 'applicant',
            'lane' => 'citizen',
            'icon' => 'ri-user-smile-line',
            'en' => ['label' => 'Applicant portal', 'short' => 'Register, apply, track'],
            'tl' => ['label' => 'Portal ng aplikante', 'short' => 'Magparehistro, mag-apply, subaybayan'],
        ],
        [
            'key' => 'applications.manage',
            'lane' => 'pipeline',
            'icon' => 'ri-file-list-3-line',
            'en' => ['label' => 'Applications (staff)', 'short' => 'Lookup & staff inbox'],
            'tl' => ['label' => 'Aplikasyon (kawani)', 'short' => 'Paghahanap at inbox'],
        ],
        [
            'key' => 'evaluations.manage',
            'lane' => 'pipeline',
            'icon' => 'ri-clipboard-line',
            'en' => ['label' => 'Evaluations', 'short' => 'Queue, classify, slips'],
            'tl' => ['label' => 'Evaluation', 'short' => 'Queue, classify, slip'],
        ],
        [
            'key' => 'inspections.manage',
            'lane' => 'pipeline',
            'icon' => 'ri-search-eye-line',
            'en' => ['label' => 'Inspections', 'short' => 'Schedule & field forms'],
            'tl' => ['label' => 'Inspeksyon', 'short' => 'Iskedyul at field forms'],
        ],
        [
            'key' => 'fees.manage',
            'lane' => 'pipeline',
            'icon' => 'ri-bill-line',
            'en' => ['label' => 'Fees / OoP', 'short' => 'G-02 assessment'],
            'tl' => ['label' => 'Bayarin / OoP', 'short' => 'Pagtataya ng G-02'],
        ],
        [
            'key' => 'compliance.manage',
            'lane' => 'pipeline',
            'icon' => 'ri-error-warning-line',
            'en' => ['label' => 'Compliance', 'short' => 'G-03 / G-04 notices'],
            'tl' => ['label' => 'Compliance', 'short' => 'G-03 / G-04 notice'],
        ],
        [
            'key' => 'records.manage',
            'lane' => 'pipeline',
            'icon' => 'ri-archive-drawer-line',
            'en' => ['label' => 'Records', 'short' => 'Logbooks & archives'],
            'tl' => ['label' => 'Records', 'short' => 'Logbook at archive'],
        ],
        [
            'key' => 'users.manage',
            'lane' => 'admin',
            'icon' => 'ri-user-settings-line',
            'en' => ['label' => 'Users', 'short' => 'Accounts & registrations'],
            'tl' => ['label' => 'Users', 'short' => 'Account at registration'],
        ],
        [
            'key' => 'departments.manage',
            'lane' => 'admin',
            'icon' => 'ri-building-line',
            'en' => ['label' => 'Departments', 'short' => 'Office master data'],
            'tl' => ['label' => 'Departments', 'short' => 'Master data ng opisina'],
        ],
        [
            'key' => 'forms.manage',
            'lane' => 'admin',
            'icon' => 'ri-survey-line',
            'en' => ['label' => 'Forms', 'short' => 'QMS form builder'],
            'tl' => ['label' => 'Forms', 'short' => 'QMS form builder'],
        ],
        [
            'key' => 'workflow.manage',
            'lane' => 'admin',
            'icon' => 'ri-flow-chart',
            'en' => ['label' => 'Workflow config', 'short' => 'Rules, routes, fees'],
            'tl' => ['label' => 'Workflow config', 'short' => 'Rules, ruta, bayarin'],
        ],
        [
            'key' => 'audit.view',
            'lane' => 'admin',
            'icon' => 'ri-shield-keyhole-line',
            'en' => ['label' => 'Audit trail', 'short' => 'ICT oversight logs'],
            'tl' => ['label' => 'Audit trail', 'short' => 'Tala para sa ICT'],
        ],
    ],

    'applicant' => [
        'en' => [
            'title' => 'Applicant portal (no office privileges)',
            'summary' => 'Citizen / owner / authorized representative accounts after OCBO approval.',
            'functions' => [
                'Self-register and wait for admin approve / decline',
                'Sign in to the applicant workspace',
                'Create, edit, and submit permit applications (QMS forms)',
                'Upload supporting documents',
                'Track application status through the pipeline',
                'File appeals on compliance notices when allowed',
                'Update own profile and password',
            ],
        ],
        'tl' => [
            'title' => 'Portal ng aplikante (walang pribilehiyo ng opisina)',
            'summary' => 'Account ng mamamayan / may-ari / awtorisadong kinatawan pagkatapos ng pag-apruba ng OCBO.',
            'functions' => [
                'Magparehistro at maghintay ng apruba / pagtanggi ng admin',
                'Mag-sign in sa workspace ng aplikante',
                'Gumawa, mag-edit, at magsumite ng aplikasyon (mga formang QMS)',
                'Mag-upload ng mga supporting documents',
                'Subaybayan ang status ng aplikasyon sa pipeline',
                'Maghain ng apela sa compliance notice kung pinapayagan',
                'I-update ang sariling profile at password',
            ],
        ],
    ],

    'privileges' => [
        'applications.manage' => [
            'screens' => ['Staff application lookup', 'Notifications inbox (staff)'],
            'en' => [
                'title' => 'Manage applications (staff)',
                'summary' => 'Read and work with permit applications from the OCBO side of the pipeline.',
                'functions' => [
                    'Look up applications across receiving / evaluation / later stages',
                    'Open staff application detail for operational follow-up',
                    'Access the staff notifications inbox for operational messages',
                    'Shared base privilege for most office roles (paired with a specialty privilege)',
                ],
            ],
            'tl' => [
                'title' => 'Pamamahala ng aplikasyon (kawani)',
                'summary' => 'Basahin at trabahuhin ang mga permit application mula sa panig ng OCBO.',
                'functions' => [
                    'Maghanap ng aplikasyon sa receiving / evaluation / susunod na yugto',
                    'Buksan ang detalye ng aplikasyon para sa follow-up ng operasyon',
                    'Buksan ang inbox ng notipikasyon ng kawani',
                    'Batayang pribilehiyo ng karamihan sa mga tungkulin sa opisina (kasama ang espesyal na pribilehiyo)',
                ],
            ],
        ],
        'users.manage' => [
            'screens' => ['Registrations queue', 'Users console'],
            'en' => [
                'title' => 'Manage users & registrations',
                'summary' => 'Control who may sign in as an applicant or office user.',
                'functions' => [
                    'Approve or decline applicant self-registrations (with email notices)',
                    'Create and update staff / office user accounts',
                    'Assign roles that carry privileges',
                    'Support account lifecycle for OCBO personnel',
                ],
            ],
            'tl' => [
                'title' => 'Pamamahala ng mga user at pagpaparehistro',
                'summary' => 'Kontrolin kung sino ang maaaring mag-sign in bilang aplikante o kawani.',
                'functions' => [
                    'Aprubahan o tanggihan ang self-registration ng aplikante (may email)',
                    'Gumawa at i-update ang account ng kawani / opisina',
                    'Magtalaga ng mga tungkulin (roles) na may pribilehiyo',
                    'Suportahan ang lifecycle ng account ng tauhan ng OCBO',
                ],
            ],
        ],
        'departments.manage' => [
            'screens' => ['Departments'],
            'en' => [
                'title' => 'Manage departments',
                'summary' => 'Maintain office master data used in routing and evaluation.',
                'functions' => [
                    'Create, update, and deactivate departments / offices',
                    'Import and export department lists',
                    'Keep routing destinations consistent with OCBO structure',
                ],
            ],
            'tl' => [
                'title' => 'Pamamahala ng mga departamento',
                'summary' => 'Panatilihin ang master data ng opisina na ginagamit sa routing at evaluation.',
                'functions' => [
                    'Gumawa, i-update, at i-deactivate ang departamento / opisina',
                    'Mag-import at mag-export ng listahan ng departamento',
                    'Panatilihing tugma ang destinasyon ng routing sa istruktura ng OCBO',
                ],
            ],
        ],
        'forms.manage' => [
            'screens' => ['Form definitions'],
            'en' => [
                'title' => 'Manage form definitions',
                'summary' => 'Administer dynamic QMS application form schemas.',
                'functions' => [
                    'Create and edit form definitions (sections and fields)',
                    'Configure field types including map location fields',
                    'Set required attachments and activate / deactivate forms',
                    'Use templates aligned with QMS intake forms',
                ],
            ],
            'tl' => [
                'title' => 'Pamamahala ng mga form definition',
                'summary' => 'Pangasiwaan ang mga dynamic na schema ng formang QMS.',
                'functions' => [
                    'Gumawa at mag-edit ng form definition (seksyon at field)',
                    'I-configure ang uri ng field kabilang ang map location',
                    'Itakda ang required attachments at i-activate / i-deactivate ang form',
                    'Gumamit ng template na tugma sa mga formang QMS',
                ],
            ],
        ],
        'workflow.manage' => [
            'screens' => ['Classification rules', 'Routing templates', 'Fee rules', 'Notification templates'],
            'en' => [
                'title' => 'Manage workflow configuration',
                'summary' => 'Configure how applications are classified, routed, priced, and messaged.',
                'functions' => [
                    'Maintain permit classifier rules (Simple / Complex / Highly Technical)',
                    'Design routing templates and office steps with SLA hours',
                    'Maintain fee rules used by Orders of Payment',
                    'Manage notification message templates (when authorized)',
                ],
            ],
            'tl' => [
                'title' => 'Pamamahala ng configuration ng workflow',
                'summary' => 'I-configure kung paano ina-classify, niruruta, sinisingil, at tinutugunan ang aplikasyon.',
                'functions' => [
                    'Panatilihin ang mga classification rule (Simple / Complex / Highly Technical)',
                    'Gumawa ng routing template at hakbang ng opisina na may SLA',
                    'Panatilihin ang fee rules para sa Order of Payment',
                    'Pamahalaan ang notification templates (kung awtorisado)',
                ],
            ],
        ],
        'evaluations.manage' => [
            'screens' => ['Evaluation queue', 'Routing slips', 'Evaluation forms'],
            'en' => [
                'title' => 'Manage evaluations',
                'summary' => 'Run receiving and technical evaluation for submitted applications.',
                'functions' => [
                    'Work the evaluation queue and claim / process slips',
                    'Classify applications when permitted',
                    'Complete evaluation forms and recommendations',
                    'Print / save evaluation and routing slip documents',
                    'Advance applications toward inspection or compliance paths',
                ],
            ],
            'tl' => [
                'title' => 'Pamamahala ng evaluation',
                'summary' => 'Patakbuhin ang receiving at teknikal na evaluation ng naisumiteng aplikasyon.',
                'functions' => [
                    'Trabaho sa evaluation queue at mag-claim / magproseso ng slip',
                    'I-classify ang aplikasyon kung pinapayagan',
                    'Kumpletuhin ang evaluation forms at rekomendasyon',
                    'I-print / i-save ang evaluation at routing slip',
                    'Isulong ang aplikasyon tungo sa inspection o compliance',
                ],
            ],
        ],
        'inspections.manage' => [
            'screens' => ['Inspections'],
            'en' => [
                'title' => 'Manage inspections',
                'summary' => 'Schedule and record field inspections tied to applications.',
                'functions' => [
                    'Schedule inspections and assign teams',
                    'Capture inspection form products and findings',
                    'Update inspection status and notes',
                    'Print inspection-related documents',
                ],
            ],
            'tl' => [
                'title' => 'Pamamahala ng inspeksyon',
                'summary' => 'Mag-iskedyul at magtala ng field inspection na nakaugnay sa aplikasyon.',
                'functions' => [
                    'Mag-iskedyul ng inspeksyon at magtalaga ng team',
                    'Kumpunihin ang inspection forms at natuklasan',
                    'I-update ang status at tala ng inspeksyon',
                    'I-print ang mga dokumentong may kinalaman sa inspeksyon',
                ],
            ],
        ],
        'fees.manage' => [
            'screens' => ['Orders of Payment'],
            'en' => [
                'title' => 'Manage fees & Orders of Payment',
                'summary' => 'Assess fees and issue Orders of Payment (G-02 flow).',
                'functions' => [
                    'Generate and review Orders of Payment',
                    'Apply fee rules and overrides when authorized',
                    'Track payment-related application status',
                    'Support CTO / cashier hand-off stubs configured for Phase I',
                ],
            ],
            'tl' => [
                'title' => 'Pamamahala ng bayarin at Order of Payment',
                'summary' => 'Tayahin ang bayarin at mag-isyu ng Order of Payment (daloy ng G-02).',
                'functions' => [
                    'Gumawa at suriin ang Order of Payment',
                    'Ilapat ang fee rules at override kung awtorisado',
                    'Subaybayan ang status ng aplikasyon kaugnay ng bayad',
                    'Suportahan ang CTO / cashier hand-off (Phase I stubs)',
                ],
            ],
        ],
        'compliance.manage' => [
            'screens' => ['Compliance notices'],
            'en' => [
                'title' => 'Manage compliance notices',
                'summary' => 'Issue and resolve G-03 / G-04 compliance and disapproval notices.',
                'functions' => [
                    'Issue Notice of Compliance (G-03) or Disapproval (G-04)',
                    'Record office actions on notices',
                    'Review applicant appeals filed against notices',
                    'Uphold or deny appeals and re-open evaluation when required',
                ],
            ],
            'tl' => [
                'title' => 'Pamamahala ng compliance notice',
                'summary' => 'Mag-isyu at lutasin ang G-03 / G-04 compliance at disapproval notice.',
                'functions' => [
                    'Mag-isyu ng Notice of Compliance (G-03) o Disapproval (G-04)',
                    'Itala ang aksyon ng opisina sa notice',
                    'Suriin ang apela ng aplikante laban sa notice',
                    'Panatilihin o tanggihan ang apela at muling buksan ang evaluation kung kailangan',
                ],
            ],
        ],
        'records.manage' => [
            'screens' => ['Logbooks', 'Archives'],
            'en' => [
                'title' => 'Manage records & archives',
                'summary' => 'Keep releasing / receiving logbooks and archive records.',
                'functions' => [
                    'Create and maintain logbook entries (G-01 / related books)',
                    'Print official logbook pages',
                    'Create archive records for completed applications',
                    'Support records-ready release trail for OCBO',
                ],
            ],
            'tl' => [
                'title' => 'Pamamahala ng records at archive',
                'summary' => 'Panatilihin ang logbook ng releasing / receiving at mga archive record.',
                'functions' => [
                    'Gumawa at panatilihin ang logbook entries (G-01 / kaugnay)',
                    'I-print ang opisyal na pahina ng logbook',
                    'Gumawa ng archive record para sa tapos na aplikasyon',
                    'Suportahan ang records-ready release trail ng OCBO',
                ],
            ],
        ],
        'audit.view' => [
            'screens' => ['Audit trail', 'Project plan (read)'],
            'en' => [
                'title' => 'View audit trail',
                'summary' => 'Read immutable activity logs for ICT oversight and accountability.',
                'functions' => [
                    'Browse authentication and mutation events (actor, IP, safe metadata)',
                    'Investigate suspicious or failed authorization attempts',
                    'Open the living project plan status view (when permitted)',
                    'Typically reserved for Administrator (admin role)',
                ],
            ],
            'tl' => [
                'title' => 'Pagtingin sa audit trail',
                'summary' => 'Basahin ang hindi nababagong tala ng aktibidad para sa ICT at pananagutan.',
                'functions' => [
                    'Tingnan ang authentication at mutation events (aktor, IP, ligtas na metadata)',
                    'Imbestigahan ang kahina-hinala o nabigong authorization',
                    'Buksan ang project plan status (kung pinapayagan)',
                    'Karaniwang para sa Administrator (admin role) lamang',
                ],
            ],
        ],
    ],

    'roles' => [
        'admin' => [
            'permissions' => [
                'departments.manage',
                'forms.manage',
                'audit.view',
                'applications.manage',
                'users.manage',
                'workflow.manage',
                'evaluations.manage',
                'inspections.manage',
                'fees.manage',
                'compliance.manage',
                'records.manage',
            ],
            'en' => [
                'title' => 'Administrator',
                'summary' => 'Full Phase I office control: master data, users, workflow, operations, and audit.',
            ],
            'tl' => [
                'title' => 'Administrator',
                'summary' => 'Buong kontrol sa opisina para sa Phase I: master data, user, workflow, operasyon, at audit.',
            ],
        ],
        'building_official' => [
            'permissions' => [
                'applications.manage',
                'users.manage',
                'workflow.manage',
                'evaluations.manage',
                'inspections.manage',
                'fees.manage',
                'compliance.manage',
                'records.manage',
            ],
            'en' => [
                'title' => 'Building Official',
                'summary' => 'Operational leadership across the permit pipeline; no departments/forms/audit master modules.',
            ],
            'tl' => [
                'title' => 'Building Official',
                'summary' => 'Pamumuno sa operasyon sa buong pipeline ng permit; walang modules ng departments/forms/audit.',
            ],
        ],
        'receiving' => [
            'permissions' => ['applications.manage', 'evaluations.manage'],
            'en' => [
                'title' => 'Receiving',
                'summary' => 'Intake / receiving desk: application lookup and evaluation queue work.',
            ],
            'tl' => [
                'title' => 'Receiving',
                'summary' => 'Tanggapan ng pagtanggap: paghahanap ng aplikasyon at trabaho sa evaluation queue.',
            ],
        ],
        'evaluator' => [
            'permissions' => ['applications.manage', 'evaluations.manage'],
            'en' => [
                'title' => 'Evaluator',
                'summary' => 'Technical evaluation of submitted applications and routing slips.',
            ],
            'tl' => [
                'title' => 'Evaluator',
                'summary' => 'Teknikal na evaluation ng naisumiteng aplikasyon at routing slip.',
            ],
        ],
        'inspector' => [
            'permissions' => ['applications.manage', 'inspections.manage'],
            'en' => [
                'title' => 'Inspector',
                'summary' => 'Field inspection scheduling, forms, and findings.',
            ],
            'tl' => [
                'title' => 'Inspector',
                'summary' => 'Pag-iskedyul ng field inspection, forms, at mga natuklasan.',
            ],
        ],
        'assessor' => [
            'permissions' => ['applications.manage', 'fees.manage'],
            'en' => [
                'title' => 'Assessor',
                'summary' => 'Fee assessment and Orders of Payment.',
            ],
            'tl' => [
                'title' => 'Assessor',
                'summary' => 'Pagtataya ng bayarin at Order of Payment.',
            ],
        ],
        'compliance' => [
            'permissions' => ['applications.manage', 'compliance.manage'],
            'en' => [
                'title' => 'Compliance',
                'summary' => 'Compliance / disapproval notices and appeal decisions.',
            ],
            'tl' => [
                'title' => 'Compliance',
                'summary' => 'Compliance / disapproval notice at desisyon sa apela.',
            ],
        ],
        'records' => [
            'permissions' => ['applications.manage', 'records.manage'],
            'en' => [
                'title' => 'Records',
                'summary' => 'Logbooks, releasing records, and archives.',
            ],
            'tl' => [
                'title' => 'Records',
                'summary' => 'Logbook, tala ng releasing, at archive.',
            ],
        ],
        'staff' => [
            'permissions' => [
                'applications.manage',
                'evaluations.manage',
                'inspections.manage',
                'fees.manage',
                'compliance.manage',
                'records.manage',
            ],
            'en' => [
                'title' => 'Staff (general)',
                'summary' => 'Broad operational privileges across the pipeline without admin master-data modules.',
            ],
            'tl' => [
                'title' => 'Staff (pangkalahatan)',
                'summary' => 'Malawak na pribilehiyo sa operasyon sa pipeline nang walang admin master-data modules.',
            ],
        ],
        'applicant' => [
            'permissions' => [],
            'en' => [
                'title' => 'Applicant',
                'summary' => 'No office privileges — uses the applicant portal only after registration approval.',
            ],
            'tl' => [
                'title' => 'Aplikante',
                'summary' => 'Walang pribilehiyo ng opisina — portal ng aplikante lang pagkatapos maaprubahan ang registration.',
            ],
        ],
    ],
];
