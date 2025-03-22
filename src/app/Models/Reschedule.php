<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reschedule extends Model
{
    use HasFactory;

    protected $fillable = ['user_lesson_status_id', 'new_user_lesson_id'];

    public function originalLessonStatus()
    {
        return $this->belongsTo(UserLessonStatus::class, 'user_lesson_status_id');
    }

    public function newUserLesson()
    {
        return $this->belongsTo(UserLesson::class, 'new_user_lesson_id');
    }
}
