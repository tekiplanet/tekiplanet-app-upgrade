<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CourseLesson extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'module_id',
        'title',
        'description',
        'content_type',
        'duration_minutes',
        'order',
        'resource_url',
        'is_preview',
        'pass_percentage',
        'learn_rewards'
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

    public function module()
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class, 'lesson_id')->orderBy('order');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class, 'lesson_id');
    }
}
