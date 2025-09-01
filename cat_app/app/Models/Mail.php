<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mail extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'body',
        'attachment',
        'sent_at',
        'send_to_text',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function sendTos()
    {
        return $this->hasMany(SendTo::class);
    }

    public function userLessons()
    {
        return $this->hasManyThrough(UserLesson::class, SendTo::class, 'mail_id', 'id', 'id', 'user_lesson_id');
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($mail) {
            $mail->sendTos()->delete(); // 例: リレーションデータも削除
        });
    }
}
