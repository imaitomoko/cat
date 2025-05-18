<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reschedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_lesson_status_id',
        'lesson_id',
        'user_id',
        'reschedule_status', 
    ];

    public function originalLessonStatus()
    {
        return $this->belongsTo(UserLessonStatus::class, 'user_lesson_status_id');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
