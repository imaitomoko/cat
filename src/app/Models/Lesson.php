<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'lesson_value1',
        'day2',
        'start_time2',
        'duration2',
        'lesson_value2',
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
                    ->withPivot('status')
                    ->withTimestamps();
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
        $start = Carbon::createFromFormat('Y', $year)->startOfMonth()->addMonths(3); // 4月1日開始
        $end = $start->copy()->addYear()->subDay(); // 翌年3月31日終了

        while ($start <= $end) {
            $dayOfWeek = $start->dayOfWeek; // 曜日を取得 (0:日曜, 6:土曜)
            if ($dayOfWeek == $this->day1) {
            // `day1`に対応する曜日なら`lesson_value1`を割り当て
                $calendar[$start->format('Y-m-d')] = $this->lesson_value1 ?? '休校';
            } elseif ($dayOfWeek == $this->day2) {
            // `day2`に対応する曜日なら`lesson_value2`を割り当て
                $calendar[$start->format('Y-m-d')] = $this->lesson_value2 ?? '休校';
            } else {
            // レッスンがない場合は「休校」
                $calendar[$start->format('Y-m-d')] = '休校';
            }
            
            $start->addDay();
        }

        return $calendar;
    }



}
