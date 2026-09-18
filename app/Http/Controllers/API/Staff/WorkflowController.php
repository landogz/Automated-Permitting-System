<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Classifier\ClassifyApplicationRequest;
use App\Http\Requests\Evaluation\DecideEvaluationRequest;
use App\Http\Requests\Evaluation\SaveEvaluationFormsRequest;
use App\Http\Requests\Evaluation\StoreEvaluationRequest;
use App\Http\Resources\EvaluationResource;
use App\Http\Resources\PermitApplicationResource;
use App\Http\Resources\RoutingSlipResource;
use App\Http\Resources\RoutingTemplateResource;
use App\Http\Responses\ApiResponse;
use App\Models\Evaluation;
use App\Models\PermitApplication;
use App\Models\RoutingSlipStep;
use App\Services\Classifier\ClassifierService;
use App\Services\Evaluation\EvaluationService;
use App\Services\Operations\OperationsWorkflow;
use App\Services\PermitApplication\PermitApplicationService;
use App\Services\Routing\RoutingService;
use App\Support\Evaluation\EvaluationFormCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WorkflowController extends Controller
{
    public function __construct(
        private readonly ClassifierService $classifier,
        private readonly RoutingService $routing,
        private readonly EvaluationService $evaluation,
        private readonly PermitApplicationService $applications,
        private readonly OperationsWorkflow $operations,
    ) {
    }

    /**
     * List applications in the evaluation queue.
     */
    public function queue(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('evaluations.manage'), 403);

        $bucket = $request->string('bucket')->toString() ?: 'active';
        if (! in_array($bucket, ['active', 'completed'], true)) {
            $bucket = 'active';
        }

        $paginator = $this->evaluation->listForStaff(
            $request->string('search')->toString(),
            (int) $request->integer('per_page', 15),
            $bucket,
        );

        return ApiResponse::success('Evaluation queue retrieved', [
            'items' => PermitApplicationResource::collection($paginator->items()),
            'bucket' => $bucket,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Searchable application picker for staff forms (inspections, fees, records, etc.).
     */
    public function lookupApplications(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('applications.manage'), 403);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'for_step' => ['nullable', 'string', Rule::in([
                OperationsWorkflow::STEP_EVALUATION,
                OperationsWorkflow::STEP_INSPECTION,
                OperationsWorkflow::STEP_PAYMENT,
                OperationsWorkflow::STEP_COMPLIANCE,
                OperationsWorkflow::STEP_RELEASING,
            ])],
        ]);

        $forStep = isset($validated['for_step']) ? (string) $validated['for_step'] : null;
        $statuses = $forStep ? $this->operations->statusesForStep($forStep) : null;
        $excludeOpenInspection = $forStep === OperationsWorkflow::STEP_INSPECTION;

        $paginator = $this->applications->lookupForStaff(
            (string) ($validated['search'] ?? ''),
            (int) ($validated['per_page'] ?? 20),
            $statuses,
            $excludeOpenInspection,
        );

        $items = collect($paginator->items())->map(static fn (PermitApplication $app): array => [
            'uuid' => $app->uuid,
            'application_no' => $app->application_no,
            'project_title' => $app->project_title,
            'status' => $app->status,
            'applicant_name' => $app->user?->name,
        ]);

        return ApiResponse::success('Applications lookup retrieved', [
            'items' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Staff detail view for an application (payload, documents, routing).
     */
    public function showApplication(Request $request, PermitApplication $application): JsonResponse
    {
        $user = $request->user();
        abort_unless(
            $user?->can('applications.manage')
                || $user?->can('evaluations.manage')
                || $user?->can('inspections.manage')
                || $user?->can('fees.manage')
                || $user?->can('compliance.manage')
                || $user?->can('records.manage'),
            403
        );

        try {
            $model = $this->applications->showForStaff($application->uuid);
        } catch (ValidationException $e) {
            return ApiResponse::error($e->getMessage(), $e->errors(), 404);
        }

        if ($user?->can('evaluations.manage') && $this->evaluation->pruneOrphanDrafts($model) > 0) {
            $model->load([
                'evaluations' => fn ($q) => $q->orderByDesc('id')->with('evaluator:id,uuid,name,email'),
            ]);
        }

        return ApiResponse::success('Application details retrieved', new PermitApplicationResource($model));
    }

    /**
     * Suggest classification for an application using active rules.
     */
    public function suggest(Request $request, PermitApplication $application): JsonResponse
    {
        abort_unless($request->user()?->can('evaluations.manage'), 403);

        $result = $this->classifier->suggest($application);

        return ApiResponse::success('Classification suggested', [
            'classification' => $result['classification']->value,
            'rule' => $result['rule'] ? [
                'uuid' => $result['rule']->uuid,
                'code' => $result['rule']->code,
                'name' => $result['rule']->name,
            ] : null,
        ]);
    }

    /**
     * Classify an application (auto or manual).
     */
    public function classify(ClassifyApplicationRequest $request, PermitApplication $application): JsonResponse
    {
        $data = $request->validated();
        if (($data['auto'] ?? false) === true) {
            unset($data['classification']);
        }

        $model = $this->classifier->classify($application, $request->user(), $data);

        return ApiResponse::success('Application classified', new PermitApplicationResource($model));
    }

    /**
     * Read-only routing templates catalog for Operations staff.
     * Admins with workflow.manage can still edit them under Workflow Config.
     */
    public function listRoutingTemplates(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless(
            $user?->can('evaluations.manage')
                || $user?->can('workflow.manage')
                || $user?->can('applications.manage'),
            403,
        );

        $paginator = $this->routing->listTemplates(
            $request->string('search')->toString(),
            (int) $request->integer('per_page', 50),
        );

        return ApiResponse::success('Routing templates retrieved', [
            'items' => RoutingTemplateResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'can_manage' => (bool) $user?->can('workflow.manage'),
        ]);
    }

    /**
     * Generate a routing slip from the matching template.
     */
    public function generateRoutingSlip(Request $request, PermitApplication $application): JsonResponse
    {
        abort_unless($request->user()?->can('evaluations.manage'), 403);

        $slip = $this->routing->generateSlip($application, $request->user());

        return ApiResponse::success('Routing slip generated', new RoutingSlipResource($slip), 201);
    }

    /**
     * Start a routing slip step.
     */
    public function startStep(Request $request, RoutingSlipStep $step): JsonResponse
    {
        abort_unless($request->user()?->can('evaluations.manage'), 403);

        $model = $this->routing->startStep($step, $request->user());

        return ApiResponse::success('Routing step started', [
            'uuid' => $model->uuid,
            'status' => $model->status?->value ?? $model->status,
        ]);
    }

    /**
     * Complete a routing slip step.
     */
    public function completeStep(Request $request, RoutingSlipStep $step): JsonResponse
    {
        abort_unless($request->user()?->can('evaluations.manage'), 403);

        $model = $this->routing->completeStep(
            $step,
            $request->user(),
            $request->string('notes')->toString() ?: null,
        );

        return ApiResponse::success('Routing step completed', [
            'uuid' => $model->uuid,
            'status' => $model->status?->value ?? $model->status,
        ]);
    }

    /**
     * Blank evaluation form templates (QMS-61/62/63/64).
     */
    public function evaluationFormTemplates(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('evaluations.manage'), 403);

        return ApiResponse::success('Evaluation form templates retrieved', [
            'items' => EvaluationFormCatalog::templates(),
            'qms63_default_items' => EvaluationFormCatalog::qms63DefaultItems(),
            'qms64_default_items' => EvaluationFormCatalog::qms64DefaultItems(),
        ]);
    }

    /**
     * Create an evaluation sheet.
     */
    public function storeEvaluation(StoreEvaluationRequest $request, PermitApplication $application): JsonResponse
    {
        $evaluation = $this->evaluation->create($application, $request->user(), $request->validated());

        return ApiResponse::success('Evaluation created', new EvaluationResource($evaluation), 201);
    }

    /**
     * Save draft QMS-63/64 findings without deciding.
     */
    public function saveEvaluationForms(SaveEvaluationFormsRequest $request, Evaluation $evaluation): JsonResponse
    {
        $model = $this->evaluation->saveDraft($evaluation, $request->user(), $request->validated());

        return ApiResponse::success('Evaluation forms saved', new EvaluationResource($model));
    }

    /**
     * Decide an evaluation sheet.
     */
    public function decideEvaluation(DecideEvaluationRequest $request, Evaluation $evaluation): JsonResponse
    {
        $model = $this->evaluation->decide($evaluation, $request->user(), $request->validated());
        $model->loadMissing('application');

        return ApiResponse::success(
            'Evaluation decided',
            $this->operations->withNextStep(
                (new EvaluationResource($model))->resolve(),
                $model->application,
            ),
        );
    }

    /**
     * Start evaluation time tracking.
     */
    public function startTimer(Request $request, PermitApplication $application): JsonResponse
    {
        abort_unless($request->user()?->can('evaluations.manage'), 403);

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
            'department_uuid' => ['nullable', 'uuid', 'exists:departments,uuid'],
            'routing_slip_step_uuid' => ['nullable', 'uuid', 'exists:routing_slip_steps,uuid'],
        ]);

        $log = $this->evaluation->startTimer($application, $request->user(), $validated);

        return ApiResponse::success('Timer started', [
            'uuid' => $log->uuid,
            'started_at' => $log->started_at?->toIso8601String(),
            'application_no' => $application->application_no,
            'application_uuid' => $application->uuid,
            'department' => $log->department ? [
                'uuid' => $log->department->uuid,
                'code' => $log->department->code,
                'name' => $log->department->name,
            ] : null,
        ], 201);
    }

    /**
     * Current open evaluation timer for the authenticated staff user (if any).
     */
    public function currentTimer(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('evaluations.manage'), 403);

        $open = $this->evaluation->currentOpenTimer($request->user());
        if (! $open) {
            return ApiResponse::success('No open timer', null);
        }

        return ApiResponse::success('Open timer', [
            'uuid' => $open->uuid,
            'started_at' => $open->started_at?->toIso8601String(),
            'elapsed_seconds' => max(0, now()->diffInSeconds($open->started_at)),
            'application_no' => $open->application?->application_no,
            'application_uuid' => $open->application?->uuid,
            'department' => $open->department ? [
                'uuid' => $open->department->uuid,
                'code' => $open->department->code,
                'name' => $open->department->name,
            ] : null,
        ]);
    }

    /**
     * Stop the current open evaluation timer.
     */
    public function stopTimer(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('evaluations.manage'), 403);

        $log = $this->evaluation->stopTimer($request->user());

        return ApiResponse::success('Timer stopped', [
            'uuid' => $log->uuid,
            'duration_seconds' => $log->duration_seconds,
            'ended_at' => $log->ended_at?->toIso8601String(),
            'department' => $log->department ? [
                'uuid' => $log->department->uuid,
                'code' => $log->department->code,
                'name' => $log->department->name,
            ] : null,
        ]);
    }

    /**
     * Time tracking rollup by department and staff.
     */
    public function timeSummary(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('evaluations.manage'), 403);

        $summary = $this->evaluation->timeSummary(
            $request->string('application_uuid')->toString() ?: null,
        );

        return ApiResponse::success('Evaluation time summary retrieved', $summary);
    }
}
