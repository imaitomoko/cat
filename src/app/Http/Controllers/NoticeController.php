<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NewsList;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = NewsList::all();
        return view('admin.notice', compact('notices'));
    }

    // 新規登録処理
    public function store(Request $request)
    {
        $request->validate([
            'news_list' => 'required|string',
        ]);

        NewsList::create($request->only('news_list'));

        return redirect()->route('notices.index')->with('success', 'お知らせを登録しました。');
    }

    // 削除処理
    public function destroy($id)
    {
        $notice = NewsList::findOrFail($id);
        $notice->delete();

        return redirect()->route('notices.index')->with('success', 'お知らせを削除しました。');
    }
    //
}
