<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
