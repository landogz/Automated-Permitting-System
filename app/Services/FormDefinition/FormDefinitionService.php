<?php

declare(strict_types=1);

namespace App\Services\FormDefinition;

use App\Models\FormDefinition;
use App\Repositories\FormDefinitionRepository;
use App\Services\Audit\AuditLogger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class FormDefinitionService
{
    public function __construct(
        private readonly FormDefinitionRepository $repository,
        private readonly AuditLogger $audit,
    ) {
    }

    public function list(string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($search, $perPage);
    }

    public function activeList()
    {
        return $this->repository->allActive();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): FormDefinition
    {
        return DB::transaction(function () use ($data): FormDefinition {
            $form = $this->repository->create([
                'code' => strtoupper($data['code']),
                'title' => $data['title'],
                'revision' => $data['revision'] ?? '01',
                'effective_date' => $data['effective_date'] ?? null,
                'schema' => $data['schema'] ?? [],
                'required_attachments' => $data['required_attachments'] ?? [],
                'is_active' => $data['is_active'] ?? true,
            ]);

            $this->audit->log('form_definition.created', [
                'form_id' => $form->uuid,
                'code' => $form->code,
                'title' => $form->title,
            ]);

            return $form;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(FormDefinition $form, array $data): FormDefinition
    {
        return DB::transaction(function () use ($form, $data): FormDefinition {
            if (isset($data['code'])) {
                $data['code'] = strtoupper($data['code']);
            }

            $form = $this->repository->update($form, $data);

            $this->audit->log('form_definition.updated', [
                'form_id' => $form->uuid,
                'code' => $form->code,
                'title' => $form->title,
            ]);

            return $form;
        });
    }

    public function delete(FormDefinition $form): void
    {
        DB::transaction(function () use ($form): void {
            $meta = [
                'form_id' => $form->uuid,
                'code' => $form->code,
                'title' => $form->title,
            ];
            $this->repository->delete($form);
            $this->audit->log('form_definition.deleted', $meta);
        });
    }
}
