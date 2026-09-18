<?php

declare(strict_types=1);

use App\Http\Controllers\API\Admin\AuditLogController;
use App\Http\Controllers\API\Admin\ClassificationRuleController;
use App\Http\Controllers\API\Admin\DepartmentController;
use App\Http\Controllers\API\Admin\FeeRuleController;
use App\Http\Controllers\API\Admin\FormDefinitionController;
use App\Http\Controllers\API\Admin\NotificationTemplateController;
use App\Http\Controllers\API\Admin\ProjectPlanController;
use App\Http\Controllers\API\Admin\RegistrationController;
use App\Http\Controllers\API\Admin\RoutingTemplateController;
use App\Http\Controllers\API\Admin\UserController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\GeoController;
use App\Http\Controllers\API\PermitApplicationController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\Staff\PhaseFiveController;
use App\Http\Controllers\API\Staff\PhaseSixController;
use App\Http\Controllers\API\Staff\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
        Route::post('demo-login', [AuthController::class, 'demoLogin'])->middleware('throttle:10,1');
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
            Route::put('profile', [ProfileController::class, 'update'])
                ->middleware('throttle:30,1');
            Route::post('profile/avatar', [ProfileController::class, 'uploadAvatar'])
                ->middleware('throttle:20,1');
            Route::delete('profile/avatar', [ProfileController::class, 'removeAvatar'])
                ->middleware('throttle:20,1');
            Route::put('password', [ProfileController::class, 'changePassword'])
                ->middleware('throttle:10,1');
        });
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('geo/search', [GeoController::class, 'search'])->middleware('throttle:30,1');
        Route::get('geo/reverse', [GeoController::class, 'reverse'])->middleware('throttle:60,1');

        Route::get('applications/forms', [PermitApplicationController::class, 'forms']);
        Route::get('applications', [PermitApplicationController::class, 'index']);
        Route::post('applications', [PermitApplicationController::class, 'store']);
        Route::get('applications/{application}', [PermitApplicationController::class, 'show']);
        Route::put('applications/{application}', [PermitApplicationController::class, 'update']);
        Route::post('applications/{application}/documents', [PermitApplicationController::class, 'uploadDocument'])
            ->middleware('throttle:30,1');
        Route::get('applications/{application}/documents/{document}/file', [PermitApplicationController::class, 'streamDocument'])
            ->middleware('throttle:60,1');
        Route::delete('applications/{application}/documents/{document}', [PermitApplicationController::class, 'destroyDocument'])
            ->middleware('throttle:30,1');
        Route::post('applications/{application}/submit', [PermitApplicationController::class, 'submit']);

        // Applicant (or staff) appeal against an issued compliance notice
        Route::post('compliance-notices/{notice}/appeals', [PhaseFiveController::class, 'fileAppeal'])
            ->middleware('throttle:30,1');

        Route::prefix('staff')->group(function (): void {
            Route::get('queue', [WorkflowController::class, 'queue']);
            Route::get('applications/lookup', [WorkflowController::class, 'lookupApplications'])
                ->middleware('throttle:60,1');
            Route::get('applications/{application}', [WorkflowController::class, 'showApplication'])
                ->middleware('throttle:60,1');
            Route::get('applications/{application}/suggest-classification', [WorkflowController::class, 'suggest']);
            Route::post('applications/{application}/classify', [WorkflowController::class, 'classify'])
                ->middleware('throttle:30,1');
            Route::post('applications/{application}/routing-slip', [WorkflowController::class, 'generateRoutingSlip'])
                ->middleware('throttle:30,1');
            Route::get('routing-templates', [WorkflowController::class, 'listRoutingTemplates'])
                ->middleware('throttle:60,1');
            Route::post('routing-steps/{step}/start', [WorkflowController::class, 'startStep']);
            Route::post('routing-steps/{step}/complete', [WorkflowController::class, 'completeStep']);
            Route::post('applications/{application}/evaluations', [WorkflowController::class, 'storeEvaluation']);
            Route::post('evaluations/{evaluation}/forms', [WorkflowController::class, 'saveEvaluationForms'])
                ->middleware('throttle:60,1');
            Route::post('evaluations/{evaluation}/decide', [WorkflowController::class, 'decideEvaluation']);
            Route::get('evaluation-form-templates', [WorkflowController::class, 'evaluationFormTemplates'])
                ->middleware('throttle:60,1');
            Route::get('evaluation-time-summary', [WorkflowController::class, 'timeSummary'])
                ->middleware('throttle:60,1');
            Route::post('applications/{application}/timer/start', [WorkflowController::class, 'startTimer']);
            Route::get('timer/current', [WorkflowController::class, 'currentTimer']);
            Route::post('timer/stop', [WorkflowController::class, 'stopTimer']);

            Route::get('inspections', [PhaseFiveController::class, 'listInspections']);
            Route::get('inspection-form-templates', [PhaseFiveController::class, 'inspectionFormTemplates'])
                ->middleware('throttle:60,1');
            Route::post('applications/{application}/inspections', [PhaseFiveController::class, 'scheduleInspection'])
                ->middleware('throttle:30,1');
            Route::post('inspections/{inspection}/forms', [PhaseFiveController::class, 'saveInspectionForms'])
                ->middleware('throttle:60,1');
            Route::post('inspections/{inspection}/complete', [PhaseFiveController::class, 'completeInspection'])
                ->middleware('throttle:30,1');

            Route::get('orders-of-payment', [PhaseFiveController::class, 'listOrders']);
            Route::get('applications/{application}/orders-of-payment/preview', [PhaseFiveController::class, 'previewOrder'])
                ->middleware('throttle:60,1');
            Route::post('applications/{application}/orders-of-payment', [PhaseFiveController::class, 'generateOrder'])
                ->middleware('throttle:30,1');
            Route::post('orders-of-payment/{order}/mark-paid', [PhaseFiveController::class, 'markOrderPaid'])
                ->middleware('throttle:30,1');

            Route::get('compliance-notices', [PhaseFiveController::class, 'listNotices']);
            Route::post('applications/{application}/compliance-notices', [PhaseFiveController::class, 'issueNotice'])
                ->middleware('throttle:30,1');
            Route::post('compliance-notices/{notice}/appeals', [PhaseFiveController::class, 'fileAppeal'])
                ->middleware('throttle:30,1');
            Route::post('compliance-appeals/{appeal}/resolve', [PhaseFiveController::class, 'resolveAppeal'])
                ->middleware('throttle:30,1');

            Route::get('dashboard-stats', [PhaseSixController::class, 'dashboardStats']);
            Route::get('logbook-entries', [PhaseSixController::class, 'listLogbook']);
            Route::post('logbook-entries', [PhaseSixController::class, 'storeLogbook'])
                ->middleware('throttle:30,1');
            Route::get('archive-records', [PhaseSixController::class, 'listArchives']);
            Route::post('archive-records', [PhaseSixController::class, 'storeArchive'])
                ->middleware('throttle:30,1');
            Route::post('notifications/send', [PhaseSixController::class, 'sendNotification'])
                ->middleware('throttle:30,1');
        });

        Route::get('notifications', [PhaseSixController::class, 'myNotifications']);
        Route::post('notifications/read-all', [PhaseSixController::class, 'markAllNotificationsRead']);
        Route::post('notifications/{notification}/read', [PhaseSixController::class, 'markNotificationRead']);

        Route::prefix('admin')->group(function (): void {
            Route::get('departments/export', [DepartmentController::class, 'export']);
            Route::post('departments/import', [DepartmentController::class, 'import']);
            Route::apiResource('departments', DepartmentController::class);

            Route::get('form-definitions/templates', [FormDefinitionController::class, 'templates']);
            Route::apiResource('form-definitions', FormDefinitionController::class);

            Route::get('audit-logs/summary', [AuditLogController::class, 'summary']);
            Route::get('audit-logs', [AuditLogController::class, 'index']);
            Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show']);

            Route::get('project-plan', [ProjectPlanController::class, 'show']);

            Route::get('registrations', [RegistrationController::class, 'index']);
            Route::post('registrations/{user}/approve', [RegistrationController::class, 'approve'])
                ->middleware('throttle:30,1');
            Route::post('registrations/{user}/decline', [RegistrationController::class, 'decline'])
                ->middleware('throttle:30,1');

            Route::get('users/meta', [UserController::class, 'meta']);
            Route::apiResource('users', UserController::class);

            Route::apiResource('classification-rules', ClassificationRuleController::class)
                ->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('routing-templates', RoutingTemplateController::class)
                ->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('fee-rules', FeeRuleController::class)
                ->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('notification-templates', NotificationTemplateController::class)
                ->only(['index', 'store', 'destroy']);
        });
    });
});
