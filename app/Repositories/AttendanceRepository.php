<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Attendance;

/**
 * AttendanceRepository.
 *
 * @extends BaseRepository<Attendance>
 */
class AttendanceRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Attendance::class;
    }
}
