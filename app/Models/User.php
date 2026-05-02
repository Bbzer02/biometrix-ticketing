<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_EMPLOYEE = 'employee';
    public const ROLE_FRONT_DESK = 'front_desk';
    public const ROLE_IT_STAFF = 'it_staff';
    public const ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'emergency_email',
        'password',
        'role',
        'profile_picture',
        'sidebar_collapsed',
        'last_help_read_at',
        'last_notification_seen_at',
        'failed_login_attempts',
    ];

    /**
     * Profile picture public URL. Null if not set or file missing.
     * Uses asset() so the image loads correctly (same host as the app).
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (empty($this->profile_picture)) {
            return null;
        }
        if (! Storage::disk('public')->exists($this->profile_picture)) {
            return null;
        }
        return asset('storage/' . ltrim($this->profile_picture, '/'));
    }

    public function submittedTickets()
    {
        return $this->hasMany(Ticket::class, 'submitter_id');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assignee_id');
    }

    public function isFrontDesk(): bool
    {
        return in_array($this->role, [self::ROLE_FRONT_DESK, self::ROLE_ADMIN], true);
    }

    public function isItStaff(): bool
    {
        return in_array($this->role, [self::ROLE_IT_STAFF, self::ROLE_ADMIN], true);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public static function roleLabel(string $role): string
    {
        return match ($role) {
            self::ROLE_EMPLOYEE => 'Employee',
            self::ROLE_FRONT_DESK => 'Front Desk',
            self::ROLE_IT_STAFF => 'IT Staff',
            self::ROLE_ADMIN => 'Admin',
            default => $role,
        };
    }

    public function getRoleLabel(): string
    {
        return self::roleLabel($this->role ?? '');
    }

    /**
     * Whether this user has a password set (existing/old user).
     * Users with no password (null or empty) are "new" and can sign in with email only.
     */
    public function hasPasswordSet(): bool
    {
        $pw = $this->getRawPasswordValue();
        return $pw !== null && $pw !== '';
    }

    /**
     * Raw password value from the attribute (before cast); used to detect null/empty.
     */
    protected function getRawPasswordValue(): ?string
    {
        return $this->getRawOriginal('password');
    }

    /**
     * User IDs that currently have an active session (online).
     *
     * @return array<int>
     */
    public static function getOnlineUserIds(): array
    {
        if (! Schema::hasTable('sessions') || config('session.driver') !== 'database') {
            return [];
        }

        // Query directly — no cache so logout reflects immediately
        $lifetime = (int) config('session.lifetime', 120);
        $minLastActivity = now()->subMinutes($lifetime)->timestamp;

        return DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $minLastActivity)
            ->distinct()
            ->pluck('user_id')
            ->all();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'sidebar_collapsed' => 'boolean',
            'last_help_read_at' => 'datetime',
            'last_notification_seen_at' => 'datetime',
        ];
    }
}
