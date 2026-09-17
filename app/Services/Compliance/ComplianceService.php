<?php

declare(strict_types=1);

namespace App\Services\Compliance;

use App\Enums\AppealStatus;
use App\Enums\ComplianceNoticeStatus;
use App\Enums\ComplianceNoticeType;
use App\Enums\InspectionResult;
use App\Models\ComplianceAppeal;
use App\Models\ComplianceNotice;
use App\Models\Inspection;
use App\Models\NumberingSeries;
use App\Models\PermitApplication;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Notification\WorkflowNotifier;
use App\Services\Operations\OperationsWorkflow;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ComplianceService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly OperationsWorkflow $operations,
        private readonly WorkflowNotifier $notifier,
    ) {
    }

    public function listNotices(
        string $search = '',
        ?string $type = null,
        ?string $status = null,
        int $perPage = 25,
        ?string $bucket = null,
    ): LengthAwarePaginator {
        return ComplianceNotice::query()
            ->with([
                'application:id,uuid,application_no,project_title,project_location,status,classification,payload',
                'inspection:id,uuid,inspection_no,type,result,status',
                'issuedByUser:id,uuid,name',
                'appeals' => fn ($q) => $q->with(['filedByUser:id,uuid,name', 'resolvedByUser:id,uuid,name'])->latest('id'),
            ])
            ->when($type !== null && $type !== '' && $type !== 'all', fn ($q) => $q->where('type', $type))
            ->when($bucket === 'active', function ($q): void {
                $q->whereIn('status', [
                    ComplianceNoticeStatus::Issued->value,
                    ComplianceNoticeStatus::Appealed->value,
                ]);
            })
            ->when($bucket === 'completed', fn ($q) => $q->where('status', ComplianceNoticeStatus::Closed->value))
            ->when(
                ($bucket === null || $bucket === '')
                    && $status !== null
                    && $status !== ''
                    && $status !== 'all',
                fn ($q) => $q->where('status', $status),
            )
            ->when($search !== '', function ($q) use ($search): void {
                $like = '%'.$search.'%';
                $q->where(function ($inner) use ($like): void {
                    $inner->where('notice_no', 'like', $like)
                        ->orWhere('title', 'like', $like)
                        ->orWhere('body', 'like', $like)
                        ->orWhereHas('application', function ($app) use ($like): void {
                            $app->where('application_no', 'like', $like)
                                ->orWhere('project_title', 'like', $like);
                        });
                });
            })
            ->latest('id')
            ->paginate(min(max($perPage, 1), 100));
    }

    /**
     * Dashboard-style counts for the compliance operations page.
     *
     * @return array{
     *     issued: int,
     *     appealed: int,
     *     closed: int,
     *     g03: int,
     *     g04: int,
     *     g04_active: int,
     *     g04_archived: int,
     *     total: int
     * }
     */
    public function noticeSummary(): array
    {
        $g04Active = ComplianceNotice::query()
            ->where('type', ComplianceNoticeType::G04Disapproval->value)
            ->whereIn('status', [
                ComplianceNoticeStatus::Issued->value,
                ComplianceNoticeStatus::Appealed->value,
            ])
            ->count();
        $g04Archived = ComplianceNotice::query()
            ->where('type', ComplianceNoticeType::G04Disapproval->value)
            ->where('status', ComplianceNoticeStatus::Closed->value)
            ->count();

        return [
            'issued' => ComplianceNotice::query()->where('status', ComplianceNoticeStatus::Issued->value)->count(),
            'appealed' => ComplianceNotice::query()->where('status', ComplianceNoticeStatus::Appealed->value)->count(),
            'closed' => ComplianceNotice::query()->where('status', ComplianceNoticeStatus::Closed->value)->count(),
            'g03' => ComplianceNotice::query()->where('type', ComplianceNoticeType::G03Compliance->value)->count(),
            'g04' => $g04Active,
            'g04_active' => $g04Active,
            'g04_archived' => $g04Archived,
            'total' => ComplianceNotice::query()->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function issue(PermitApplication $application, User $actor, array $data): ComplianceNotice
    {
        $this->operations->assertCanIssueCompliance($application);

        $notice = DB::transaction(function () use ($application, $actor, $data): ComplianceNotice {
            $type = ComplianceNoticeType::from((string) $data['type']);
            $inspection = null;
            if (! empty($data['inspection_uuid'])) {
                $inspection = Inspection::query()->where('uuid', $data['inspection_uuid'])->first();
                if (! $inspection) {
                    throw ValidationException::withMessages([
                        'inspection_uuid' => ['Inspection not found.'],
                    ]);
                }
            } else {
                // Prefer the latest failed inspection so the notice stays linked to site findings.
                $inspection = Inspection::query()
                    ->where('permit_application_id', $application->id)
                    ->where('result', InspectionResult::Failed->value)
                    ->latest('completed_at')
                    ->first();
            }

            $prefix = $type === ComplianceNoticeType::G03Compliance
                ? 'G03-'.date('Y').'-'
                : 'G04-'.date('Y').'-';
            $seriesKey = $type === ComplianceNoticeType::G03Compliance ? 'notice_g03' : 'notice_g04';

            $notice = ComplianceNotice::query()->create([
                'permit_application_id' => $application->id,
                'inspection_id' => $inspection?->id,
                'notice_no' => $this->nextNumber($seriesKey, $prefix),
                'type' => $type->value,
                'status' => ComplianceNoticeStatus::Issued->value,
                'title' => $data['title'],
                'body' => $data['body'],
                'issued_by' => $actor->id,
                'issued_at' => now(),
                'due_at' => $data['due_at'] ?? now()->addDays(15),
            ]);

            $application->update([
                'status' => $type === ComplianceNoticeType::G04Disapproval
                    ? 'disapproved'
                    : 'for_compliance',
            ]);

            $this->audit->log('compliance_notice.issued', [
                'notice_id' => $notice->uuid,
                'notice_no' => $notice->notice_no,
                'type' => $type->value,
                'application_id' => $application->uuid,
            ]);

            return $notice->load(['application.user', 'inspection', 'issuedByUser']);
        });

        $this->notifier->complianceNoticeIssued($notice);

        return $notice;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function fileAppeal(ComplianceNotice $notice, User $filer, array $data): ComplianceAppeal
    {
        if ($notice->status !== ComplianceNoticeStatus::Issued) {
            throw ValidationException::withMessages([
                'notice' => ['Only issued notices can be appealed.'],
            ]);
        }

        $notice->loadMissing('application');
        $isStaff = $filer->can('compliance.manage');
        $isOwner = (int) ($notice->application?->user_id ?? 0) === (int) $filer->id;

        if (! $isStaff && ! $isOwner) {
            throw ValidationException::withMessages([
                'notice' => ['You are not allowed to appeal this notice.'],
            ]);
        }

        $appeal = DB::transaction(function () use ($notice, $filer, $data): ComplianceAppeal {
            $appeal = ComplianceAppeal::query()->create([
                'compliance_notice_id' => $notice->id,
                'filed_by' => $filer->id,
                'status' => AppealStatus::Pending->value,
                'grounds' => $data['grounds'],
            ]);

            $notice->update(['status' => ComplianceNoticeStatus::Appealed->value]);

            $this->audit->log('compliance_appeal.filed', [
                'appeal_id' => $appeal->uuid,
                'notice_id' => $notice->uuid,
                'filed_by_applicant' => ! $filer->can('compliance.manage'),
            ]);

            return $appeal->load(['notice.application', 'filedByUser']);
        });

        $this->notifier->appealFiled($appeal);

        return $appeal;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function resolveAppeal(ComplianceAppeal $appeal, User $resolver, array $data): ComplianceAppeal
    {
        if ($appeal->status !== AppealStatus::Pending) {
            throw ValidationException::withMessages([
                'appeal' => ['Appeal already resolved.'],
            ]);
        }

        $resolved = DB::transaction(function () use ($appeal, $resolver, $data): ComplianceAppeal {
            $status = AppealStatus::from((string) $data['status']);

            $appeal->fill([
                'status' => $status->value,
                'resolution_notes' => $data['resolution_notes'] ?? null,
                'resolved_by' => $resolver->id,
                'resolved_at' => now(),
            ]);
            $appeal->save();

            $notice = $appeal->notice;
            if ($notice) {
                $notice->update([
                    'status' => $status === AppealStatus::Upheld
                        ? ComplianceNoticeStatus::Closed->value
                        : ComplianceNoticeStatus::Issued->value,
                ]);

                if ($status === AppealStatus::Upheld && $notice->application) {
                    $notice->application->update(['status' => 'under_evaluation']);
                }
            }

            $this->audit->log('compliance_appeal.resolved', [
                'appeal_id' => $appeal->uuid,
                'status' => $status->value,
            ]);

            return $appeal->fresh(['notice.application', 'filedByUser', 'resolvedByUser']) ?? $appeal;
        });

        $this->notifier->appealResolved($resolved);

        return $resolved;
    }

    private function nextNumber(string $key, string $prefix): string
    {
        $series = NumberingSeries::query()->where('key', $key)->lockForUpdate()->first();
        if (! $series) {
            NumberingSeries::query()->create([
                'key' => $key,
                'prefix' => $prefix,
                'next_number' => 1,
                'pad_length' => 6,
            ]);
            $series = NumberingSeries::query()->where('key', $key)->lockForUpdate()->firstOrFail();
        }

        $number = $series->prefix.str_pad((string) $series->next_number, $series->pad_length, '0', STR_PAD_LEFT);
        $series->update(['next_number' => $series->next_number + 1]);

        return $number;
    }
}
