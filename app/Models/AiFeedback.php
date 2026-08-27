<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AiFeedback.
 *
 * User rating/comment on an AI response, used to evaluate and improve the
 * assistant (future research on response quality).
 *
 * @property int $id
 * @property int $user_id
 * @property int $ai_audit_log_id
 * @property int|null $rating
 */
class AiFeedback extends Model
{
    protected $fillable = [
        'user_id', 'ai_audit_log_id', 'rating', 'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * @return BelongsTo
     */
    public function auditLog()
    {
        return $this->belongsTo(AiAuditLog::class);
    }
}
