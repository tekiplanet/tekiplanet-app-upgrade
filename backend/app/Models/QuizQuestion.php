<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QuizQuestion extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'lesson_id',
        'question',
        'question_type',
        'points',
        'order'
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

    public function lesson()
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id');
    }

    public function answers()
    {
        return $this->hasMany(QuizAnswer::class, 'question_id')->orderBy('order');
    }

    public function responses()
    {
        return $this->hasMany(QuizResponse::class, 'question_id');
    }
} 