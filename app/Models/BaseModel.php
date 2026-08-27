<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * BaseModel.
 *
 * Abstract base for every tenant-scoped, soft-deletable domain model in the
 * platform. Enforces the project rule that every table contains id,
 * created_at, updated_at and deleted_at columns.
 */
abstract class BaseModel extends EloquentModel
{
    use HasFactory;
    use SoftDeletes;
    use TenantScoped;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'deleted_at',
    ];
}
