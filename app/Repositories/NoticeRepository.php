<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Notice;

/**
 * NoticeRepository.
 */
class NoticeRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Notice::class;
    }
}
