<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Professional extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'title',
        'specialization',
        'expertise_areas',
        'years_of_experience',
        'hourly_rate',
        'availability_status',
        'bio',
        'certifications',
        'linkedin_url',
        'github_url',
        'portfolio_url',
        'preferred_contact_method',
        'timezone',
        'languages',
        'rating',
        'total_sessions',
        'status',
        'verified_at',
        'category_id',
        'completion_rate',
        'average_rating',
        'total_projects_completed',
        'qualifications',
        'portfolio_items'
    ];

    protected $casts = [
        'expertise_areas' => 'array',
        'certifications' => 'array',
        'languages' => 'array',
        'rating' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'verified_at' => 'datetime',
        'years_of_experience' => 'integer',
        'total_sessions' => 'integer',
        'completion_rate' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'total_projects_completed' => 'integer',
        'qualifications' => 'array',
        'portfolio_items' => 'array'
    ];

    // Relationship with User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with ConsultingBookings
    public function consultingBookings(): HasMany
    {
        return $this->hasMany(ConsultingBooking::class, 'assigned_expert_id');
    }

    // Scope for available professionals
    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')
            ->where('availability_status', 'available');
    }

    // Scope for verified professionals
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    // Check if professional is available for a specific time slot
    public function isAvailableForBooking($date, $time): bool
    {
        return $this->availability_status === 'available' 
            && !$this->consultingBookings()
                ->where('selected_date', $date)
                ->where('selected_time', $time)
                ->exists();
    }

    // Update rating when new review is added
    public function updateRating()
    {
        $averageRating = $this->consultingBookings()
            ->whereHas('review')
            ->with('review')
            ->get()
            ->avg('review.rating');

        $this->update(['rating' => $averageRating]);
    }

    // Add this to your existing Professional model
    public function category()
    {
        return $this->belongsTo(ProfessionalCategory::class, 'category_id');
    }

    // Add these relationships to the existing Professional model

    public function gritApplications()
    {
        return $this->hasMany(GritApplication::class);
    }

    public function assignedGrits()
    {
        return $this->hasMany(Grit::class, 'assigned_professional_id');
    }

    public function gritPayments()
    {
        return $this->hasMany(GritPayment::class);
    }

    // Helper method to check if professional can apply for a grit
    public function canApplyForGrit(Grit $grit): bool
    {
        // Check if professional's profile is active
        if ($this->status !== 'active') {
            return false;
        }

        // Check if professional's category matches grit category
        if ($this->category_id !== $grit->category_id) {
            return false;
        }

        // Check if professional has already applied
        if ($this->gritApplications()
            ->where('grit_id', $grit->id)
            ->exists()) {
            return false;
        }

        // Check if grit is still open
        if ($grit->status !== 'open') {
            return false;
        }

        return true;
    }
} 