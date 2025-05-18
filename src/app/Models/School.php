<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Comment;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_name',
        'en_school_name',
    ];

    public function lessons()
    {
    return $this->hasMany(Lesson::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
