<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FileCategory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'allowed_extensions',
        'max_file_size',
        'resource_type',
        'is_active',
        'requires_optimization',
        'cloudinary_options',
        'sort_order'
    ];

    protected $casts = [
        'allowed_extensions' => 'array',
        'max_file_size' => 'integer',
        'is_active' => 'boolean',
        'requires_optimization' => 'boolean',
        'cloudinary_options' => 'array',
        'sort_order' => 'integer'
    ];

    /**
     * Get all files in this category
     */
    public function files(): HasMany
    {
        return $this->hasMany(UserFile::class, 'category_id');
    }

    /**
     * Scope to get only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * Check if a file extension is allowed in this category
     */
    public function isExtensionAllowed(string $extension): bool
    {
        return in_array(strtolower($extension), $this->allowed_extensions);
    }

    /**
     * Check if a file size is within the category limit
     */
    public function isSizeAllowed(int $fileSize): bool
    {
        return $fileSize <= $this->max_file_size;
    }

    /**
     * Get formatted max file size
     */
    public function getFormattedMaxSizeAttribute(): string
    {
        $bytes = $this->max_file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get allowed extensions as comma-separated string
     */
    public function getExtensionsStringAttribute(): string
    {
        return implode(', ', $this->allowed_extensions);
    }

    /**
     * Validate file against category rules
     */
    public function validateFile($file): array
    {
        $errors = [];
        
        // Check file size
        if (!$this->isSizeAllowed($file->getSize())) {
            $errors[] = "File size exceeds the maximum allowed size of {$this->formatted_max_size}";
        }
        
        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!$this->isExtensionAllowed($extension)) {
            $errors[] = "File extension '{$extension}' is not allowed. Allowed extensions: {$this->extensions_string}";
        }
        
        return $errors;
    }

    /**
     * Get Cloudinary options for this category
     */
    public function getCloudinaryOptions(): array
    {
        return $this->cloudinary_options ?? [];
    }

    /**
     * Check if category requires optimization
     */
    public function needsOptimization(): bool
    {
        return $this->requires_optimization && in_array($this->resource_type, ['image', 'video']);
    }
}
