<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsList extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_list',
        'post_date',
        'end_date'
    ];
}
