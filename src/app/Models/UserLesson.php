<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class UserLesson extends Model
{
    use HasFactory;

    protected $table = 'user_lessons';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'start_date',
        'end_date'
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id')->withDefault();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    public function userLessonStatus()
    {
        return $this->hasMany(UserLessonStatus::class, 'user_lesson_id');
    }

    public function isActive()
    {
        // 現在の日付
        $now = Carbon::now();

        // start_date と end_date が設定されている場合、期間内で有効かを判定
        if ($this->start_date && $this->end_date) {
            return $now->between(Carbon::parse($this->start_date), Carbon::parse($this->end_date));
        }

        // start_date と end_date が設定されていない場合は有効
        return true;
    }

    public function sendTos()
    {
        return $this->hasMany(SendTo::class);
    }


}
