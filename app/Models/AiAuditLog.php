<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * AiAuditLog.
 *
 * Immutable audit record of every AI Assistant interaction: detected intent,
 * tool used, the user query and the generated response. This is the core of
 * the "Step 6: Audit Logging" requirement and supports future research on
 * assistant behaviour.
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $institution_id
 * @property string|null $intent
 * @property string|null $tool
 * @property string $query
 * @property string|null $response
 * @property int $tokens_used
 */
class AiAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'institution_id', 'intent', 'tool', 'query',
        'response', 'tokens_used', 'created_at',
    ];

    protected $casts = [
        'tokens_used' => 'integer',
        'created_at' => 'datetime',
    ];
}
