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

        return view('index', compact('news', 'user'));
    }
    //
}
