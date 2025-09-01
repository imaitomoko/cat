<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.admin_login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'admin_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('admin_id', 'password');

        $admin = Admin::where('admin_id', $credentials['admin_id'])->first();

        if ($admin && Hash::check($credentials['password'], $admin->password)) {
        // 認証成功 
            Auth::guard('admin')->login($admin);

            return redirect()->route('admin.admin');
        }

        return back()->withErrors([
            'admin_id' => '管理者IDが見つかりません',
            'password' => 'パスワードが正しくありません',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout(); // guardを使っている場合
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function index()
    {
        return view('admin.admin');
    }

    //
}
