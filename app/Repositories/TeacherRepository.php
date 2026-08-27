<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Teacher;

/**
 * TeacherRepository.
 *
 * Tenant-scoped data access for {@see Teacher}.
 *
 * @extends BaseRepository<Teacher>
 */
class TeacherRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Teacher::class;
    }
}
