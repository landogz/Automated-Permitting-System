<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\FormDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class FormDefinitionRepository
{
    public function paginate(string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        return FormDefinition::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('code', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->orderBy('code')
            ->paginate($perPage);
    }

    public function allActive(): Collection
    {
        return FormDefinition::query()->where('is_active', true)->orderBy('code')->get();
    }

    public function findByUuid(string $uuid): ?FormDefinition
    {
        return FormDefinition::query()->where('uuid', $uuid)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): FormDefinition
    {
        return FormDefinition::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(FormDefinition $form, array $data): FormDefinition
    {
        $form->update($data);

        return $form->refresh();
    }

    public function delete(FormDefinition $form): void
    {
        $form->delete();
    }
}
