<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportPublication extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'term_id',
        'school_id',
        'class_id',
        'publish_at',
    ];

    protected $casts = [
        'publish_at' => 'datetime',
    ];

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
