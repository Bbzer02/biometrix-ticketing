<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class AccessRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_IGNORED = 'ignored';

    protected $fillable = [
        'email',
        'status',
    ];

    public static function allowedStatuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_IGNORED];
    }

    public static function normalizeStatus(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = strtolower(trim($value));
        return in_array($normalized, self::allowedStatuses(), true) ? $normalized : null;
    }

    public function setStatusAttribute(mixed $value): void
    {
        $normalized = self::normalizeStatus($value);
        if ($normalized === null) {
            throw ValidationException::withMessages([
                'status' => 'Invalid access request status value.',
            ]);
        }

        $this->attributes['status'] = $normalized;
    }

    public function setEmailAttribute(mixed $value): void
    {
        if (! is_string($value)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid email value.',
            ]);
        }

        $email = strtolower(trim($value));
        if ($email === '') {
            throw ValidationException::withMessages([
                'email' => 'Email is required.',
            ]);
        }

        $this->attributes['email'] = $email;
    }
}

