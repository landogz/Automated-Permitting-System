<?php

declare(strict_types=1);

/**
 * Fallback mirror of `.cursor/plans/apics_phase_i_plan_5d06a183.plan.md` todos.
 * The admin dashboard prefers the markdown plan file; this config is used only
 * if the markdown file is missing or unreadable.
 */
return [
    'project' => 'APICS Phase I — OCBO, City of San Fernando, Pampanga',
    'plan_markdown' => '.cursor/plans/apics_phase_i_plan_5d06a183.plan.md',
    'updated_at' => '2026-09-17',

    'phases' => [
        [
            'id' => 'docs-foundation',
            'title' => 'Docs, rules & foundation',
            'status' => 'completed',
            'items' => [
                ['id' => 'docs-suite', 'label' => 'Create docs/ suite (charter, phases, flows, admin catalog, API, security, UAT, deploy, manuals, KT, roadmap, SLA) and update README', 'status' => 'completed'],
                ['id' => 'plan-sync-rule', 'label' => 'Add alwaysApply Cursor rule — after every completed function/feature, update this project plan (todos, status, changelog) before saying done', 'status' => 'completed'],
                ['id' => 'foundation', 'label' => 'Foundation: Sanctum auth, RBAC, AuditLogger, API envelope, Admin shell, reusable UI, import/export framework', 'status' => 'completed'],
            ],
        ],
        [
            'id' => 'intake',
            'title' => 'Application intake & forms',
            'status' => 'completed',
            'items' => [
                ['id' => 'forms-intake', 'label' => 'Dynamic Forms Engine + Online Application/Intake + uploads + Citizens Portal adapter stub + tracking', 'status' => 'completed'],
            ],
        ],
        [
            'id' => 'registration',
            'title' => 'Registration approval',
            'status' => 'completed',
            'items' => [
                ['id' => 'registration-approval', 'label' => 'Applicant self-registration; Admin approve/decline queue; emails on submitted/approved/declined; block login until approved', 'status' => 'completed'],
            ],
        ],
        [
            'id' => 'core-modules',
            'title' => 'Core OCBO modules',
            'status' => 'completed',
            'items' => [
                ['id' => 'classifier-eval', 'label' => 'Permit Classifier + Evaluation/Routing slips + time tracking + Admin workflow config', 'status' => 'completed'],
                ['id' => 'inspection-fees-compliance', 'label' => 'Inspection scheduling/notes + Order of Payment/fee engine + CTO/BFP/DPWH stubs + G-03/G-04 compliance', 'status' => 'completed'],
                ['id' => 'records-notif', 'label' => 'Records/Archiving logbooks + Notifications + dashboards + PDF/print polish', 'status' => 'completed'],
            ],
        ],
        [
            'id' => 'assure-deploy',
            'title' => 'QA, UAT & go-live',
            'status' => 'in_progress',
            'items' => [
                ['id' => 'qa-uat-deploy', 'label' => 'Automated tests, security review, device-matrix QA, UAT, staging, training/KT, production go-live + warranty process', 'status' => 'pending'],
            ],
        ],
    ],
];
