<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notice.
 *
 * Announcement, event or notification published to a target audience inside an
 * institution. Used for both news feed items and calendar events.
 *
 * @property int $id
 * @property int $institution_id
 * @property string $title
 * @property string $type announcement|event|notification
 * @property string $audience all|students|teachers|parents|admins
 */
class Notice extends BaseModel
{
    protected $fillable = [
        'institution_id', 'title', 'body', 'type', 'audience',
        'published_at', 'expires_at', 'created_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * @return BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
