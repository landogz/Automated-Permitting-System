<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Compliance\FileAppealRequest;
use App\Http\Requests\Compliance\IssueComplianceNoticeRequest;
use App\Http\Requests\Compliance\ResolveAppealRequest;
use App\Http\Requests\Fee\GenerateOrderOfPaymentRequest;
use App\Http\Requests\Inspection\CompleteInspectionRequest;
use App\Http\Requests\Inspection\ScheduleInspectionRequest;
use App\Http\Resources\ComplianceNoticeResource;
use App\Http\Resources\InspectionResource;
use App\Http\Resources\OrderOfPaymentResource;
use App\Http\Responses\ApiResponse;
use App\Models\ComplianceAppeal;
use App\Models\ComplianceNotice;
use App\Models\Inspection;
use App\Models\OrderOfPayment;
use App\Models\PermitApplication;
use App\Services\Compliance\ComplianceService;
use App\Services\Fee\FeeService;
use App\Services\Inspection\InspectionService;
use App\Services\Operations\OperationsWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhaseFiveController extends Controller
{
    public function __construct(
        private readonly InspectionService $inspections,
        private readonly FeeService $fees,
        private readonly ComplianceService $compliance,
        private readonly OperationsWorkflow $operations,
    ) {
    }

    /**
     * List inspections for staff.
     */
    public function listInspections(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('inspections.manage'), 403);

        $paginator = $this->inspections->list(
            $request->string('search')->toString(),
            $request->string('status')->toString() ?: null,
            (int) $request->integer('per_page', 25),
            $request->string('bucket')->toString() ?: null,
        );

        return ApiResponse::success('Inspections retrieved', [
            'items' => InspectionResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Schedule an inspection for an application.
     */
    public function scheduleInspection(ScheduleInspectionRequest $request, PermitApplication $application): JsonResponse
    {
        $inspection = $this->inspections->schedule($application, $request->user(), $request->validated());
        $application->refresh();

        return ApiResponse::success(
            'Inspection scheduled',
            $this->operations->withNextStep(
                (new InspectionResource($inspection))->resolve(),
                $application,
            ),
            201,
        );
    }

    /**
     * Complete an inspection with result and notes/sheets.
     */
    public function completeInspection(CompleteInspectionRequest $request, Inspection $inspection): JsonResponse
    {
        $model = $this->inspections->complete($inspection, $request->user(), $request->validated());
        $model->loadMissing('application');

        return ApiResponse::success(
            'Inspection completed',
            $this->operations->withNextStep(
                (new InspectionResource($model))->resolve(),
                $model->application,
            ),
        );
    }

    /**
     * List orders of payment.
     */
    public function listOrders(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('fees.manage'), 403);

        $paginator = $this->fees->listOrders(
            $request->string('search')->toString(),
            $request->string('status')->toString() ?: null,
            (int) $request->integer('per_page', 25),
            $request->string('bucket')->toString() ?: null,
        );

        return ApiResponse::success('Orders of payment retrieved', [
            'items' => OrderOfPaymentResource::collection($paginator->items()),
            'summary' => $this->fees->orderSummary(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Preview fee assessment for an application (no OoP issued yet).
     */
    public function previewOrder(Request $request, PermitApplication $application): JsonResponse
    {
        abort_unless($request->user()?->can('fees.manage'), 403);

        $preview = $this->fees->previewForApplication($application);

        return ApiResponse::success('Fee preview retrieved', $preview);
    }

    /**
     * Generate / issue an Order of Payment from fee rules (+ CTO/BFP/DPWH stubs).
     */
    public function generateOrder(GenerateOrderOfPaymentRequest $request, PermitApplication $application): JsonResponse
    {
        $order = $this->fees->generateOrder($application, $request->user(), $request->validated());
        $application->refresh();

        return ApiResponse::success(
            'Order of payment issued',
            $this->operations->withNextStep(
                (new OrderOfPaymentResource($order))->resolve(),
                $application,
            ),
            201,
        );
    }

    /**
     * Mark an OoP as paid via CTO stub.
     */
    public function markOrderPaid(Request $request, OrderOfPayment $order): JsonResponse
    {
        abort_unless($request->user()?->can('fees.manage'), 403);

        $model = $this->fees->markPaidStub(
            $order,
            $request->user(),
            $request->string('payment_reference')->toString() ?: null,
        );
        $model->loadMissing('application');

        return ApiResponse::success(
            'Order marked paid (CTO stub)',
            $this->operations->withNextStep(
                (new OrderOfPaymentResource($model))->resolve(),
                $model->application,
            ),
        );
    }

    /**
     * List compliance notices (G-03 / G-04).
     */
    public function listNotices(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('compliance.manage'), 403);

        $paginator = $this->compliance->listNotices(
            $request->string('search')->toString(),
            $request->string('type')->toString() ?: null,
            $request->string('status')->toString() ?: null,
            (int) $request->integer('per_page', 25),
            $request->string('bucket')->toString() ?: null,
        );

        return ApiResponse::success('Compliance notices retrieved', [
            'items' => ComplianceNoticeResource::collection($paginator->items()),
            'summary' => $this->compliance->noticeSummary(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Issue a G-03 or G-04 notice.
     */
    public function issueNotice(IssueComplianceNoticeRequest $request, PermitApplication $application): JsonResponse
    {
        $notice = $this->compliance->issue($application, $request->user(), $request->validated());
        $application->refresh();

        return ApiResponse::success(
            'Compliance notice issued',
            $this->operations->withNextStep(
                (new ComplianceNoticeResource($notice))->resolve(),
                $application,
            ),
            201,
        );
    }

    /**
     * File an appeal against an issued notice.
     */
    public function fileAppeal(FileAppealRequest $request, ComplianceNotice $notice): JsonResponse
    {
        $appeal = $this->compliance->fileAppeal($notice, $request->user(), $request->validated());

        return ApiResponse::success('Appeal filed', [
            'uuid' => $appeal->uuid,
            'status' => $appeal->status?->value ?? $appeal->status,
            'notice_uuid' => $notice->uuid,
        ], 201);
    }

    /**
     * Resolve a compliance appeal.
     */
    public function resolveAppeal(ResolveAppealRequest $request, ComplianceAppeal $appeal): JsonResponse
    {
        $model = $this->compliance->resolveAppeal($appeal, $request->user(), $request->validated());

        return ApiResponse::success('Appeal resolved', [
            'uuid' => $model->uuid,
            'status' => $model->status?->value ?? $model->status,
            'resolution_notes' => $model->resolution_notes,
        ]);
    }
}
