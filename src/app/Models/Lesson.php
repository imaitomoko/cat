<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'year',
        'school_id',
        'class_id',
        'day1',
        'start_time1',
        'duration1',
        'day2',
        'start_time2',
        'duration2',
        'max_number',
        
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_lessons', 'lesson_id', 'user_id')
                    ->withPivot('status', 'end_date', 'status')
                    ->withTimestamps();
    }

    public function userLessons()
    {
        return $this->hasMany(UserLesson::class, 'lesson_id');
    }


    public function setYearAttribute($value)
    {
    // 入力された値を基に4月から翌年3月までの範囲を計算
        $this->attributes['year'] = Carbon::createFromFormat('Y', $value)->format('Y');
    }

    public function getYearRangeAttribute()
    {
    // `year`を基に範囲を計算して取得
        $start = Carbon::createFromFormat('Y-m-d', "{$this->year}-04-01");
        $end = $start->copy()->addYear()->subDay();
        return "{$start->format('Y/m/d')} - {$end->format('Y/m/d')}";
    }

    public function getLessonCalendar($year)
    {
        $calendar = [];
        $start = Carbon::createFromFormat('Y-m-d', "{$year}-04-01"); // 4月1日開始
        $end = $start->copy()->addYear()->subDay(); // 翌年3月31日終了

        while ($start <= $end) {
            $dayOfWeek = $start->dayOfWeek; // 0:日曜, 6:土曜
            if ($dayOfWeek == Carbon::parse($this->day1)->dayOfWeek) {
                $calendar[$start->format('Y-m-d')] = $this->lesson_value ?? '休校';
            } elseif ($dayOfWeek == Carbon::parse($this->day2)->dayOfWeek) {
                $calendar[$start->format('Y-m-d')] = $this->lesson_value ?? '休校';
            } else {
                $calendar[$start->format('Y-m-d')] = '休校';
            }
            $start->addDay();
        }

        return $calendar;
    }

    public function lessonValues()
    {
        return $this->hasMany(LessonValue::class);
    }

    

}
