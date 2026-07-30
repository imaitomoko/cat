<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_lesson_id',
        'term_id',
        'free_comment_ja',
        'free_comment_en',
        'type',
    ];

    public function userLesson()
    {
        return $this->belongsTo(UserLesson::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }


}
