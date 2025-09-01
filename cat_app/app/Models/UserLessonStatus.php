<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLessonStatus extends Model
{
    use HasFactory;

    protected $fillable = ['user_lesson_id', 'date', 'status', 'reschedule_to','is_manual_absence',];

    public function userLesson()
    {
        return $this->belongsTo(UserLesson::class, 'user_lesson_id');
    }

    public function reschedule()
    {
        return $this->hasOne(Reschedule::class, 'user_lesson_status_id');
    }
}
