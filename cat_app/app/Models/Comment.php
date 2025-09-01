<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\School;
use App\Models\SchoolClass;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'body',
        'school_id', 
        'class_id', 
        'year',
        'month'
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
