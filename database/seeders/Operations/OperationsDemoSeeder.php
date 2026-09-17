<?php

declare(strict_types=1);

namespace Database\Seeders\Operations;

use App\Enums\ComplianceNoticeStatus;
use App\Enums\ComplianceNoticeType;
use App\Enums\EvaluationResult;
use App\Enums\FeeAgency;
use App\Enums\InspectionResult;
use App\Enums\InspectionStatus;
use App\Enums\OrderOfPaymentStatus;
use App\Models\ComplianceNotice;
use App\Models\Evaluation;
use App\Models\FeeRule;
use App\Models\Inspection;
use App\Models\NumberingSeries;
use App\Models\OrderOfPayment;
use App\Models\PermitApplication;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds evaluation / inspection / OoP / compliance rows aligned with OperationsWorkflow gates.
 */
class OperationsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $evaluator = User::query()->where('email', 'evaluator@csfp.local')->first();
        $inspector = User::query()->where('email', 'inspector@csfp.local')->first();
        $assessor = User::query()->where('email', 'assessor@csfp.local')->first();
        $compliance = User::query()->where('email', 'compliance@csfp.local')->first();

        if (! $evaluator || ! $inspector || ! $assessor || ! $compliance) {
            return;
        }

        $this->seedEvaluation($evaluator, 'Neighborhood Clinic Fit-Out', EvaluationResult::Compliant);
        $this->seedEvaluation($evaluator, 'School Covered Court', EvaluationResult::Compliant);
        $this->seedEvaluation($evaluator, 'Office Fit-Out Plaza Miranda', EvaluationResult::Compliant);
        $this->seedEvaluation($evaluator, 'Retail Strip Mall Annex', EvaluationResult::Compliant);
        $this->seedEvaluation($evaluator, 'Hardware Store Renovation', EvaluationResult::Compliant);
        $this->seedEvaluation($evaluator, 'Barangay Hall Extension', EvaluationResult::Compliant);

        $this->seedScheduledInspection($inspector, 'School Covered Court');

        $passedOffice = $this->seedCompletedInspection(
            $inspector,
            'Office Fit-Out Plaza Miranda',
            InspectionResult::Passed,
        );
        $passedRetail = $this->seedCompletedInspection(
            $inspector,
            'Retail Strip Mall Annex',
            InspectionResult::Passed,
        );
        $failedHardware = $this->seedCompletedInspection(
            $inspector,
            'Hardware Store Renovation',
            InspectionResult::Failed,
        );
        $passedHall = $this->seedCompletedInspection(
            $inspector,
            'Barangay Hall Extension',
            InspectionResult::Passed,
        );

        $this->seedIssuedOrder($assessor, 'Retail Strip Mall Annex');
        $this->seedPaidOrder($assessor, 'Barangay Hall Extension');

        if ($failedHardware) {
            $this->seedComplianceNotice($compliance, 'Hardware Store Renovation', $failedHardware);
        }

        $this->dedupeOpenInspections();

        unset($passedOffice, $passedRetail, $passedHall);
    }

    /**
     * Ensure at most one scheduled/in-progress inspection remains per application.
     */
    private function dedupeOpenInspections(): void
    {
        $applicationIds = Inspection::query()
            ->whereIn('status', [
                InspectionStatus::Scheduled->value,
                InspectionStatus::InProgress->value,
            ])
            ->select('permit_application_id')
            ->groupBy('permit_application_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('permit_application_id');

        foreach ($applicationIds as $applicationId) {
            $keeperId = Inspection::query()
                ->where('permit_application_id', $applicationId)
                ->whereIn('status', [
                    InspectionStatus::Scheduled->value,
                    InspectionStatus::InProgress->value,
                ])
                ->orderBy('id')
                ->value('id');

            if (! $keeperId) {
                continue;
            }

            Inspection::query()
                ->where('permit_application_id', $applicationId)
                ->whereIn('status', [
                    InspectionStatus::Scheduled->value,
                    InspectionStatus::InProgress->value,
                ])
                ->where('id', '!=', $keeperId)
                ->delete();
        }
    }

    private function seedEvaluation(User $evaluator, string $projectTitle, EvaluationResult $result): void
    {
        $application = $this->findApplication($projectTitle);
        if (! $application) {
            return;
        }

        $exists = Evaluation::query()
            ->where('permit_application_id', $application->id)
            ->where('status', 'decided')
            ->exists();

        if ($exists) {
            return;
        }

        Evaluation::query()->create([
            'permit_application_id' => $application->id,
            'evaluator_id' => $evaluator->id,
            'status' => 'decided',
            'result' => $result->value,
            'findings' => [['item' => 'Documentary completeness', 'status' => 'ok']],
            'remarks' => 'Seed evaluation — '.$result->value,
            'decided_at' => now()->subDays(3),
        ]);
    }

    private function seedScheduledInspection(User $inspector, string $projectTitle): void
    {
        $application = $this->findApplication($projectTitle);
        if (! $application || $application->status !== 'for_inspection') {
            return;
        }

        // Keep a single open inspection per application (no discipline fan-out duplicates).
        $open = Inspection::query()
            ->where('permit_application_id', $application->id)
            ->whereIn('status', [
                InspectionStatus::Scheduled->value,
                InspectionStatus::InProgress->value,
            ])
            ->orderBy('id')
            ->get();

        if ($open->isNotEmpty()) {
            $keeper = $open->first();
            Inspection::query()
                ->where('permit_application_id', $application->id)
                ->whereIn('status', [
                    InspectionStatus::Scheduled->value,
                    InspectionStatus::InProgress->value,
                ])
                ->where('id', '!=', $keeper->id)
                ->delete();

            return;
        }

        Inspection::query()->create([
            'permit_application_id' => $application->id,
            'inspection_no' => $this->nextNumber('inspection', 'IN-'.date('Y').'-'),
            'type' => 'joint_structural',
            'status' => InspectionStatus::Scheduled->value,
            'scheduled_at' => now()->addDays(2)->seconds(0),
            'inspector_id' => $inspector->id,
            'scheduled_by' => $inspector->id,
            'location' => $application->project_location,
            'notes' => 'Seed scheduled joint inspection',
            'compliance_sheet' => [],
            'electrical_form' => [],
        ]);
    }

    private function seedCompletedInspection(
        User $inspector,
        string $projectTitle,
        InspectionResult $result,
    ): ?Inspection {
        $application = $this->findApplication($projectTitle);
        if (! $application) {
            return null;
        }

        $existing = Inspection::query()
            ->where('permit_application_id', $application->id)
            ->where('status', InspectionStatus::Completed->value)
            ->first();

        if ($existing) {
            return $existing;
        }

        return Inspection::query()->create([
            'permit_application_id' => $application->id,
            'inspection_no' => $this->nextNumber('inspection', 'IN-'.date('Y').'-'),
            'type' => 'joint_structural',
            'status' => InspectionStatus::Completed->value,
            'result' => $result->value,
            'scheduled_at' => now()->subDays(4),
            'completed_at' => now()->subDays(2),
            'inspector_id' => $inspector->id,
            'scheduled_by' => $inspector->id,
            'location' => $application->project_location,
            'notes' => 'Seed completed inspection — '.$result->value,
            'compliance_sheet' => [
                ['item' => 'Site conditions', 'status' => $result === InspectionResult::Failed ? 'fail' : 'ok'],
            ],
            'electrical_form' => ['form' => '77-006-E', 'status' => $result === InspectionResult::Failed ? 'fail' : 'ok'],
        ]);
    }

    private function seedIssuedOrder(User $assessor, string $projectTitle): void
    {
        $application = $this->findApplication($projectTitle);
        if (! $application || $application->status !== 'for_payment') {
            return;
        }

        if (OrderOfPayment::query()->where('permit_application_id', $application->id)->exists()) {
            return;
        }

        $lines = $this->feeLines($application);
        $total = collect($lines)->sum('amount');

        $order = OrderOfPayment::query()->create([
            'permit_application_id' => $application->id,
            'oop_no' => $this->nextNumber('order_of_payment', 'G02-'.date('Y').'-'),
            'status' => OrderOfPaymentStatus::Issued->value,
            'total_amount' => $total,
            'assessed_by' => $assessor->id,
            'issued_at' => now()->subDay(),
            'cto_stub_reference' => 'CTO-SEED-'.strtoupper(substr(md5($projectTitle), 0, 8)),
        ]);

        foreach ($lines as $index => $line) {
            $order->lines()->create([
                'fee_rule_id' => $line['fee_rule_id'],
                'agency' => $line['agency'],
                'description' => $line['description'],
                'amount' => $line['amount'],
                'external_stub_reference' => $line['external_stub_reference'],
                'line_order' => $index + 1,
            ]);
        }
    }

    private function seedPaidOrder(User $assessor, string $projectTitle): void
    {
        $application = $this->findApplication($projectTitle);
        if (! $application || $application->status !== 'released') {
            return;
        }

        if (OrderOfPayment::query()->where('permit_application_id', $application->id)->exists()) {
            return;
        }

        $lines = $this->feeLines($application);
        $total = collect($lines)->sum('amount');

        $order = OrderOfPayment::query()->create([
            'permit_application_id' => $application->id,
            'oop_no' => $this->nextNumber('order_of_payment', 'G02-'.date('Y').'-'),
            'status' => OrderOfPaymentStatus::PaidStub->value,
            'total_amount' => $total,
            'assessed_by' => $assessor->id,
            'issued_at' => now()->subDays(5),
            'paid_at' => now()->subDays(3),
            'payment_reference' => 'PAY-SEED-'.strtoupper(substr(md5($projectTitle), 0, 8)),
            'cto_stub_reference' => 'CTO-SEED-'.strtoupper(substr(md5($projectTitle.'cto'), 0, 8)),
        ]);

        foreach ($lines as $index => $line) {
            $order->lines()->create([
                'fee_rule_id' => $line['fee_rule_id'],
                'agency' => $line['agency'],
                'description' => $line['description'],
                'amount' => $line['amount'],
                'external_stub_reference' => $line['external_stub_reference'],
                'line_order' => $index + 1,
            ]);
        }
    }

    private function seedComplianceNotice(User $officer, string $projectTitle, Inspection $inspection): void
    {
        $application = $this->findApplication($projectTitle);
        if (! $application || $application->status !== 'for_compliance') {
            return;
        }

        if (ComplianceNotice::query()->where('permit_application_id', $application->id)->exists()) {
            return;
        }

        ComplianceNotice::query()->create([
            'permit_application_id' => $application->id,
            'inspection_id' => $inspection->id,
            'notice_no' => $this->nextNumber('notice_g03', 'G03-'.date('Y').'-'),
            'type' => ComplianceNoticeType::G03Compliance->value,
            'status' => ComplianceNoticeStatus::Issued->value,
            'title' => 'Notice of Compliance — seed demo',
            'body' => 'Seeded G-03 notice. Correct cited deficiencies and request re-inspection.',
            'issued_by' => $officer->id,
            'issued_at' => now()->subDay(),
            'due_at' => now()->addDays(15),
        ]);
    }

    private function findApplication(string $projectTitle): ?PermitApplication
    {
        return PermitApplication::query()->where('project_title', $projectTitle)->first();
    }

    /**
     * @return list<array{fee_rule_id: int|null, agency: string, description: string, amount: float, external_stub_reference: string|null}>
     */
    private function feeLines(PermitApplication $application): array
    {
        $payload = is_array($application->payload) ? $application->payload : [];
        $lotArea = (float) ($payload['lot_area'] ?? 0);
        $lines = [];

        foreach (FeeRule::query()->where('is_active', true)->orderBy('priority')->get() as $rule) {
            $agency = $rule->agency instanceof FeeAgency ? $rule->agency->value : (string) $rule->agency;
            $amount = $rule->basis === 'area_rate'
                ? round(max($lotArea, 0) * (float) $rule->rate, 2)
                : round((float) $rule->amount, 2);

            $stub = null;
            if ($agency === FeeAgency::Bfp->value) {
                $stub = 'BFP-SEED-'.strtoupper(substr(md5($application->uuid.$rule->code), 0, 6));
            } elseif ($agency === FeeAgency::Dpwh->value) {
                $stub = 'DPWH-SEED-'.strtoupper(substr(md5($application->uuid.$rule->code), 0, 6));
            }

            $lines[] = [
                'fee_rule_id' => $rule->id,
                'agency' => $agency,
                'description' => $rule->name,
                'amount' => $amount,
                'external_stub_reference' => $stub,
            ];
        }

        if ($lines === []) {
            $lines[] = [
                'fee_rule_id' => null,
                'agency' => FeeAgency::Lgu->value,
                'description' => 'Seed base fee',
                'amount' => 1500.0,
                'external_stub_reference' => null,
            ];
        }

        return $lines;
    }

    private function nextNumber(string $key, string $prefix): string
    {
        $series = NumberingSeries::query()->where('key', $key)->lockForUpdate()->first();
        if (! $series) {
            $series = NumberingSeries::query()->create([
                'key' => $key,
                'prefix' => $prefix,
                'next_number' => 1,
                'pad_length' => 6,
            ]);
        }

        $number = $series->prefix.str_pad((string) $series->next_number, $series->pad_length, '0', STR_PAD_LEFT);
        $series->update(['next_number' => $series->next_number + 1]);

        return $number;
    }
}
