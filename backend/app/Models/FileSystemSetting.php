<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FileSystemSetting extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'description',
        'is_editable'
    ];

    protected $casts = [
        'is_editable' => 'boolean'
    ];

    /**
     * Get setting value with proper type casting
     */
    public function getTypedValueAttribute()
    {
        switch ($this->setting_type) {
            case 'integer':
                return (int) $this->setting_value;
            case 'boolean':
                return filter_var($this->setting_value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($this->setting_value, true);
            default:
                return $this->setting_value;
        }
    }

    /**
     * Set setting value with proper type handling
     */
    public function setTypedValueAttribute($value)
    {
        switch ($this->setting_type) {
            case 'integer':
                $this->setting_value = (string) (int) $value;
                break;
            case 'boolean':
                $this->setting_value = $value ? 'true' : 'false';
                break;
            case 'json':
                $this->setting_value = is_string($value) ? $value : json_encode($value);
                break;
            default:
                $this->setting_value = (string) $value;
        }
    }

    /**
     * Get setting by key with caching
     */
    public static function getValue(string $key, $default = null)
    {
        $cacheKey = "file_system_setting_{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('setting_key', $key)->first();
            return $setting ? $setting->typed_value : $default;
        });
    }

    /**
     * Set setting value
     */
    public static function setValue(string $key, $value): bool
    {
        $setting = static::where('setting_key', $key)->first();
        
        if (!$setting) {
            return false;
        }

        if (!$setting->is_editable) {
            return false;
        }

        $setting->typed_value = $value;
        $setting->save();

        // Clear cache
        Cache::forget("file_system_setting_{$key}");

        return true;
    }

    /**
     * Get all settings as array
     */
    public static function getAllSettings(): array
    {
        return Cache::remember('file_system_settings_all', 3600, function () {
            $settings = static::all();
            $result = [];
            
            foreach ($settings as $setting) {
                $result[$setting->setting_key] = $setting->typed_value;
            }
            
            return $result;
        });
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $settings = static::all();
        
        foreach ($settings as $setting) {
            Cache::forget("file_system_setting_{$setting->setting_key}");
        }
        
        Cache::forget('file_system_settings_all');
    }

    /**
     * Get formatted value for display
     */
    public function getFormattedValueAttribute(): string
    {
        switch ($this->setting_type) {
            case 'integer':
                if (str_contains($this->setting_key, 'storage') || str_contains($this->setting_key, 'size')) {
                    return $this->formatBytes($this->typed_value);
                }
                return number_format($this->typed_value);
            case 'boolean':
                return $this->typed_value ? 'Enabled' : 'Disabled';
            case 'json':
                return json_encode($this->typed_value, JSON_PRETTY_PRINT);
            default:
                return $this->setting_value;
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if setting is a sensitive setting (should be hidden in logs)
     */
    public function isSensitive(): bool
    {
        $sensitiveKeys = [
            'cloudinary_api_secret',
            'cloudinary_api_key',
            'encryption_key'
        ];

        return in_array($this->setting_key, $sensitiveKeys);
    }

    /**
     * Get masked value for sensitive settings
     */
    public function getMaskedValueAttribute(): string
    {
        if (!$this->isSensitive()) {
            return $this->setting_value;
        }

        $value = $this->setting_value;
        $length = strlen($value);
        
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, 2) . str_repeat('*', $length - 4) . substr($value, -2);
    }
}
