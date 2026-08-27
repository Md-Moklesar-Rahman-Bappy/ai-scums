<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Student;
use App\Repositories\StudentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * StudentService.
 *
 * Encapsulates student admission, enrollment updates and promotion. Keeps the
 * controller thin and centralises admission-number generation and tenant
 * assignment (handled by the TenantScoped scope on create).
 */
class StudentService
{
    public function __construct(private readonly StudentRepository $repository) {}

    /**
     * @return LengthAwarePaginator<int, Student>
     */
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Admit a student, generating a unique admission number.
     *
     * @param  array<string, mixed>  $data
     */
    public function admit(array $data): Student
    {
        $data['admission_no'] = $data['admission_no']
            ?? 'ADM-'.now()->year.'-'.strtoupper(Str::random(5));

        return $this->repository->create($data);
    }

    /**
     * Update a student's profile/enrollment.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Student $student, array $data): Student
    {
        return $this->repository->update($student, $data);
    }

    /**
     * Promote a student to the next class/semester by updating placement.
     *
     * @param  array<string, mixed>  $placement
     */
    public function promote(Student $student, array $placement): Student
    {
        return $this->repository->update($student, $placement);
    }

    /**
     * Soft-delete a student.
     */
    public function delete(Student $student): void
    {
        $this->repository->delete($student);
    }
}
