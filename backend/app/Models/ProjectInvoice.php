<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProjectInvoice extends Model
{
    use HasUuids;

    protected $fillable = [
        'project_id',
        'invoice_number',
        'amount',
        'status',
        'due_date',
        'paid_at',
        'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'datetime',
        'paid_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            // Generate invoice number if not set
            if (!$invoice->invoice_number) {
                $latestInvoice = static::latest()->first();
                $nextNumber = $latestInvoice ? intval(substr($latestInvoice->invoice_number, 3)) + 1 : 1;
                $invoice->invoice_number = 'INV' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            }

            // Set default description if not provided
            if (!$invoice->description) {
                $invoice->description = 'Project Invoice #' . $invoice->invoice_number;
            }
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
} 