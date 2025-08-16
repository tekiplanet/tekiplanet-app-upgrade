<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class UserFile extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'category_id',
        'file_name',
        'original_name',
        'file_size',
        'mime_type',
        'file_extension',
        'cloudinary_public_id',
        'cloudinary_url',
        'cloudinary_secure_url',
        'resource_type',
        'status',
        'download_count',
        'expires_at',
        'is_public',
        'metadata',
        'description'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'download_count' => 'integer',
        'expires_at' => 'datetime',
        'is_public' => 'boolean',
        'metadata' => 'array'
    ];

    /**
     * Get the sender of the file
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver of the file
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the category of the file
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(FileCategory::class, 'category_id');
    }

    /**
     * Get file permissions
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(FilePermission::class, 'file_id');
    }

    /**
     * Scope for files sent by a user
     */
    public function scopeSentBy($query, $userId)
    {
        return $query->where('sender_id', $userId);
    }

    /**
     * Scope for files received by a user
     */
    public function scopeReceivedBy($query, $userId)
    {
        return $query->where('receiver_id', $userId);
    }

    /**
     * Scope for active files only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for expired files
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope for files that haven't expired
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Check if user can access this file
     */
    public function canAccess(User $user): bool
    {
        // Admin can access all files
        if ($user->is_admin) {
            return true;
        }

        // Sender and receiver can always access
        if ($user->id === $this->sender_id || $user->id === $this->receiver_id) {
            return true;
        }

        // Check explicit permissions
        return $this->permissions()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Check if user can download this file
     */
    public function canDownload(User $user): bool
    {
        if (!$this->canAccess($user)) {
            return false;
        }

        // Check for download permission
        $permission = $this->permissions()
            ->where('user_id', $user->id)
            ->where('permission_type', 'download')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->first();

        // If no explicit permission, sender and receiver can download
        if (!$permission) {
            return $user->id === $this->sender_id || $user->id === $this->receiver_id;
        }

        return true;
    }

    /**
     * Check if user can delete this file
     */
    public function canDelete(User $user): bool
    {
        if (!$this->canAccess($user)) {
            return false;
        }

        // Admin can delete any file
        if ($user->is_admin) {
            return true;
        }

        // Only sender can delete
        return $user->id === $this->sender_id;
    }

    /**
     * Increment download count
     */
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    /**
     * Check if file is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get time until expiration
     */
    public function getTimeUntilExpirationAttribute(): ?string
    {
        if (!$this->expires_at) {
            return null;
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
     * Mark file as deleted
     */
    public function markAsDeleted(): void
    {
        $this->update(['status' => 'deleted']);
    }

    /**
     * Mark file as expired
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Get file type icon
     */
    public function getFileTypeIconAttribute(): string
    {
        $extension = strtolower($this->file_extension);
        
        $iconMap = [
            // Images
            'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️', 'webp' => '🖼️', 'svg' => '🖼️',
            // Videos
            'mp4' => '🎥', 'avi' => '🎥', 'mov' => '🎥', 'wmv' => '🎥', 'flv' => '🎥', 'webm' => '🎥',
            // Documents
            'pdf' => '📄', 'doc' => '📄', 'docx' => '📄', 'xls' => '📊', 'xlsx' => '📊', 'ppt' => '📊', 'pptx' => '📊',
            // Archives
            'zip' => '📦', 'rar' => '📦', '7z' => '📦', 'tar' => '📦', 'gz' => '📦'
        ];

        return $iconMap[$extension] ?? '📎';
    }
}
