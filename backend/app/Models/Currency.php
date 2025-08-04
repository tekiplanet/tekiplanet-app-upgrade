<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'symbol',
        'rate',
        'is_base',
        'is_active',
        'decimal_places',
        'position'
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'is_base' => 'boolean',
        'is_active' => 'boolean',
        'decimal_places' => 'integer',
        'position' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($currency) {
            if (empty($currency->position)) {
                $currency->position = static::max('position') + 1;
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBase($query)
    {
        return $query->where('is_base', true);
    }

    public function getFormattedRateAttribute()
    {
        return number_format($this->rate, 6);
    }

    public function getDisplayNameAttribute()
    {
        return "{$this->name} ({$this->code})";
    }

    public function convertAmount($amount, $fromCurrency = null)
    {
        if ($fromCurrency) {
            // Convert from another currency to this currency
            // Both rates are relative to base currency (NGN)
            // To convert from currency A to currency B:
            // 1. Convert A to base: amount / A_rate
            // 2. Convert base to B: (amount / A_rate) * B_rate
            $fromRate = $fromCurrency->rate;
            $toRate = $this->rate;
            return ($amount / $fromRate) * $toRate;
        }
        
        // Convert from base currency to this currency
        return $amount * $this->rate;
    }

    public function convertToBase($amount)
    {
        return $amount / $this->rate;
    }
} 