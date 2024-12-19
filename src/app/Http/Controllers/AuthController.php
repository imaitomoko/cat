<?php

namespace App\Http\Controllers;

use App\Models\NewsList;
use App\Models\UserLesson; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index()
    {
        $news = NewsList::all();
        $user = Auth::user();
        $contents = UserLesson::join('users', 'user_lessons.user_id', '=', 'users.id')
                ->join('lessons', 'user_lessons.lesson_id', '=', 'lessons.id')
                ->join('schools', 'lessons.school_id', '=', 'schools.id')
                ->join('classes', 'lessons.class_id', '=', 'classes.id')
                ->where('user_lessons.user_id', $user->id)
                ->get(['users.user_name', 'schools.school_name', 'classes.class_name']);

        return view('index', compact('news', 'user', 'contents'));
    }
    //
}
