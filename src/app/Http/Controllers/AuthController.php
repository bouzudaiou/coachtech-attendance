<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AdminLoginRequest;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.admin_login');
    }

    public function login(AdminLoginRequest $request)
    {
        // ① フォームのメール・パスワードで認証を試みる
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // ② 認証成功 → roleがadminか確認
            if (auth()->user()->role === 'admin') {
                return redirect('/admin/attendance/list');
            }
            // roleがuserだった場合はログアウトしてエラー
            Auth::logout();
            return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
        }

        // ③ 認証失敗
        return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
    }
}
