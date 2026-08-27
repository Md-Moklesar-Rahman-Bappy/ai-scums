<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Teacher;
use App\Repositories\TeacherRepository;
use Illuminate\Contracts\Pagination\Paginator;

/**
 * TeacherService.
 *
 * Teacher management: assignment, department linkage and subject allocation.
 * The controller delegates persistence here; tenant isolation is applied by
 * the TenantScoped global scope.
 */
class TeacherService
{
    public function __construct(private readonly TeacherRepository $repository) {}

    /**
     * @return Paginator<Teacher>
     */
    public function list(int $perPage = 15): Paginator
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Create a teacher with an optional subject allocation.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Teacher
    {
        $subjects = $data['subject_ids'] ?? [];
        unset($data['subject_ids']);

        /** @var Teacher $teacher */
        $teacher = $this->repository->create($data);

        if (! empty($subjects)) {
            $teacher->subjects()->sync($subjects);
        }

        return $teacher;
    }

    /**
     * Update a teacher and subject allocation.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Teacher $teacher, array $data): Teacher
    {
        $subjects = $data['subject_ids'] ?? null;
        unset($data['subject_ids']);

        $this->repository->update($teacher, $data);

        if ($subjects !== null) {
            $teacher->subjects()->sync($subjects);
        }

        return $teacher;
    }

    /**
     * Soft-delete a teacher.
     */
    public function delete(Teacher $teacher): void
    {
        $this->repository->delete($teacher);
    }
}
