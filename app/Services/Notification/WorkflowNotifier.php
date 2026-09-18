<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Models\ComplianceAppeal;
use App\Models\ComplianceNotice;
use App\Models\Evaluation;
use App\Models\Inspection;
use App\Models\LogbookEntry;
use App\Models\OrderOfPayment;
use App\Models\PermitApplication;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Domain-level notification fan-out (in-app bell + email) for APICS workflow events.
 * Failures never break the primary business transaction.
 */
final class WorkflowNotifier
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function registrationSubmitted(User $applicant): void
    {
        $this->notifications->safeNotify(
            $applicant,
            'registration.received',
            [
                'name' => $applicant->name,
                'message' => 'Your registration was received and is pending OCBO admin approval.',
            ],
            ['url' => '/login', 'event' => 'registration.submitted'],
            sendEmail: false, // Dedicated RegistrationReceivedMail already sent
        );

        foreach ($this->usersWithPermission('users.manage') as $admin) {
            $this->notifications->safeNotify(
                $admin,
                'registration.pending_admin',
                [
                    'name' => $admin->name,
                    'applicant_name' => $applicant->name,
                    'applicant_email' => $applicant->email,
                    'message' => "New applicant registration: {$applicant->name} ({$applicant->email}).",
                ],
                [
                    'url' => '/admin/registrations',
                    'event' => 'registration.pending_admin',
                    'applicant_uuid' => $applicant->uuid,
                ],
                sendEmail: false, // Dedicated RegistrationPendingAdminMail already sent
            );
        }
    }

    public function registrationApproved(User $applicant): void
    {
        $this->notifications->safeNotify(
            $applicant,
            'registration.approved',
            [
                'name' => $applicant->name,
                'message' => 'Your APICS account was approved. You may now sign in and file applications.',
            ],
            ['url' => '/login', 'event' => 'registration.approved'],
            sendEmail: false,
        );
    }

    public function registrationDeclined(User $applicant, string $reason): void
    {
        $this->notifications->safeNotify(
            $applicant,
            'registration.declined',
            [
                'name' => $applicant->name,
                'message' => 'Your registration was declined. Reason: '.$reason,
                'reason' => $reason,
            ],
            ['url' => '/register', 'event' => 'registration.declined'],
            sendEmail: false,
        );
    }

    public function applicationSubmitted(PermitApplication $application, User $applicant): void
    {
        $appNo = (string) $application->application_no;

        $this->notifications->safeNotify(
            $applicant,
            'application.submitted',
            [
                'name' => $applicant->name,
                'application_no' => $appNo,
                'message' => "Your application {$appNo} was submitted and is awaiting evaluation.",
            ],
            [
                'url' => '/applications',
                'event' => 'application.submitted',
                'application_uuid' => $application->uuid,
            ],
        );

        foreach ($this->usersWithPermission('evaluations.manage') as $staff) {
            if ((int) $staff->id === (int) $applicant->id) {
                continue;
            }
            $this->notifications->safeNotify(
                $staff,
                'staff.application_submitted',
                [
                    'name' => $staff->name,
                    'application_no' => $appNo,
                    'project_title' => (string) ($application->project_title ?? 'Application'),
                    'message' => "New filing {$appNo} is ready for evaluation.",
                ],
                [
                    'url' => '/admin/evaluation-queue',
                    'event' => 'staff.application_submitted',
                    'application_uuid' => $application->uuid,
                ],
            );
        }
    }

    public function documentCorrectionRequested(
        User $applicant,
        PermitApplication $application,
        string $documentLabel,
        string $staffMessage,
    ): void {
        $appNo = (string) $application->application_no;

        $this->notifications->safeNotify(
            $applicant,
            'document.correction_requested',
            [
                'name' => $applicant->name,
                'application_no' => $appNo,
                'document' => $documentLabel,
                'message' => "Please upload or correct “{$documentLabel}” for {$appNo}. {$staffMessage}",
            ],
            [
                'url' => '/applications',
                'event' => 'document.correction_requested',
                'application_uuid' => $application->uuid,
                'document' => $documentLabel,
            ],
        );
    }

    public function applicationClassified(PermitApplication $application): void
    {
        $applicant = $application->user;
        if (! $applicant) {
            return;
        }

        $appNo = (string) $application->application_no;
        $classification = (string) ($application->classification ?? 'classified');

        $this->notifications->safeNotify(
            $applicant,
            'application.classified',
            [
                'name' => $applicant->name,
                'application_no' => $appNo,
                'classification' => $classification,
                'message' => "Application {$appNo} was classified as {$classification} and is under evaluation.",
            ],
            [
                'url' => '/applications',
                'event' => 'application.classified',
                'application_uuid' => $application->uuid,
            ],
        );
    }

    public function evaluationDecided(Evaluation $evaluation): void
    {
        $application = $evaluation->application;
        $applicant = $application?->user;
        if (! $application || ! $applicant) {
            return;
        }

        $appNo = (string) $application->application_no;
        $result = (string) ($evaluation->result?->value ?? $evaluation->result);
        $template = match ($result) {
            'compliant' => 'evaluation.compliant',
            'non_compliant' => 'evaluation.non_compliant',
            default => 'evaluation.needs_info',
        };
        $message = match ($result) {
            'compliant' => "Evaluation for {$appNo} is compliant. Next: inspection scheduling.",
            'non_compliant' => "Evaluation for {$appNo} is non-compliant. Please check compliance instructions.",
            default => "Evaluation for {$appNo} needs additional information. Please update your filing.",
        };

        $this->notifications->safeNotify(
            $applicant,
            $template,
            [
                'name' => $applicant->name,
                'application_no' => $appNo,
                'result' => $result,
                'message' => $message,
            ],
            [
                'url' => '/applications',
                'event' => 'evaluation.decided',
                'application_uuid' => $application->uuid,
                'result' => $result,
            ],
        );

        if ($result === 'compliant') {
            foreach ($this->usersWithPermission('inspections.manage') as $inspector) {
                $this->notifications->safeNotify(
                    $inspector,
                    'staff.ready_for_inspection',
                    [
                        'name' => $inspector->name,
                        'application_no' => $appNo,
                        'message' => "{$appNo} is ready for inspection scheduling.",
                    ],
                    [
                        'url' => '/admin/inspections',
                        'event' => 'staff.ready_for_inspection',
                        'application_uuid' => $application->uuid,
                    ],
                );
            }
        }
    }

    public function inspectionScheduled(Inspection $inspection): void
    {
        $application = $inspection->application;
        $applicant = $application?->user;
        if (! $application || ! $applicant) {
            return;
        }

        $appNo = (string) $application->application_no;
        $when = $inspection->scheduled_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') ?? 'TBA';

        $this->notifications->safeNotify(
            $applicant,
            'inspection.scheduled',
            [
                'name' => $applicant->name,
                'application_no' => $appNo,
                'inspection_no' => (string) $inspection->inspection_no,
                'scheduled_at' => $when,
                'message' => "Inspection {$inspection->inspection_no} for {$appNo} is scheduled on {$when}.",
            ],
            [
                'url' => '/applications',
                'event' => 'inspection.scheduled',
                'application_uuid' => $application->uuid,
                'inspection_uuid' => $inspection->uuid,
            ],
        );
    }

    public function inspectionCompleted(Inspection $inspection): void
    {
        $application = $inspection->application;
        $applicant = $application?->user;
        if (! $application || ! $applicant) {
            return;
        }

        $appNo = (string) $application->application_no;
        $result = (string) ($inspection->result?->value ?? $inspection->result);
        $passed = in_array($result, ['passed', 'conditional'], true);
        $template = $passed ? 'inspection.passed' : 'inspection.failed';
        $message = $passed
            ? "Inspection for {$appNo} passed. Next: Order of Payment."
            : "Inspection for {$appNo} failed. A compliance notice may follow. Notes: ".((string) ($inspection->notes ?? ''));

        $this->notifications->safeNotify(
            $applicant,
            $template,
            [
                'name' => $applicant->name,
                'application_no' => $appNo,
                'result' => $result,
                'message' => $message,
            ],
            [
                'url' => '/applications',
                'event' => 'inspection.completed',
                'application_uuid' => $application->uuid,
                'inspection_uuid' => $inspection->uuid,
                'result' => $result,
            ],
        );

        if ($passed) {
            foreach ($this->usersWithPermission('fees.manage') as $assessor) {
                $this->notifications->safeNotify(
                    $assessor,
                    'staff.ready_for_payment',
                    [
                        'name' => $assessor->name,
                        'application_no' => $appNo,
                        'message' => "{$appNo} passed inspection and is ready for Order of Payment.",
                    ],
                    [
                        'url' => '/admin/orders-of-payment',
                        'event' => 'staff.ready_for_payment',
                        'application_uuid' => $application->uuid,
                    ],
                );
            }
        } else {
            foreach ($this->usersWithPermission('compliance.manage') as $officer) {
                $this->notifications->safeNotify(
                    $officer,
                    'staff.ready_for_compliance',
                    [
                        'name' => $officer->name,
                        'application_no' => $appNo,
                        'message' => "{$appNo} failed inspection — issue G-03/G-04 as needed.",
                    ],
                    [
                        'url' => '/admin/compliance-notices',
                        'event' => 'staff.ready_for_compliance',
                        'application_uuid' => $application->uuid,
                    ],
                );
            }
        }
    }

    public function orderIssued(OrderOfPayment $order): void
    {
        $application = $order->application;
        $applicant = $application?->user;
        if (! $application || ! $applicant) {
            return;
        }

        $appNo = (string) $application->application_no;
        $amount = number_format((float) $order->total_amount, 2);

        $this->notifications->safeNotify(
            $applicant,
            'payment.order_issued',
            [
                'name' => $applicant->name,
                'application_no' => $appNo,
                'oop_no' => (string) $order->oop_no,
                'amount' => $amount,
                'message' => "Order of Payment {$order->oop_no} for {$appNo} was issued (₱{$amount}). Please settle at CTO.",
            ],
            [
                'url' => '/applications',
                'event' => 'payment.order_issued',
                'application_uuid' => $application->uuid,
                'oop_uuid' => $order->uuid,
            ],
        );
    }

    public function orderPaid(OrderOfPayment $order): void
    {
        $application = $order->application;
        $applicant = $application?->user;
        if (! $application || ! $applicant) {
            return;
        }

        $appNo = (string) $application->application_no;

        $this->notifications->safeNotify(
            $applicant,
            'payment.paid',
            [
                'name' => $applicant->name,
                'application_no' => $appNo,
                'oop_no' => (string) $order->oop_no,
                'message' => "Payment for {$order->oop_no} ({$appNo}) was recorded. Status is For Releasing — please proceed to the OCBO Releasing area to claim your permit.",
            ],
            [
                'url' => '/applications',
                'event' => 'payment.paid',
                'application_uuid' => $application->uuid,
                'oop_uuid' => $order->uuid,
            ],
        );

        foreach ($this->usersWithPermission('records.manage') as $records) {
            $this->notifications->safeNotify(
                $records,
                'staff.ready_for_release',
                [
                    'name' => $records->name,
                    'application_no' => $appNo,
                    'message' => "{$appNo} is For Releasing — record G-01 in Logbooks to release the permit.",
                ],
                [
                    'url' => '/admin/logbooks',
                    'event' => 'staff.ready_for_release',
                    'application_uuid' => $application->uuid,
                ],
            );
        }
    }

    public function complianceNoticeIssued(ComplianceNotice $notice): void
    {
        $application = $notice->application;
        $applicant = $application?->user;
        if (! $application || ! $applicant) {
            return;
        }

        $appNo = (string) $application->application_no;
        $type = (string) ($notice->type?->value ?? $notice->type);

        $this->notifications->safeNotify(
            $applicant,
            'compliance.notice_issued',
            [
                'name' => $applicant->name,
                'application_no' => $appNo,
                'notice_no' => (string) $notice->notice_no,
                'title' => (string) $notice->title,
                'message' => "Compliance notice {$notice->notice_no} was issued for {$appNo}: {$notice->title}",
            ],
            [
                'url' => '/applications',
                'event' => 'compliance.notice_issued',
                'application_uuid' => $application->uuid,
                'notice_uuid' => $notice->uuid,
                'type' => $type,
            ],
        );
    }

    public function appealFiled(ComplianceAppeal $appeal): void
    {
        $notice = $appeal->notice;
        $application = $notice?->application;
        if (! $notice || ! $application) {
            return;
        }

        $appNo = (string) $application->application_no;

        foreach ($this->usersWithPermission('compliance.manage') as $officer) {
            if ((int) $officer->id === (int) $appeal->filed_by) {
                continue;
            }

            $this->notifications->safeNotify(
                $officer,
                'staff.appeal_filed',
                [
                    'name' => $officer->name,
                    'application_no' => $appNo,
                    'notice_no' => (string) $notice->notice_no,
                    'message' => "An appeal was filed on notice {$notice->notice_no} ({$appNo}).",
                ],
                [
                    'url' => '/admin/compliance-notices',
                    'event' => 'staff.appeal_filed',
                    'application_uuid' => $application->uuid,
                    'appeal_uuid' => $appeal->uuid,
                ],
            );
        }

        $filer = $appeal->filedByUser;
        if ($filer && ! $filer->can('compliance.manage')) {
            $this->notifications->safeNotify(
                $filer,
                'compliance.appeal_filed',
                [
                    'name' => $filer->name,
                    'application_no' => $appNo,
                    'notice_no' => (string) $notice->notice_no,
                    'message' => "Your appeal on {$notice->notice_no} was received and is pending review.",
                ],
                [
                    'url' => '/applications',
                    'event' => 'compliance.appeal_filed',
                    'application_uuid' => $application->uuid,
                    'appeal_uuid' => $appeal->uuid,
                ],
            );
        }
    }

    public function appealResolved(ComplianceAppeal $appeal): void
    {
        $notice = $appeal->notice;
        $application = $notice?->application;
        $filer = $appeal->filedByUser;
        if (! $filer) {
            return;
        }

        $appNo = (string) ($application?->application_no ?? '');
        $status = (string) ($appeal->status?->value ?? $appeal->status);

        $this->notifications->safeNotify(
            $filer,
            'compliance.appeal_resolved',
            [
                'name' => $filer->name,
                'application_no' => $appNo,
                'notice_no' => (string) ($notice?->notice_no ?? ''),
                'status' => $status,
                'message' => "Your appeal on ".((string) ($notice?->notice_no ?? 'notice'))." was resolved as {$status}.",
            ],
            [
                'url' => '/applications',
                'event' => 'compliance.appeal_resolved',
                'application_uuid' => $application?->uuid,
                'appeal_uuid' => $appeal->uuid,
                'status' => $status,
            ],
        );
    }

    public function permitReleased(LogbookEntry $entry): void
    {
        $application = $entry->application;
        $applicant = $application?->user;
        if (! $application || ! $applicant) {
            return;
        }

        $appNo = (string) $application->application_no;

        $this->notifications->safeNotify(
            $applicant,
            'permit.released',
            [
                'name' => $applicant->name,
                'application_no' => $appNo,
                'entry_no' => (string) $entry->entry_no,
                'message' => "Permit for {$appNo} was recorded for release ({$entry->entry_no}). Please claim as instructed by OCBO Records.",
            ],
            [
                'url' => '/applications',
                'event' => 'permit.released',
                'application_uuid' => $application->uuid,
                'logbook_uuid' => $entry->uuid,
            ],
        );
    }

    /**
     * @return Collection<int, User>
     */
    private function usersWithPermission(string $permission): Collection
    {
        return User::query()
            ->permission($permission)
            ->where('is_active', true)
            ->whereNotNull('email')
            ->get();
    }
}
