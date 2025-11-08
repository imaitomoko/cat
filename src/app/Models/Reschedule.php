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

    public function userLessonStatus()
    {
        return $this->belongsTo(UserLessonStatus::class, 'user_lesson_status_id');
    }

    public function userLesson()
    {
        return $this->hasOneThrough(
            UserLesson::class,       // 取得したい最終モデル
            UserLessonStatus::class, // 中間モデル
            'id',                    // 中間モデルの主キー（user_lesson_status.id）
            'id',                    // 最終モデルの主キー（user_lesson.id）
            'user_lesson_status_id',// Rescheduleにある外部キー
            'user_lesson_id'         // 中間モデルにある外部キー
        );
    }


}
