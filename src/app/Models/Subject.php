<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;

class Subject extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'name_en',
    ];

    public function classes()
    {
        return $this->belongsToMany(
            SchoolClass::class,
            'class_subjects',
            'subject_id',
            'class_id'
        );
    } 

    public function categories()
    {
        return $this->hasMany(Category::class);
    }


}
