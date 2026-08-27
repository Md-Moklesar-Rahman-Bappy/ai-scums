<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Institution;
use App\Repositories\InstitutionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * InstitutionService.
 *
 * Business logic for tenant (institution) management: create, update,
 * activate/deactivate. Keeps the controller thin and centralises slug
 * generation and validation rules reuse via the repository.
 */
class InstitutionService
{
    public function __construct(private readonly InstitutionRepository $repository) {}

    /**
     * Paginated list of institutions.
     *
     * @return LengthAwarePaginator<int, Institution>
     */
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Create an institution, deriving a unique slug.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Institution
    {
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(6);

        return $this->repository->create($data);
    }

    /**
     * Update an institution.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Institution $institution, array $data): Institution
    {
        return $this->repository->update($institution, $data);
    }

    /**
     * Soft-delete an institution.
     */
    public function delete(Institution $institution): void
    {
        $this->repository->delete($institution);
    }
}
