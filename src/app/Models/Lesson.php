<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'year',
        'school_id',
        'class_id',
        'day1',
        'start_time1',
        'duration1',
        'day2',
        'start_time2',
        'duration2',
        'max_number',
        'lesson_value',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_lessons')
                    ->withPivot('status')
                    ->withTimestamps();
    }

}
