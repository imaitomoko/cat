<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_comment_id',
        'report_id',
    ];

    public function teacherComment()
    {
        return $this->belongsTo(
            TeacherComment::class,
            'teacher_comment_id'
        );
    }

    public function report()
    {
        return $this->belongsTo(
            Report::class,
            'report_id'
        );
    }
}
