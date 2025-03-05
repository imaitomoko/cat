<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id', 'date', 'lesson_value1', 'lesson_value2'
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
