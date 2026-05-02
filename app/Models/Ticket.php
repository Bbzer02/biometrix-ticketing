<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Ticket extends Model
{
    use HasFactory;

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_MAJOR = 'major';
    public const PRIORITY_CRITICAL = 'critical';

    /** Workflow: Open → In Progress → Resolved → Closed | Cancelled */
    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    public const SOURCE_SYSTEM = 'self_service';   // ticket created via system (self-service)
    public const SOURCE_CALL_LOGGED = 'phone';      // call-logged (phone or walk_in)

    public const SOURCE_SELF_SERVICE = 'self_service';
    public const SOURCE_PHONE = 'phone';
    public const SOURCE_WALK_IN = 'walk_in';

    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'location',
        'scheduled_for',
        'category_id',
        'priority',
        'status',
        'source',
        'submitter_id',
        'requester_name',
        'requester_email',
        'requester_phone',
        'assignee_id',
        'accepted_by_id',
        'assigned_at',
        'resolved_at',
        'closed_at',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'scheduled_for' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitter_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class)->latest();
    }

    public function requesterDisplay(): string
    {
        if ($this->submitter_id) {
            return $this->submitter?->name ?? $this->requester_email ?? '—';
        }
        return $this->requester_name ?: ($this->requester_email ?? '—');
    }

    public static function generateTicketNumber(): string
    {
        $last = static::query()->orderByDesc('id')->first();
        $num = $last ? (int) preg_replace('/\D/', '', $last->ticket_number) + 1 : 1;
        return 'TKT-' . str_pad((string) $num, 5, '0', STR_PAD_LEFT);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_OPEN => 'Open',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_RESOLVED => 'Resolved',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public static function allowedStatuses(): array
    {
        return array_keys(self::statusLabels());
    }

    public static function allowedSources(): array
    {
        return [
            self::SOURCE_SELF_SERVICE,
            self::SOURCE_PHONE,
            self::SOURCE_WALK_IN,
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

    public static function normalizeSource(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = strtolower(trim($value));
        return in_array($normalized, self::allowedSources(), true) ? $normalized : null;
    }

    public function setStatusAttribute(mixed $value): void
    {
        $normalized = self::normalizeStatus($value);
        if ($normalized === null) {
            throw ValidationException::withMessages([
                'status' => 'Invalid ticket status value.',
            ]);
        }

        $this->attributes['status'] = $normalized;
    }

    public function setSourceAttribute(mixed $value): void
    {
        $normalized = self::normalizeSource($value);
        if ($normalized === null) {
            throw ValidationException::withMessages([
                'source' => 'Invalid ticket source value.',
            ]);
        }

        $this->attributes['source'] = $normalized;
    }
}
