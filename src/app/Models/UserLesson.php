<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLesson extends Model
{
    use HasFactory;

    protected $table = 'user_lessons';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'status',
    ];

    public function lessons()
    {
        return $belongsToMany(Lesson::class, 'lesson_user_lessons', 'user_lesson_id', 'lesson_id')
                    ->withPivot('status')
                    ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
