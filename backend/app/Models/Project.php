<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Project extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'client_name',
        'start_date',
        'end_date',
        'status',
        'progress',
        'budget'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'progress' => 'integer',
        'budget' => 'decimal:2'
    ];

    public function businessProfile()
    {
        return $this->belongsTo(BusinessProfile::class, 'business_id');
    }

    public function stages()
    {
        return $this->hasMany(ProjectStage::class);
    }

    public function teamMembers()
    {
        return $this->hasMany(ProjectTeamMember::class)->with('professional');
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function invoices()
    {
        return $this->hasMany(ProjectInvoice::class);
    }
} 