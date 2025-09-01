<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NewsList;

class NoticeController extends Controller
{
    public function index()
    {
        $maxNotices = 20;
        $noticesToKeep = NewsList::orderBy('created_at', 'desc')->take($maxNotices)->pluck('id');

    // それ以外を削除
        NewsList::whereNotIn('id', $noticesToKeep)->delete();

    // ページネーションで表示（1ページ5件）
        $notices = NewsList::orderBy('created_at', 'desc')->paginate(5);
        return view('admin.notice', compact('notices'));
    }

    // 新規登録処理
    public function store(Request $request)
    {
        $request->validate([
            'post_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:post_date',
            'news_list' => 'required|string',
        ]);

        NewsList::create([
            'news_list' => $request->input('news_list'),
            'post_date' => $request->input('post_date'),
            'end_date' => $request->input('end_date'),
        ]);

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
