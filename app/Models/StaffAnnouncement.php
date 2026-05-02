<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class StaffAnnouncement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'audience',
        'priority',
        'status',
        'created_by',
        'acknowledged_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];

    public const STATUS_OPEN = 'open';
    public const STATUS_ACKNOWLEDGED = 'acknowledged';
    public const AUDIENCE_SELECTED_USERS = 'selected_users';
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_MAJOR = 'major';
    public const PRIORITY_CRITICAL = 'critical';

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function acknowledgements(): HasMany
    {
        return $this->hasMany(StaffAnnouncementAck::class);
    }

    public function targetUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'staff_announcement_user');
    }

    public function scopeOpenForUser(Builder $query, User $user): Builder
    {
        $role = $user->role;
        $hasSelectedUsersTable = Schema::hasTable('staff_announcement_user');

        return $query
            ->where('status', self::STATUS_OPEN)
            ->where(function (Builder $q) use ($role, $user, $hasSelectedUsersTable) {
                $q->where('audience', 'all')
                    ->orWhere('audience', $role)
                    ->orWhere(function (Builder $q2) use ($user, $hasSelectedUsersTable) {
                        if (! $hasSelectedUsersTable) {
                            $q2->whereRaw('1 = 0');
                            return;
                        }
                        $q2->where('audience', self::AUDIENCE_SELECTED_USERS)
                            ->whereHas('targetUsers', function (Builder $uq) use ($user) {
                                $uq->where('users.id', $user->id);
                            });
                    });
            })
            ->latest();
    }

    public function expectedAudienceUsers(): Builder
    {
        return match ($this->audience) {
            'all' => User::query()->whereIn('role', [User::ROLE_EMPLOYEE, User::ROLE_FRONT_DESK, User::ROLE_IT_STAFF]),
            User::ROLE_EMPLOYEE, User::ROLE_FRONT_DESK, User::ROLE_IT_STAFF => User::query()->where('role', $this->audience),
            self::AUDIENCE_SELECTED_USERS => $this->targetUsers()->getQuery(),
            default => User::query()->whereRaw('1=0'),
        };
    }

    public static function audienceLabel(string $audience): string
    {
        return match ($audience) {
            User::ROLE_EMPLOYEE => 'Employees',
            User::ROLE_FRONT_DESK => 'Front Desk',
            User::ROLE_IT_STAFF => 'IT Staff',
            'all' => 'All staff',
            self::AUDIENCE_SELECTED_USERS => 'Selected users',
            default => ucfirst($audience),
        };
    }

    public static function allowedStatuses(): array
    {
        return [self::STATUS_OPEN, self::STATUS_ACKNOWLEDGED];
    }

    public static function allowedPriorities(): array
    {
        return [self::PRIORITY_LOW, self::PRIORITY_NORMAL, self::PRIORITY_MAJOR, self::PRIORITY_CRITICAL];
    }

    public static function allowedAudiences(): array
    {
        return [
            'all',
            self::AUDIENCE_SELECTED_USERS,
            User::ROLE_EMPLOYEE,
            User::ROLE_FRONT_DESK,
            User::ROLE_IT_STAFF,
        ];
    }

    public static function normalizeStatus(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = strtolower(trim($value));
        return in_array($normalized, self::allowedStatuses(), true) ? $normalized : null;
    }

    public static function normalizePriority(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = strtolower(trim($value));
        return in_array($normalized, self::allowedPriorities(), true) ? $normalized : null;
    }

    public static function normalizeAudience(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = strtolower(trim($value));
        return in_array($normalized, self::allowedAudiences(), true) ? $normalized : null;
    }

    public function setStatusAttribute(mixed $value): void
    {
        $normalized = self::normalizeStatus($value);
        if ($normalized === null) {
            throw ValidationException::withMessages([
                'status' => 'Invalid staff announcement status value.',
            ]);
        }

        $this->attributes['status'] = $normalized;
    }

    public function setPriorityAttribute(mixed $value): void
    {
        $normalized = self::normalizePriority($value);
        if ($normalized === null) {
            throw ValidationException::withMessages([
                'priority' => 'Invalid staff announcement priority value.',
            ]);
        }

        $this->attributes['priority'] = $normalized;
    }

    public function setAudienceAttribute(mixed $value): void
    {
        $normalized = self::normalizeAudience($value);
        if ($normalized === null) {
            throw ValidationException::withMessages([
                'audience' => 'Invalid staff announcement audience value.',
            ]);
        }

        $this->attributes['audience'] = $normalized;
    }
}

