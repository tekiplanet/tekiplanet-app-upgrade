<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCourseExam extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    // Add this line to ensure relationships are properly serialized
    protected $with = ['user', 'courseExam'];

    protected $fillable = [
        'id',
        'user_id',
        'course_exam_id',
        'status',
        'score',
        'total_score',
        'attempts',
        'started_at',
        'completed_at',
        'answers',
        'review_notes'
    ];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'course_exam_id' => 'string',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'answers' => 'array',
        'review_notes' => 'array',
        'score' => 'float',
        'total_score' => 'float'
    ];

    // Add this property to store the action temporarily
    public $action;

    protected static function boot()
    {
        parent::boot();
        
        // Auto-generate UUID
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function courseExam(): BelongsTo
    {
        return $this->belongsTo(CourseExam::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    // Utility methods
    public function markAsStarted()
    {
        $this->status = 'in_progress';
        $this->started_at = now();
        $this->attempts++;
        $this->save();
    }

    public function markAsCompleted($score, $totalScore)
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->score = $score;
        $this->total_score = $totalScore;
        $this->save();
    }

    public function markAsMissed()
    {
        $this->status = 'missed';
        $this->save();
    }

    protected $attributes = [
        'action' => null
    ];

    public function getActionAttribute()
    {
        return $this->attributes['action'] ?? null;
    }

    public function setActionAttribute($value)
    {
        $this->attributes['action'] = $value;
    }
}
