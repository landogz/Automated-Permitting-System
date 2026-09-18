<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\ArchiveRecord;
use App\Models\AuditLog;
use App\Models\ComplianceNotice;
use App\Models\Inspection;
use App\Models\LogbookEntry;
use App\Models\OrderOfPayment;
use App\Models\PermitApplication;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;

final class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function operationalStats(?User $viewer = null): array
    {
        $byStatus = PermitApplication::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $applications = [
            'total' => (int) array_sum($byStatus),
            'by_status' => $byStatus,
            'draft' => (int) ($byStatus['draft'] ?? 0),
            'submitted' => (int) ($byStatus['submitted'] ?? 0),
            'under_evaluation' => (int) ($byStatus['under_evaluation'] ?? 0),
            'for_inspection' => (int) ($byStatus['for_inspection'] ?? 0),
            'for_payment' => (int) ($byStatus['for_payment'] ?? 0),
            'for_releasing' => (int) ($byStatus['for_releasing'] ?? 0),
            'for_compliance' => (int) ($byStatus['for_compliance'] ?? 0),
            'released' => (int) ($byStatus['released'] ?? 0),
            'disapproved' => (int) ($byStatus['disapproved'] ?? 0),
        ];

        $evaluationQueue = $applications['submitted'] + $applications['under_evaluation'];
        $inspectionsScheduled = Inspection::query()->where('status', 'scheduled')->count();
        $openCompliance = ComplianceNotice::query()->whereIn('status', ['issued', 'appealed'])->count();
        $pendingRegistrations = User::query()
            ->role('applicant')
            ->where('approval_status', 'pending')
            ->count();
        $oopIssued = OrderOfPayment::query()->where('status', 'issued')->count();
        $oopPaid = OrderOfPayment::query()->where('status', 'paid_stub')->count();

        return [
            'applications' => $applications,
            'inspections' => [
                'scheduled' => $inspectionsScheduled,
                'completed' => Inspection::query()->where('status', 'completed')->count(),
            ],
            'orders_of_payment' => [
                'issued' => $oopIssued,
                'paid_stub' => $oopPaid,
            ],
            'compliance' => [
                'open_notices' => $openCompliance,
            ],
            'evaluation_queue' => $evaluationQueue,
            'records' => [
                'logbook_entries' => LogbookEntry::query()->count(),
                'archives' => ArchiveRecord::query()->count(),
            ],
            'notifications' => [
                'unread' => UserNotification::query()->whereNull('read_at')->count(),
            ],
            'pending_registrations' => $pendingRegistrations,
            'pipeline' => $this->pipelineStages($applications),
            'attention' => $this->attentionQueues(
                $evaluationQueue,
                $inspectionsScheduled,
                $applications['for_payment'],
                $applications['for_releasing'],
                $openCompliance,
                $pendingRegistrations,
                $oopIssued,
            ),
            'recent_applications' => $this->recentApplications(),
            'recent_activity' => $viewer?->hasRole('admin') ? $this->recentActivity() : [],
            'meta' => [
                'generated_at' => now()->toIso8601String(),
                'office' => 'Office of the City Building Official',
                'lgu' => 'City of San Fernando, Pampanga',
                'can_view_audit' => (bool) $viewer?->hasRole('admin'),
            ],
        ];
    }

    /**
     * @param  array<string, int>  $applications
     * @return list<array{key: string, label: string, count: int, tone: string}>
     */
    private function pipelineStages(array $applications): array
    {
        $stages = [
            ['key' => 'submitted', 'label' => 'Submitted', 'tone' => 'info'],
            ['key' => 'under_evaluation', 'label' => 'Evaluation', 'tone' => 'primary'],
            ['key' => 'for_inspection', 'label' => 'Inspection', 'tone' => 'info'],
            ['key' => 'for_payment', 'label' => 'Payment', 'tone' => 'warning'],
            ['key' => 'for_releasing', 'label' => 'Releasing', 'tone' => 'success'],
            ['key' => 'for_compliance', 'label' => 'Compliance', 'tone' => 'danger'],
            ['key' => 'released', 'label' => 'Released', 'tone' => 'success'],
            ['key' => 'disapproved', 'label' => 'Disapproved', 'tone' => 'danger'],
        ];

        return array_map(static function (array $stage) use ($applications): array {
            return [
                'key' => $stage['key'],
                'label' => $stage['label'],
                'count' => (int) ($applications[$stage['key']] ?? 0),
                'tone' => $stage['tone'],
            ];
        }, $stages);
    }

    /**
     * @return list<array{key: string, label: string, hint: string, count: int, path: string, tone: string, permission: string}>
     */
    private function attentionQueues(
        int $evaluationQueue,
        int $inspectionsScheduled,
        int $forPayment,
        int $forReleasing,
        int $openCompliance,
        int $pendingRegistrations,
        int $oopIssued,
    ): array {
        return [
            [
                'key' => 'evaluation',
                'label' => 'Evaluation queue',
                'hint' => 'Submitted & under evaluation',
                'count' => $evaluationQueue,
                'path' => '/admin/evaluation-queue',
                'tone' => 'amber',
                'permission' => 'evaluations.manage',
            ],
            [
                'key' => 'inspections',
                'label' => 'Inspections due',
                'hint' => 'Scheduled field visits',
                'count' => $inspectionsScheduled,
                'path' => '/admin/inspections',
                'tone' => 'blue',
                'permission' => 'inspections.manage',
            ],
            [
                'key' => 'payment',
                'label' => 'Orders of payment',
                'hint' => 'Awaiting cashier / mark paid',
                'count' => max($forPayment, $oopIssued),
                'path' => '/admin/orders-of-payment',
                'tone' => 'indigo',
                'permission' => 'fees.manage',
            ],
            [
                'key' => 'releasing',
                'label' => 'For releasing',
                'hint' => 'Paid — awaiting G-01 logbook',
                'count' => $forReleasing,
                'path' => '/admin/logbooks',
                'tone' => 'emerald',
                'permission' => 'records.manage',
            ],
            [
                'key' => 'compliance',
                'label' => 'Open compliance',
                'hint' => 'G-03 / G-04 notices & appeals',
                'count' => $openCompliance,
                'path' => '/admin/compliance-notices',
                'tone' => 'rose',
                'permission' => 'compliance.manage',
            ],
            [
                'key' => 'registrations',
                'label' => 'Pending registrations',
                'hint' => 'Applicant accounts awaiting approval',
                'count' => $pendingRegistrations,
                'path' => '/admin/registrations',
                'tone' => 'slate',
                'permission' => 'users.manage',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentApplications(): array
    {
        return PermitApplication::query()
            ->with(['user:id,uuid,name'])
            ->latest('updated_at')
            ->limit(8)
            ->get(['id', 'uuid', 'application_no', 'project_title', 'status', 'classification', 'updated_at', 'user_id'])
            ->map(static fn (PermitApplication $app): array => [
                'uuid' => $app->uuid,
                'application_no' => $app->application_no,
                'project_title' => $app->project_title,
                'status' => $app->status,
                'classification' => $app->classification,
                'applicant_name' => $app->user?->name,
                'updated_at' => $app->updated_at?->toIso8601String(),
            ])
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentActivity(): array
    {
        return AuditLog::query()
            ->latest('id')
            ->limit(10)
            ->get(['uuid', 'event', 'actor_name', 'meta', 'created_at'])
            ->map(static fn (AuditLog $log): array => [
                'uuid' => $log->uuid,
                'event' => $log->event,
                'actor_name' => $log->actor_name,
                'meta' => is_array($log->meta) ? array_intersect_key($log->meta, array_flip([
                    'application_id',
                    'application_no',
                    'oop_no',
                    'entry_no',
                    'notice_no',
                    'user_id',
                    'email',
                ])) : [],
                'created_at' => $log->created_at?->toIso8601String(),
            ])
            ->all();
    }
}
