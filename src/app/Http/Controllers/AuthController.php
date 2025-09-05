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
        $today = now()->toDateString();
        $news = NewsList::where('post_date', '<=', $today) // 掲載開始日が今日以前
                        ->where('end_date', '>=', $today)  // 掲載終了日が今日以降
                        ->orderBy('post_date', 'asc')
                        ->get();

        $user = Auth::user();

        return view('index', compact('news', 'user'));
    }
    //
}
