<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\ArchiveRecord;
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
    public function operationalStats(): array
    {
        $byStatus = PermitApplication::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return [
            'applications' => [
                'total' => (int) array_sum($byStatus),
                'by_status' => $byStatus,
                'submitted' => (int) ($byStatus['submitted'] ?? 0),
                'under_evaluation' => (int) ($byStatus['under_evaluation'] ?? 0),
                'for_inspection' => (int) ($byStatus['for_inspection'] ?? 0),
                'for_payment' => (int) ($byStatus['for_payment'] ?? 0),
                'released' => (int) ($byStatus['released'] ?? 0),
                'disapproved' => (int) ($byStatus['disapproved'] ?? 0),
            ],
            'inspections' => [
                'scheduled' => Inspection::query()->where('status', 'scheduled')->count(),
                'completed' => Inspection::query()->where('status', 'completed')->count(),
            ],
            'orders_of_payment' => [
                'issued' => OrderOfPayment::query()->where('status', 'issued')->count(),
                'paid_stub' => OrderOfPayment::query()->where('status', 'paid_stub')->count(),
            ],
            'compliance' => [
                'open_notices' => ComplianceNotice::query()->whereIn('status', ['issued', 'appealed'])->count(),
            ],
            'evaluation_queue' => (int) (($byStatus['submitted'] ?? 0) + ($byStatus['under_evaluation'] ?? 0)),
            'records' => [
                'logbook_entries' => LogbookEntry::query()->count(),
                'archives' => ArchiveRecord::query()->count(),
            ],
            'notifications' => [
                'unread' => UserNotification::query()->whereNull('read_at')->count(),
            ],
            'pending_registrations' => User::query()
                ->role('applicant')
                ->where('approval_status', 'pending')
                ->count(),
        ];
    }
}
