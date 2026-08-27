<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Fee;

/**
 * FeeRepository.
 *
 * @extends BaseRepository<Fee>
 */
class FeeRepository extends BaseRepository
{
    protected function modelClass(): string
    {
        return Fee::class;
    }
}
