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

    public function makeupShow($userLessonStatusId)
    {
        $userLessonStatus = UserLessonStatus::with('userLesson.lesson')->findOrFail($userLessonStatusId);
        $user = $userLessonStatus->userLesson->user;
        $currentLesson = $userLessonStatus->userLesson->lesson;
    
        $startDate = now()->subMonth();
        $endDate = now()->addMonth();

        if (!function_exists('getNearestDate')) {
            function getNearestDate($japaneseDay)
            {
                // 日本語の曜日を Carbon の曜日番号に変換
                $weekMap = [
                    '日' => 0,
                    '月' => 1,
                    '火' => 2,
                    '水' => 3,
                    '木' => 4,
                    '金' => 5,
                    '土' => 6,
                ];

                if (!isset($weekMap[$japaneseDay])) {
                     return '日付不明'; // 万が一、曜日が間違っていた場合
                }

                // 今日の日付
                $today = Carbon::today();

                // 次に来る該当曜日の日付を取得
                $targetDate = $today->copy()->next($weekMap[$japaneseDay]);

                 // m/d 形式で返す
                return $targetDate->format('m/d');
            }
        }

        // そのクラスの別の曜日で、かつユーザーがまだ受講していないレッスンを取得
        $availableLessons = Lesson::where('school_class_id', $currentLesson->school_class_id)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->where(function ($query) use ($currentLesson) {
                $query->where('day1', '!=', $currentLesson->day1)
                        ->orWhere('day2', '!=', $currentLesson->day1);
            })
            ->whereDoesntHave('userLessons.userLessonStatus', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        // そのクラスがある他の教室を取得
        $otherSchools = School::whereHas('classes', function ($query) use ($currentLesson) {
            $query->where('id', $currentLesson->school_class_id);
        })->get();

        return view('admin.reschedule.form', compact('userLessonStatus', 'availableLessons', 'otherSchools', 'user'));
    }

    public function reschedule(Request $request)
    {
        $validated = $request->validate([
            'user_lesson_status_id' => 'required|exists:user_lesson_statuses,id',
            'new_lesson_id' => 'required|exists:lessons,id',
        ]);

        $originalLessonStatus = UserLessonStatus::findOrFail($validated['user_lesson_status_id']);
        $newLesson = Lesson::findOrFail($validated['new_lesson_id']);

        // 振替先の UserLesson を取得または作成
        $newUserLesson = UserLesson::firstOrCreate([
            'user_id' => $originalLessonStatus->userLesson->user_id,
            'lesson_id' => $newLesson->id,
        ]);

        // 新しい UserLessonStatus を作成
        $newUserLessonStatus = UserLessonStatus::create([
            'user_lesson_id' => $newUserLesson->id,
            'date' => $newLesson->start_date,
            'status' => '振替',
        ]);

        // Reschedule モデルを保存（振替情報を記録）
        Reschedule::create([
            'user_lesson_status_id' => $originalLessonStatus->id,
            'new_user_lesson_id' => $newUserLesson->id,
        ]);

        return redirect()->route('admin.status.makeup')->with('success', '振替予約が完了しました。');
    }



    //
}
