<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AiConversation.
 *
 * Stores the message history of a single AI Assistant conversation for a user.
 * Not tenant-scoped by default because super-admins may query across tenants;
 * the institution_id is recorded for audit purposes.
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $institution_id
 * @property string|null $title
 * @property array|null $messages
 */
class AiConversation extends Model
{
    protected $fillable = [
        'user_id', 'institution_id', 'title', 'messages',
    ];

    protected $casts = [
        'messages' => 'array',
    ];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
