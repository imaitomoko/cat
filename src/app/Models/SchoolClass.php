<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Comment;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'class_name',
    ];

    public function lessons()
    {
    return $this->hasMany(Lesson::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'class_id');
    }
}
