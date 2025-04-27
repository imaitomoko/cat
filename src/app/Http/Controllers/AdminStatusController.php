<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lesson;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\LessonValue;
use App\Models\UserLesson;
use App\Models\UserLessonStatus;
use App\Models\Reschedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AdminStatusController extends Controller
{
    public function index()
    {
        // 学校とクラスを取得
        $schools = School::all();
        $schoolClasses = SchoolClass::all();

        return view('admin.status.admin_class', compact('schools', 'schoolClasses'));
    }

    public function search(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
        ]);

        $schoolId = $request->input('school_id');
        $classId = $request->input('class_id');
        $date =  Carbon::parse($request->input('date'));

        // 学校・クラスの情報を取得
        $school = School::findOrFail($schoolId);
        $class = SchoolClass::findOrFail($classId);

        $weekdayMap = [
            'Sunday'    => '日',
            'Monday'    => '月',
            'Tuesday'   => '火',
            'Wednesday' => '水',
            'Thursday'  => '木',
            'Friday'    => '金',
            'Saturday'  => '土'
        ];
        $weekdayJapanese = $weekdayMap[$date->format('l')] ?? null;

        $lessonValues = LessonValue::whereHas('lesson', function ($query) use ($schoolId, $classId) {
            $query->where('school_id', $schoolId)->where('class_id', $classId);
        })->get();


        // lesson_value が「休校」でないレッスンを取得
        $lessons = Lesson::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->whereHas('lessonValues', function ($query) use ($date) {
                $query->whereDate('date', $date) // lessonValues の date を基準にする
                        ->where('lesson_value', '!=', '休校');
            })
            ->pluck('id')
            ->toArray(); 
        
        // `$date` に該当する `userLesson` を取得（通常のレッスン + 振替レッスン）
        $userLessons = UserLesson::whereIn('lesson_id', $lessons)
            ->whereHas('userLessonStatus', function ($query) use ($date) {
                $query->whereDate('date', $date);
            })
            ->orWhereHas('userLessonStatus', function ($query) use ($date) {
                $query->whereDate('reschedule_to', $date);
            })
            ->orWhereHas('rescheduledFrom', function ($query) use ($date) {
                $query->whereHas('newUserLesson', function ($q) use ($date) {
                    $q->whereHas('userLessonStatus', function ($subQuery) use ($date) {
                        $subQuery->whereDate('date', $date);
                    });
                });
            })
            ->with(['user', 'lesson', 'userLessonStatus', 'userLessonStatus.reschedule.newUserLesson', 'rescheduledFrom'])
            ->get()
            ->map(function ($userLesson) use ($date, $weekdayJapanese) {
                // start_time1 または start_time2 を選択
                $lesson = $userLesson->lesson;
                $startTime = null;

                if ($lesson->day1 === $weekdayJapanese && !empty($lesson->start_time1)) {
                    $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $lesson->start_time1);
                } elseif ($lesson->day2 === $weekdayJapanese && !empty($lesson->start_time2)) {
                    $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $lesson->start_time2);
                } elseif (!empty($lesson->start_time1)) {
                    // day1・day2と一致しない場合でも fallback で start_time1 を使用
                    $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $lesson->start_time1);
                }

                $matchedStatus = $userLesson->userLessonStatus
                    ->first(function ($status) use ($date) {
                        return Carbon::parse($status->date)->isSameDay($date);
                    });

                $status = $matchedStatus->status ?? null;
                
                $now = Carbon::now();

                if ($date->isToday() && $startTime && $startTime->lt($now)) {
                    if ($status === '未受講' || is_null($status)) {
                        $status = '受講済み';
                    }
                } elseif ($date->lt($now)) {
                   // 検索日が本日より過去なら、すでに終了しているとみなす
                    if ($status === '未受講' || is_null($status)) {
                        $status = '受講済み';
                    }
                }

                if ($status === '欠席する') {
                    $status = '欠席';
                }

                return [
                    'userLesson' => $userLesson,
                    'user' => $userLesson->user,
                    'lessonDate' => Carbon::parse($matchedStatus->date ?? $userLesson->lesson->start_date),
                    'startTime' => $startTime,
                    'status' => $status,
                    'rescheduleTo' => optional($userLesson->userLessonStatus->first()->reschedule)->date,
                    'isRescheduled' => $userLesson->reschedules && $userLesson->reschedules->isNotEmpty() 
                        ? optional($userLesson->reschedules->first())->newUserLesson->userLessonStatus->date 
                        : null,

                ];
            });
        return view('admin.status.admin_class_list', compact('school', 'class', 'date', 'lessons', 'userLessons', 'lessonValues', 'weekdayJapanese'));
    }

    public function detail($id)
    {

        $student = User::with([
            'userLessons.lesson.school',
            'userLessons.lesson.schoolClass',
            'userLessons.userLessonStatus',
            'userLessons.userLessonStatus.reschedule',
            'userLessons.userLessonStatus.reschedule.newUserLesson',
            'userLessons.userLessonStatus.reschedule.newUserLesson.lesson',
        ])->findOrFail($id);

        return view('admin.status.admin_status', compact('student'));

    }

    public function toggleAbsence(Request $request, $userLessonId)
    {
        $date = $request->input('date'); // ← 日付を取得
        $newStatus = $request->input('status');

        // 該当の日付のステータスを取得
        $userLessonStatus = UserLessonStatus::where('user_lesson_id', $userLessonId)
            ->whereDate('date', $date)
            ->first();

        // なければ新しく作成（status は「欠席する」にする）
        if (!$userLessonStatus) {
            $userLessonStatus = new UserLessonStatus();
            $userLessonStatus->user_lesson_id = $userLessonId;
            $userLessonStatus->date = $date;
            $userLessonStatus->status = '欠席する';
        } else {
           // すでにある場合はステータスをトグル
            if ($userLessonStatus->status === '欠席する') {
                $userLessonStatus->status = '未受講';
            } else {
                $userLessonStatus->status = '欠席する';
            }
        }
        $userLessonStatus->status = $newStatus;

        $userLessonStatus->save();

        return back()->with('status', 'ステータスを更新しました');
    }

    // 振替キャンセル処理
    public function cancelReschedule($rescheduleId)
    {
        $reschedule = Reschedule::findOrFail($rescheduleId);

        // reschedule_to カラムを null にする
        $reschedule->originalLessonStatus->update(['reschedule_to' => null]);

        // reschedules テーブルから削除
        $reschedule->delete();

        return redirect()->route('admin.status.search')->with('success', '振替をキャンセルしました');
    }

    public function makeupShow($userLessonId, Request $request)
    {
        $statusId = $request->input('status_id');
        $userLesson = UserLesson::with(['lesson.schoolClass', 'lesson.school'])->findOrFail($userLessonId);
        $lesson = $userLesson->lesson;

        $startOfYear = Carbon::createFromDate($lesson->year, 4, 1);
        $endOfYear = $startOfYear->copy()->addYear()->subDay();
    
        $now = Carbon::now();
        $startDate = $now->copy();
        $endDate = $now->copy()->addMonth();

        $selectedSchoolId = $request->input('school_id', $lesson->school_id); // デフォルトは現在の教

         // 同じクラス・同じ年度・選ばれた教室のレッスン（自身を除く）
        $otherLessons = Lesson::where('year', $lesson->year)
            ->where('class_id', $lesson->class_id)
            ->where('school_id', $selectedSchoolId)
            ->where('id', '!=', $lesson->id)
            ->get();

        // 他の教室一覧（自分の教室以外）
        $otherSchools = School::where('id', '!=', $lesson->school_id)->get();

        // 候補日の生成
        $rescheduleCandidates = collect();
        foreach ($otherLessons as $otherLesson) {
            foreach ([$otherLesson->day1, $otherLesson->day2] as $day) {
                if (!$day) continue;
                $date = Carbon::parse($startDate);
                while ($date->lte($endDate)) {
                    if ($date->isoFormat('ddd') === $day && $date->between($startOfYear, $endOfYear)) {
                        $start_time = ($day === $otherLesson->day1) ? $otherLesson->start_time1 : $otherLesson->start_time2;
                        $rescheduleCandidates->push([
                            'date' => $date->copy(),
                            'weekday' => $day,
                            'start_time' => $start_time,
                            'lesson_id' => $otherLesson->id,
                        ]);
                    }
                    $date->addDay();
                }
            }
        }

        // 日付で昇順にソート
        $sorted = $rescheduleCandidates->sortBy('date')->values();

        $page = request()->get('page', 1);
        $perPage = 10;

        $paginator = new LengthAwarePaginator(
            $sorted->forPage($page, $perPage),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('admin.status.admin_makeup', [
            'userLesson' => $userLesson,
            'rescheduleCandidates' => $paginator,
            'otherSchools' => $otherSchools,
            'selectedSchoolId' => $selectedSchoolId, // 選択状態保持用
            'statusId' => $statusId,
        ]);
    }

    public function makeupUpdate(Request $request, $userLessonId)
    {
        $request->validate([
            'date' => 'required|date',
            'lesson_id' => 'required|integer|exists:lessons,id',
            'status_id' => 'required|integer|exists:user_lesson_statuses,id',
        ]);

        // status_id から取得
        $status = UserLessonStatus::findOrFail($request->status_id);

        // reschedule_to に振替日だけ保存
        $status->reschedule_to = $request->date;
        $status->save();

        $newUserLesson = UserLesson::firstOrCreate([
            'user_id' => $status->userLesson->user_id,
            'lesson_id' => $request->lesson_id,
        ], [
            'start_date' => now(),
            'end_date' => now()->addMonth(),
        ]);

        Reschedule::firstOrCreate([
            'user_lesson_status_id' => $status->id,
            'new_user_lesson_id' => $newUserLesson->id,
        ]);

        return redirect()->route('admin.student.detail', ['id' => $status->userLesson->user_id])
    ->with('success', '振替を登録しました');
    }



    //
}
