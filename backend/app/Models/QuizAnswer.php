<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QuizAnswer extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'question_id',
        'answer_text',
        'is_correct',
        'order'
    ];

    protected $casts = [
        'is_correct' => 'boolean'
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

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
} 