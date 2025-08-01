<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'username',
        'avatar',
        'status',
        'wallet_balance',
        'dark_mode',
        'email_notifications',
        'push_notifications',
        'marketing_notifications',
        'profile_visibility',
        'timezone',
        'language',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'dark_mode' => 'boolean',
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'marketing_notifications' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'two_factor_recovery_codes' => 'array',
        'wallet_balance' => 'decimal:2',
    ];

    /**
     * The default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'dark_mode' => false,
        'two_factor_enabled' => false,
        'email_notifications' => true,
        'push_notifications' => true,
        'marketing_notifications' => true,
        'profile_visibility' => 'public',
        'status' => 'active',
        'wallet_balance' => 0.00,
    ];

    /**
     * Validation rules for account type
     */
    public static $accountTypeOptions = ['student', 'business', 'professional'];

    /**
     * Get the enrollments for the user.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Add this relationship to the existing User model
     */
    public function businessProfile()
    {
        return $this->hasOne(BusinessProfile::class);
    }

    public function business_profile()
    {
        return $this->hasOne(BusinessProfile::class);
    }

    /**
     * Get the professional profile associated with the user.
     */
    public function professional()
    {
        return $this->hasOne(Professional::class);
    }

    // Helper method to check if user has an active business profile
    public function hasActiveBusiness()
    {
        return $this->businessProfile && $this->businessProfile->status === 'active';
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });

        static::deleting(function($user) {
            // Delete business profile
            if ($user->businessProfile) {
                $user->businessProfile->delete();
            }

            // Delete professional profile
            if ($user->professional) {
                $user->professional->delete();
            }

            // Delete other related records
            $user->userNotifications()->delete();
            $user->transactions()->delete();
            $user->enrollments()->delete();
            $user->userCourseNotices()->delete();
        });
    }

    // Add an accessor to get the full avatar URL
    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) {
                    return null;
                }

                // Log debugging information
                // Log::info('Avatar accessor:', [
                //     'raw_value' => $value,
                //     'app_url' => config('app.url'),
                //     'full_path' => storage_path('app/public/' . $value),
                //     'exists' => Storage::disk('public')->exists($value),
                //     'public_path' => public_path('storage/' . $value),
                //     'file_exists' => file_exists(public_path('storage/' . $value))
                // ]);

                // Check both storage and public paths
                if (!Storage::disk('public')->exists($value) && !file_exists(public_path('storage/' . $value))) {
                    return null;
                }

                // Return the full URL
                return rtrim(config('app.url'), '/') . '/storage/' . ltrim($value, '/');
            }
        );
    }

    // Add these relationships to the existing User model

    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'user_notifications')
            ->withPivot('read', 'read_at')
            ->withTimestamps()
            ->orderByDesc('created_at');
    }

    // Add this method for direct access to user_notifications
    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    public function unreadNotifications()
    {
        return $this->notifications()
            ->wherePivot('read', false);
    }

    // Add status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Get the valid status options
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    // Add this relationship to the User model
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Add this relationship
    public function userCourseNotices()
    {
        return $this->hasMany(UserCourseNotice::class);
    }

    public function getNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function routeNotificationForFcm()
    {
        return $this->deviceTokens()->pluck('token')->toArray();
    }
}
