<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title', 
        'description', 
        'category',
        'category_id', 
        'level', 
        'price', 
        'instructor_id',
        'image_url', 
        'duration_hours', 
        'status',
        'rating',
        'total_reviews',
        'total_students',
        'prerequisites',
        'learning_outcomes'
    ];

    protected $casts = [
        'prerequisites' => 'array',
        'learning_outcomes' => 'array',
        'rating' => 'float',
        'status' => 'string'
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

    public function modules()
    {
        return $this->hasMany(CourseModule::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function features()
    {
        return $this->hasMany(CourseFeature::class);
    }

    public function exams()
    {
        return $this->hasMany(CourseExam::class);
    }

    public function notices()
    {
        return $this->hasMany(CourseNotice::class);
    }

    public function schedules()
    {
        return $this->hasMany(CourseSchedule::class);
    }

    public function reviews()
    {
        return $this->hasMany(CourseReview::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }

    public function updateRating()
    {
        $averageRating = $this->reviews()->avg('rating');
        $totalReviews = $this->reviews()->count();

        $this->update([
            'rating' => round($averageRating, 2),
            'total_reviews' => $totalReviews
        ]);

        return $this;
    }

    /**
     * Get the category that owns the course.
     */
    public function category()
    {
        return $this->belongsTo(CourseCategory::class);
    }

    /**
     * Get the prerequisites attribute.
     *
     * @param  string|null  $value
     * @return array
     */
    public function getPrerequisitesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Get the learning outcomes attribute.
     *
     * @param  string|null  $value
     * @return array
     */
    public function getLearningOutcomesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Set the prerequisites attribute.
     *
     * @param  array|string  $value
     * @return void
     */
    public function setPrerequisitesAttribute($value)
    {
        $this->attributes['prerequisites'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Set the learning outcomes attribute.
     *
     * @param  array|string  $value
     * @return void
     */
    public function setLearningOutcomesAttribute($value)
    {
        $this->attributes['learning_outcomes'] = is_array($value) ? json_encode($value) : $value;
    }
}
