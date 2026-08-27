<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Exam;

/**
 * ExamRepository.
 */
class ExamRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Exam::class;
    }
}
