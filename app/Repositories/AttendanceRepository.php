<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Attendance;

/**
 * AttendanceRepository.
 */
class AttendanceRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Attendance::class;
    }
}
