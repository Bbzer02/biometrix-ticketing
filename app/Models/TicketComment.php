<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketComment extends Model
{
    public const TYPE_COMMENT = 'comment';
    public const TYPE_SYSTEM = 'system';

    protected $fillable = ['ticket_id', 'user_id', 'type', 'body'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function authorDisplay(): string
    {
        if ($this->type === self::TYPE_SYSTEM) {
            return 'System';
        }
        return $this->user?->name ?? 'Guest';
    }
}
