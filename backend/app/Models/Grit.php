<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Grit extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'budget',
        'deadline',
        'requirements',
        'status',
        'assigned_professional_id',
        'initial_payment_released',
        'final_payment_released',
        'created_by_user_id',
        'admin_approval_status',
        'owner_budget',
        'owner_currency',
        'professional_budget',
        'professional_currency',
        'negotiation_status',
        'terms_modified_at',
        'project_started_at',
        'completion_requested_at',
        'owner_satisfaction',
        'owner_rating',
        'dispute_status',
        'project_id',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'owner_budget' => 'decimal:2',
        'professional_budget' => 'decimal:2',
        'deadline' => 'date',
        'initial_payment_released' => 'boolean',
        'final_payment_released' => 'boolean',
        'terms_modified_at' => 'datetime',
        'project_started_at' => 'datetime',
        'completion_requested_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(ProfessionalCategory::class);
    }

    public function assignedProfessional()
    {
        return $this->belongsTo(Professional::class, 'assigned_professional_id');
    }

    public function applications()
    {
        return $this->hasMany(GritApplication::class);
    }

    public function messages()
    {
        return $this->hasMany(GritMessage::class);
    }

    public function payments()
    {
        return $this->hasMany(GritPayment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function escrowTransactions()
    {
        return $this->hasMany(GritEscrowTransaction::class);
    }

    public function negotiations()
    {
        return $this->hasMany(GritNegotiation::class);
    }

    public function disputes()
    {
        return $this->hasMany(GritDispute::class);
    }
} 
