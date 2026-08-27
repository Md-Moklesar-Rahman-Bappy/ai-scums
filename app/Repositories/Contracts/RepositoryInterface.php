<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * RepositoryInterface.
 *
 * Contract every domain repository must implement. Provides a thin,
 * testable abstraction over Eloquent so that controllers and services never
 * touch the query builder directly.
 *
 * @template T of Model
 */
interface RepositoryInterface
{
    /**
     * Get all records.
     *
     * @return Collection<int, T>
     */
    public function all(): Collection;

    /**
     * Paginated listing.
     *
     * @return Paginator<T>
     */
    public function paginate(int $perPage = 15): Paginator;

    /**
     * Find a record by id.
     *
     * @return T|null
     */
    public function find(int $id): ?Model;

    /**
     * Find a record by id or fail.
     *
     * @return T
     */
    public function findOrFail(int $id): Model;

    /**
     * Create a record from attributes.
     *
     * @return T
     */
    public function create(array $attributes): Model;

    /**
     * Update a record.
     *
     * @param  T  $model
     */
    public function update(Model $model, array $attributes): Model;

    /**
     * Delete a record.
     *
     * @param  T  $model
     */
    public function delete(Model $model): void;
}
