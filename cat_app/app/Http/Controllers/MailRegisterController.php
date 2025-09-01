<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MailRegisterController extends Controller
{
    public function index ()
    {
        $user = Auth::user();

        return view('mailRegister', compact( 'user')); 
    }

    public function updateEmail(Request $request)
    {
        // バリデーション
        //$request->validate([
           // 'email' => 'required|email|unique:users,email', // 新しいメールアドレスが必須で、ユニークであることを確認
        //]);

        $user = Auth::user();

        $user->email = $request->email;
        $user->save();

        // 成功メッセージとともにリダイレクト
        return redirect('/mail')->with('success', 'メールアドレスを更新しました。');
    }
    //
}
