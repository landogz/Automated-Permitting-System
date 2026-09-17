<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\NotificationChannel;
use App\Models\NotificationTemplate;
use App\Models\NumberingSeries;
use Illuminate\Database\Seeder;

class RecordsNotificationsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (
            [
                'logbook_g01_releasing' => ['prefix' => 'G01-'.date('Y').'-', 'next' => 1],
                'logbook_o02_occupancy' => ['prefix' => 'O02-'.date('Y').'-', 'next' => 1],
                'logbook_e_series' => ['prefix' => 'E-'.date('Y').'-', 'next' => 1],
                'logbook_g05' => ['prefix' => 'G05-'.date('Y').'-', 'next' => 1],
                'logbook_g06' => ['prefix' => 'G06-'.date('Y').'-', 'next' => 1],
                'archive_record' => ['prefix' => 'ARC-'.date('Y').'-', 'next' => 1],
            ] as $key => $cfg
        ) {
            NumberingSeries::query()->firstOrCreate(
                ['key' => $key],
                [
                    'prefix' => $cfg['prefix'],
                    'next_number' => $cfg['next'],
                    'pad_length' => 6,
                ]
            );
        }

        $templates = [
            'status.update' => [
                'name' => 'Application status update',
                'subject' => 'APICS update — {{name}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\nPlease sign in to APICS for details.\n\n— OCBO / CSFP",
            ],
            'status.update.email' => [
                'name' => 'Application status update (legacy email)',
                'subject' => 'APICS status update — {{name}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\nPlease sign in to APICS for details.\n\n— Office of the City Building Official",
            ],
            'registration.received' => [
                'name' => 'Registration received',
                'subject' => 'Registration received — APICS',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— OCBO / CSFP",
            ],
            'registration.pending_admin' => [
                'name' => 'Registration pending (admin)',
                'subject' => 'New registration pending approval',
                'body' => "Hello {{name}},\n\n{{message}}\n\nReview at Admin → Registrations.\n\n— APICS",
            ],
            'registration.approved' => [
                'name' => 'Registration approved',
                'subject' => 'Your APICS account was approved',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— OCBO / CSFP",
            ],
            'registration.declined' => [
                'name' => 'Registration declined',
                'subject' => 'APICS registration update',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— OCBO / CSFP",
            ],
            'application.submitted' => [
                'name' => 'Application submitted',
                'subject' => 'Application {{application_no}} submitted',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— OCBO / CSFP",
            ],
            'staff.application_submitted' => [
                'name' => 'Staff — new application',
                'subject' => 'New filing {{application_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\nOpen Evaluation Queue in APICS.\n\n— APICS",
            ],
            'document.correction_requested' => [
                'name' => 'Document correction requested',
                'subject' => 'Document request — {{application_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\nSign in to My Applications to upload the corrected file.\n\n— OCBO / CSFP",
            ],
            'application.classified' => [
                'name' => 'Application classified',
                'subject' => 'Application {{application_no}} classified',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— OCBO / CSFP",
            ],
            'evaluation.compliant' => [
                'name' => 'Evaluation compliant',
                'subject' => 'Evaluation result — {{application_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— OCBO / CSFP",
            ],
            'evaluation.non_compliant' => [
                'name' => 'Evaluation non-compliant',
                'subject' => 'Evaluation result — {{application_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— OCBO / CSFP",
            ],
            'evaluation.needs_info' => [
                'name' => 'Evaluation needs information',
                'subject' => 'Additional information needed — {{application_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— OCBO / CSFP",
            ],
            'staff.ready_for_inspection' => [
                'name' => 'Staff — ready for inspection',
                'subject' => 'Ready for inspection — {{application_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— APICS",
            ],
            'inspection.scheduled' => [
                'name' => 'Inspection scheduled',
                'subject' => 'Inspection scheduled — {{application_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— OCBO / CSFP",
            ],
            'inspection.passed' => [
                'name' => 'Inspection passed',
                'subject' => 'Inspection passed — {{application_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— OCBO / CSFP",
            ],
            'inspection.failed' => [
                'name' => 'Inspection failed',
                'subject' => 'Inspection failed — {{application_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— OCBO / CSFP",
            ],
            'staff.ready_for_payment' => [
                'name' => 'Staff — ready for OoP',
                'subject' => 'Ready for Order of Payment — {{application_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— APICS",
            ],
            'staff.ready_for_compliance' => [
                'name' => 'Staff — ready for compliance',
                'subject' => 'Compliance action needed — {{application_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— APICS",
            ],
            'payment.order_issued' => [
                'name' => 'Order of Payment issued',
                'subject' => 'Order of Payment {{oop_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— OCBO / CSFP",
            ],
            'payment.paid' => [
                'name' => 'Payment recorded',
                'subject' => 'Payment recorded — {{application_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— OCBO / CSFP",
            ],
            'staff.ready_for_release' => [
                'name' => 'Staff — ready for release',
                'subject' => 'Ready for release — {{application_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— APICS",
            ],
            'compliance.notice_issued' => [
                'name' => 'Compliance notice issued',
                'subject' => 'Compliance notice {{notice_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\nYou may view details and file an appeal in My Applications.\n\n— OCBO / CSFP",
            ],
            'compliance.appeal_filed' => [
                'name' => 'Appeal filed (applicant)',
                'subject' => 'Appeal received — {{notice_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— OCBO / CSFP",
            ],
            'staff.appeal_filed' => [
                'name' => 'Staff — appeal filed',
                'subject' => 'Appeal filed — {{notice_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— APICS",
            ],
            'compliance.appeal_resolved' => [
                'name' => 'Appeal resolved',
                'subject' => 'Appeal resolved — {{notice_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— OCBO / CSFP",
            ],
            'permit.released' => [
                'name' => 'Permit released',
                'subject' => 'Permit ready / released — {{application_no}}',
                'body' => "Hello {{name}},\n\n{{message}}\n\n— OCBO / CSFP",
            ],
            'release.ready' => [
                'name' => 'Permit ready for release (legacy)',
                'subject' => 'Permit ready — {{name}}',
                'body' => "Hello {{name}},\n\nYour permit is ready for release. {{message}}",
            ],
        ];

        foreach ($templates as $code => $cfg) {
            NotificationTemplate::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $cfg['name'],
                    'channel' => NotificationChannel::InApp->value,
                    'subject' => $cfg['subject'],
                    'body_template' => $cfg['body'],
                    'is_active' => true,
                ]
            );
        }
    }
}
