<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\PermitApplication;
use Illuminate\Validation\ValidationException;

/**
 * Sequential Operations gate:
 * Evaluation → Inspection → Orders of Payment → Compliance (branch) → Releasing (G-01).
 */
final class OperationsWorkflow
{
    public const STEP_EVALUATION = 'evaluation';

    public const STEP_INSPECTION = 'inspection';

    public const STEP_PAYMENT = 'payment';

    public const STEP_COMPLIANCE = 'compliance';

    public const STEP_RELEASING = 'releasing';

    /**
     * Statuses eligible for staff application pickers per operations step.
     *
     * @return list<string>
     */
    public function statusesForStep(string $step): array
    {
        return match ($step) {
            self::STEP_EVALUATION => ['submitted', 'under_evaluation'],
            self::STEP_INSPECTION => ['for_inspection'],
            self::STEP_PAYMENT => ['for_payment'],
            self::STEP_COMPLIANCE => ['for_compliance'],
            self::STEP_RELEASING => ['for_releasing'],
            default => [],
        };
    }

    public function assertCanEvaluate(PermitApplication $application): void
    {
        $this->assertStatus(
            $application,
            $this->statusesForStep(self::STEP_EVALUATION),
            'Only submitted / under-evaluation applications can be evaluated.',
            self::STEP_EVALUATION,
        );
    }

    public function assertCanScheduleInspection(PermitApplication $application): void
    {
        $this->assertStatus(
            $application,
            ['for_inspection'],
            'Complete Evaluation (mark compliant) before scheduling an inspection.',
            self::STEP_EVALUATION,
        );
    }

    /**
     * Attach sequential next-step guidance for SPA toast + redirect.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function withNextStep(array $payload, PermitApplication|string|null $applicationOrStatus): array
    {
        $status = $applicationOrStatus instanceof PermitApplication
            ? (string) $applicationOrStatus->status
            : (string) ($applicationOrStatus ?? '');

        $payload['next_step'] = $this->nextStepMeta($status);

        return $payload;
    }

    public function assertCanGenerateOrder(PermitApplication $application): void
    {
        $this->assertStatus(
            $application,
            ['for_payment'],
            'Complete Inspection (passed/conditional) before generating an Order of Payment.',
            self::STEP_INSPECTION,
        );
    }

    public function assertCanIssueCompliance(PermitApplication $application): void
    {
        $this->assertStatus(
            $application,
            ['for_compliance'],
            'Compliance notices are available after Inspection fails (or evaluation is non-compliant). Finish prior steps first.',
            self::STEP_INSPECTION,
        );
    }

    /**
     * @return array{step: string, label: string, path: string, status: string, message?: string}
     */
    public function nextStepMeta(string $status): array
    {
        return match ($status) {
            'for_inspection' => [
                'step' => self::STEP_INSPECTION,
                'label' => 'Inspections',
                'path' => '/admin/inspections',
                'status' => $status,
            ],
            'for_payment' => [
                'step' => self::STEP_PAYMENT,
                'label' => 'Orders of Payment',
                'path' => '/admin/orders-of-payment',
                'status' => $status,
            ],
            'for_compliance' => [
                'step' => self::STEP_COMPLIANCE,
                'label' => 'Compliance Notices',
                'path' => '/admin/compliance-notices',
                'status' => $status,
            ],
            'for_releasing' => [
                'step' => self::STEP_RELEASING,
                'label' => 'Releasing area (G-01 Logbooks)',
                'path' => '/admin/logbooks',
                'status' => $status,
                'message' => 'Payment cleared — proceed to the Releasing area. Status becomes Released only after G-01 logbook release.',
            ],
            'released' => [
                'step' => self::STEP_RELEASING,
                'label' => 'Released (G-01 recorded)',
                'path' => '/admin/logbooks',
                'status' => $status,
                'message' => 'Permit released via G-01 logbook.',
            ],
            'disapproved' => [
                'step' => self::STEP_COMPLIANCE,
                'label' => 'Compliance Notices',
                'path' => '/admin/compliance-notices',
                'status' => $status,
                'message' => 'Record the outcome in Compliance / Records.',
            ],
            default => [
                'step' => self::STEP_EVALUATION,
                'label' => 'Evaluation Queue',
                'path' => '/admin/evaluation-queue',
                'status' => $status,
            ],
        };
    }

    /**
     * @param  list<string>  $allowed
     */
    private function assertStatus(
        PermitApplication $application,
        array $allowed,
        string $message,
        string $requiredStep,
    ): void {
        $status = (string) $application->status;
        if (in_array($status, $allowed, true)) {
            return;
        }

        throw ValidationException::withMessages([
            'application' => [$message.' Current status: '.$status.'.'],
            'required_step' => [$requiredStep],
            'current_status' => [$status],
        ]);
    }
}
