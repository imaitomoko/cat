<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Mail;
use App\Models\SendTo;
use App\Models\UserLesson;
use App\Models\Lesson;
use App\Models\School;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\Mail as MailSender;
use App\Mail\LessonNotificationMail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class MailController extends Controller
{
    public function index()
    {
        // 最新30件のIDを取得
        $latestMailIds = Mail::orderBy('sent_at', 'desc')
                        ->limit(30)
                        ->pluck('id');

        // それ以外の古いレコードを削除
        Mail::whereNotIn('id', $latestMailIds)->delete();

       // 最新30件を表示用に取得
        $mails = Mail::whereIn('id', $latestMailIds)
                    ->orderBy('sent_at', 'desc')
                    ->paginate(10);

        return view('admin.mail.mail', compact('mails'));
    }

    public function create()
    {
        return view('admin.mail.mail_create');
    }

    public function sendTo(Request $request)
    {
        // 送信先設定画面で送信先を選択するロジック
        // ここでは例として選択した送信先をセッションに保存
        $sendTo = $request->input('send_to');
        session(['selected_send_to' => $sendTo]); // セッションに保存

        return redirect()->route('admin.mails.create');
    }

    public function search()
    {
        $years = Lesson::select('year')->distinct()->get();

        return view('admin.mail.mail_sendTo', compact('years'));
    }

    // 年度に基づく学校の取得
    public function getSchoolsByYear($year)
    {
        $schools = School::whereHas('lessons', function ($query) use ($year) {
            $query->where('year', $year);
        })->get();

        return response()->json(['schools' => $schools]);
    }

    // 学校に基づくクラスの取得
    public function getClassesBySchool($schoolId)
    {
        $year = request()->query('year');

        $classIds = Lesson::where('school_id', $schoolId)
                            ->where('year', $year)
                            ->pluck('class_id')
                            ->unique();

        $classes = SchoolClass::whereIn('id', $classIds)->get();

        return response()->json(['classes' => $classes]);
    }

    // クラスに基づく曜日の取得
    public function getDaysByClass($classId)
    {

        $schoolId = request()->query('school_id');
        $year = request()->query('year');

        try {
            $lessons = Lesson::where('class_id', $classId)
                            ->where('school_id', $schoolId)
                            ->where('year', $year)
                            ->get();

            $days = [];

            foreach ($lessons as $lesson) {
                if ($lesson->day1 && !in_array($lesson->day1, $days)) {
                    $days[] = $lesson->day1;
                }
                if ($lesson->day2 && !in_array($lesson->day2, $days)) {
                    $days[] = $lesson->day2;
                }
            }

            return response()->json(['days' => array_values($days)]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching days: ' . $e->getMessage()]);
        }
    }

    public function result(Request $request)
    {
        /// 教室名を取得
        $school = School::find($request->school_id);
        $schoolName = $school ? $school->school_name : '-';

        // クラス名を取得
        $class = SchoolClass::find($request->class_id);
        $className = $class ? $class->class_name : '-';

        // 年度、教室名、クラス名、曜日のデータをセッションに保存
        $selectedSendTo = $request->year . "年度  / "  . $schoolName .  " / "  . $className .  " / " . $request->day;
        // セッションに保存
        session(['selected_send_to' => $selectedSendTo]);
        
        $userLessonIds = UserLesson::whereHas('lesson', function ($q) use ($request) {
            $q->where('year', $request->year)
                ->where('school_id', $request->school_id)
                ->where(function ($query) use ($request) {
                    $query->where('day1', $request->day)
                        ->orWhere('day2', $request->day);
                });
        })
        ->pluck('id')
        ->toArray();

        // セッションに保存
        Session::put('send_to_user_lesson_ids', $userLessonIds);

        return redirect()->route('admin.mails.create');
    }

    public function confirm(Request $request)
    {

        if (!Session::has('send_to_user_lesson_ids')) {
        return redirect()->route('admin.mails.create')->with('error', '送信先を先に設定してください。');
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:20',
            'body' => 'required|string',
            'attachment' => 'nullable|file|max:2048',
        ]);

        // ファイルは一時的に保存
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('temp', 'public');
            $validated['attachment'] = $path;
        }

        // 一時データをセッションに保存
        Session::put('mail_data', $validated);

        $mailData = Session::get('mail_data');

        $sendTo = session('selected_send_to');

        return view('admin.mail.mail_confirm', ['mail' => $validated, 'sendTo' => $sendTo,]);
    }

    public function store(Request $request)
    {
        $data = Session::get('mail_data');
        $sendToData = Session::get('send_to_user_lesson_ids'); // ← user_lesson_idの配列が入っている想定
        $sendToText = Session::get('selected_send_to'); 

        if (!$data || !$sendToData) {
            return redirect()->route('admin.mails.create')->with('error', 'セッションが切れています。');
        }

        // 添付ファイルの保存（temp → permanent）
        if (!empty($data['attachment'])) {
            $oldPath = $data['attachment'];
            $newPath = str_replace('temp/', 'attachments/', $oldPath);
            Storage::disk('public')->move($oldPath, $newPath);
            $data['attachment'] = $newPath;
        }

    // メール情報を保存
        $mail = Mail::create([
            'subject' => $data['subject'],
            'body' => $data['body'],
            'attachment' => $data['attachment'] ?? null,
            'send_to_text' => $sendToText,
            'sent_at' => now(),
        ]);

        // SendToの登録と実際のメール送信
        foreach ($sendToData as $userLessonId) {
            $mail->sendTos()->create([
                'user_lesson_id' => $userLessonId,
            ]);

            // メール送信
            $userLesson = UserLesson::with('user')->find($userLessonId);
            $user = $userLesson->user;

            if ($user && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                $mailData = [
                    'subject' => $data['subject'],
                    'body' => $data['body'],
                    'attachment' => $data['attachment'] ?? null,
                ];
                MailSender::to($user->email)->send(new LessonNotificationMail($mailData));
            }
        }

        // セッション削除
        Session::forget('mail_data');
        Session::forget('send_to_user_lesson_ids');
        Session::forget('selected_send_to');

        return redirect()->route('admin.mails.index')->with('success', 'メールを送信しました。');
    }

    
    
    //
}
