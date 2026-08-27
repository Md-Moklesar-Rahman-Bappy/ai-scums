<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * AuditLog.
 *
 * Generic, system-wide audit trail for security-sensitive mutations
 * (role changes, fee payments, mark entries, etc.). Soft deletes are NOT used
 * here so the trail remains permanent.
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $institution_id
 * @property string $action
 * @property string|null $model_type
 * @property int|null $model_id
 */
class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'institution_id', 'action', 'model_type', 'model_id',
        'old_values', 'new_values', 'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
}
