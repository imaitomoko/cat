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
        $daysMap = [
            '日' => 0,
            '月' => 1,
            '火' => 2,
            '水' => 3,
            '木' => 4,
            '金' => 5,
            '土' => 6,
        ];

        $day1Number = $daysMap[$this->day1] ?? null;
        $day2Number = $this->day2 ? ($daysMap[$this->day2] ?? null) : null;

        $start = Carbon::createFromFormat('Y-m-d', "{$year}-04-01"); // 4月1日開始
        $end = $start->copy()->addYear()->subDay(); // 翌年3月31日終了

        while ($start <= $end) {
            $dayOfWeek = $start->dayOfWeek; // 0:日曜, 6:土曜

            if ($dayOfWeek == $day1Number || $dayOfWeek == $day2Number) {
                $calendar[$start->format('Y-m-d')] = '青①';
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

    public function reschedules()
    {
        return $this->hasMany(Reschedule::class);
    }

}
