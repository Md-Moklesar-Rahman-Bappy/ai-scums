<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Student;

/**
 * StudentRepository.
 *
 * Tenant-scoped data access for {@see Student}. The BaseRepository + the
 * TenantScoped global scope guarantee every query is isolated to the active
 * institution.
 *
 * @extends BaseRepository<Student>
 */
class StudentRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Student::class;
    }
}
