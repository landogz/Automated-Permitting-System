<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\ImportDepartmentRequest;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Http\Responses\ApiResponse;
use App\Models\Department;
use App\Services\Department\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DepartmentController extends Controller
{
    public function __construct(private readonly DepartmentService $service)
    {
    }

    /**
     * Paginate departments for admin DataTables.
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('departments.manage'), 403);

        $paginator = $this->service->list(
            $request->string('search')->toString(),
            (int) $request->integer('per_page', 15),
        );

        return ApiResponse::success('Departments retrieved', [
            'items' => DepartmentResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Create a department.
     */
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = $this->service->create($request->validated());

        return ApiResponse::success('Department created', new DepartmentResource($department), 201);
    }

    /**
     * Show one department by UUID.
     */
    public function show(Department $department): JsonResponse
    {
        abort_unless(request()->user()?->can('departments.manage'), 403);

        return ApiResponse::success('Department retrieved', new DepartmentResource($department));
    }

    /**
     * Update a department.
     */
    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $department = $this->service->update($department, $request->validated());

        return ApiResponse::success('Department updated', new DepartmentResource($department));
    }

    /**
     * Soft-delete a department.
     */
    public function destroy(Department $department): JsonResponse
    {
        abort_unless(request()->user()?->can('departments.manage'), 403);
        $this->service->delete($department);

        return ApiResponse::success('Department deleted');
    }

    /**
     * Import departments from JSON rows (dry-run supported).
     */
    public function import(ImportDepartmentRequest $request): JsonResponse
    {
        $result = $this->service->importRows(
            $request->input('rows', []),
            $request->boolean('dry_run', true),
        );

        return ApiResponse::success(
            $request->boolean('dry_run', true) ? 'Import dry-run completed' : 'Import committed',
            $result
        );
    }

    /**
     * Export departments as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->can('departments.manage'), 403);

        $filename = 'departments-'.now()->format('Ymd-His').'.csv';
        $rows = Department::query()->orderBy('code')->get(['code', 'name', 'description', 'is_active']);

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['code', 'name', 'description', 'is_active']);
            foreach ($rows as $department) {
                fputcsv($handle, [
                    $department->code,
                    $department->name,
                    $department->description,
                    $department->is_active ? '1' : '0',
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
