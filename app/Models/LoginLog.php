<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    public const EVENT_LOGIN = 'login';
    public const EVENT_LOGOUT = 'logout';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Keep only the most recent $limit logs for the given user,
     * deleting any older records beyond that count.
     */
    public static function pruneForUser(int $userId, int $limit = 20): void
    {
        $idsToDelete = static::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->skip($limit)
            ->take(PHP_INT_MAX)
            ->pluck('id');

        if ($idsToDelete->isNotEmpty()) {
            static::whereIn('id', $idsToDelete)->delete();
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
