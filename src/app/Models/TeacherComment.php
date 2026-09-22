<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment_ja',
        'comment_en',
        'type',
    ];

    public function reports()
    {
        return $this->belongsToMany(
            Report::class,
            'comment_reports',
            'teacher_comment_id',
            'report_id'
        );
    }
}
