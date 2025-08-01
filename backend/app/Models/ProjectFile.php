<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Admin;

class ProjectFile extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_by',
        'project_id'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(Admin::class, 'uploaded_by');
    }
} 