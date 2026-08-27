<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * BaseRepository.
 *
 * Generic implementation of {@see RepositoryInterface} backed by Eloquent.
 * Concrete repositories extend this and bind a specific model via
 * {@see BaseRepository::modelClass()}.
 *
 * @template T of Model
 *
 * @implements RepositoryInterface<T>
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * Return the fully qualified model class name.
     *
     * @return class-string<T>
     */
    abstract protected function modelClass(): string;

    /**
     * Create a fresh query builder for the model (without global scopes
     * being bypassed - tenant scope remains active).
     */
    /**
     * Create a fresh query builder for the model (without global scopes
     * being bypassed - tenant scope remains active).
     *
     * @return Builder<T>
     */
    protected function query(): Builder
    {
        /** @var Builder<T> $builder */
        $builder = $this->modelClass()::query();

        return $builder;
    }

    /**
     * {@inheritDoc}
     *
     * @return Collection<int, T>
     */
    public function all(): Collection
    {
        /** @var Collection<int, T> $models */
        $models = $this->query()->get();

        return $models;
    }

    /**
     * {@inheritDoc}
     *
     * @return LengthAwarePaginator<int, T>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     *
     * @return T|null
     */
    public function find(int $id): ?Model
    {
        /** @var T|null $model */
        $model = $this->query()->find($id);

        return $model;
    }

    /**
     * {@inheritDoc}
     *
     * @return T
     */
    public function findOrFail(int $id): Model
    {
        /** @var T $model */
        $model = $this->query()->findOrFail($id);

        return $model;
    }

    /**
     * {@inheritDoc}
     *
     * @return T
     */
    public function create(array $attributes): Model
    {
        /** @var T $model */
        $model = $this->query()->create($attributes);

        return $model;
    }

    /**
     * {@inheritDoc}
     *
     * @param  T  $model
     * @return T
     */
    public function update(Model $model, array $attributes): Model
    {
        $model->update($attributes);

        return $model;
    }

    /**
     * {@inheritDoc}
     *
     * @param  T  $model
     */
    public function delete(Model $model): void
    {
        $model->delete();
    }
}
