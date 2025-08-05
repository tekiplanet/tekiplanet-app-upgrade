<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QuizAttempt extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'lesson_id',
        'score',
        'total_points',
        'percentage',
        'passed',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'passed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lesson()
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id');
    }

    public function responses()
    {
        return $this->hasMany(QuizResponse::class, 'attempt_id');
    }
} 