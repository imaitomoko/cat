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

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

}
