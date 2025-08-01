<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'product_name',
        'description',
        'min_price',
        'max_price',
        'deadline',
        'quantity_needed',
        'additional_details',
        'status',
        'admin_response'
    ];

    protected $casts = [
        'quantity_needed' => 'integer',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'deadline' => 'date'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 