<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SendTo extends Model
{
    use HasFactory;

    protected $table = 'send_to';

    protected $fillable = [
        'mail_id',
        'user_lesson_id',
    ];

    // mailsテーブルとのリレーション
    public function mail()
    {
        return $this->belongsTo(Mail::class);
    }

    // user_lessonsテーブルとのリレーション
    public function userLesson()
    {
        return $this->belongsTo(UserLesson::class);
    }
}
