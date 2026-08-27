<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Routine;

/**
 * RoutineRepository.
 */
class RoutineRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Routine::class;
    }
}
