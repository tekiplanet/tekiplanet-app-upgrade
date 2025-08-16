<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class FilePermission extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'file_id',
        'user_id',
        'permission_type',
        'granted_by',
        'expires_at',
        'is_active'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Get the file this permission belongs to
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(UserFile::class, 'file_id');
    }

    /**
     * Get the user this permission is granted to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who granted this permission
     */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Scope for active permissions only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for permissions that haven't expired
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope for expired permissions
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope for specific permission types
     */
    public function scopeType($query, $type)
    {
        return $query->where('permission_type', $type);
    }

    /**
     * Check if permission is valid (active and not expired)
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if permission has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get time until expiration
     */
    public function getTimeUntilExpirationAttribute(): ?string
    {
        if (!$this->expires_at) {
            return 'Never expires';
        }

        $now = Carbon::now();
        $expires = Carbon::parse($this->expires_at);

        if ($expires->isPast()) {
            return 'Expired';
        }

        $diff = $now->diff($expires);

        if ($diff->days > 0) {
            return $diff->days . ' days';
        } elseif ($diff->h > 0) {
            return $diff->h . ' hours';
        } elseif ($diff->i > 0) {
            return $diff->i . ' minutes';
        } else {
            return $diff->s . ' seconds';
        }
    }

    /**
     * Deactivate permission
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Activate permission
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Extend permission expiration
     */
    public function extendExpiration(Carbon $newExpiration): void
    {
        $this->update(['expires_at' => $newExpiration]);
    }

    /**
     * Remove expiration (make permanent)
     */
    public function removeExpiration(): void
    {
        $this->update(['expires_at' => null]);
    }

    /**
     * Get permission type label
     */
    public function getPermissionTypeLabelAttribute(): string
    {
        $labels = [
            'view' => 'View',
            'download' => 'Download',
            'delete' => 'Delete',
            'share' => 'Share'
        ];

        return $labels[$this->permission_type] ?? $this->permission_type;
    }

    /**
     * Check if permission grants a specific action
     */
    public function grants(string $action): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Permission hierarchy
        $hierarchy = [
            'view' => ['view'],
            'download' => ['view', 'download'],
            'delete' => ['view', 'download', 'delete'],
            'share' => ['view', 'download', 'share']
        ];

        $allowedActions = $hierarchy[$this->permission_type] ?? [];
        return in_array($action, $allowedActions);
    }
}
