<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /* ==========================================================
     * ID1: 認証機能（一般ユーザー）― 会員登録
     * ========================================================== */

    /** @test 名前が未入力の場合、バリデーションメッセージが表示される */
    public function 会員登録_名前が未入力の場合はバリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->followingRedirects()->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
        ]);

        $response->assertSee('お名前を入力してください');
    }

    /** @test メールアドレスが未入力の場合、バリデーションメッセージが表示される */
    public function 会員登録_メールアドレスが未入力の場合はバリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->followingRedirects()->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
        ]);

        $response->assertSee('メールアドレスを入力してください');
    }

    /** @test パスワードが8文字未満の場合、バリデーションメッセージが表示される */
    public function 会員登録_パスワードが8文字未満の場合はバリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->followingRedirects()->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'pass12',
            'password_confirmation' => 'pass12',
        ]);

        $response->assertSee('パスワードは8文字以上で入力してください');
    }

    /** @test パスワードが一致しない場合、バリデーションメッセージが表示される */
    public function 会員登録_パスワードと確認用が一致しない場合はバリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->followingRedirects()->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password1234',
            'password_confirmation' => 'different1234',
        ]);

        $response->assertSee('パスワードと一致しません');
    }

    /** @test パスワードが未入力の場合、バリデーションメッセージが表示される */
    public function 会員登録_パスワードが未入力の場合はバリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->followingRedirects()->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSee('パスワードを入力してください');
    }

    /** @test フォームに正しい内容を入力した場合、データが正常に保存される */
    public function 会員登録_正しい内容を入力した場合はデータが正常に保存される()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'role' => 'user',
        ]);
    }

    /* ==========================================================
     * ID2: ログイン認証機能（一般ユーザー）
     * ========================================================== */

    /** @test 一般ユーザーログイン：メールアドレスが未入力の場合 */
    public function 一般ユーザーログイン_メールアドレスが未入力の場合はバリデーションメッセージが表示される()
    {
        $response = $this->from('/login')->followingRedirects()->post('/login', [
            'email' => '',
            'password' => 'password1234',
        ]);

        $response->assertSee('メールアドレスを入力してください');
    }

    /** @test 一般ユーザーログイン：パスワードが未入力の場合 */
    public function 一般ユーザーログイン_パスワードが未入力の場合はバリデーションメッセージが表示される()
    {
        $response = $this->from('/login')->followingRedirects()->post('/login', [
            'email' => 'taro@example.com',
            'password' => '',
        ]);

        $response->assertSee('パスワードを入力してください');
    }

    /** @test 一般ユーザーログイン：登録内容と一致しない場合 */
    public function 一般ユーザーログイン_登録内容と一致しない場合はエラーメッセージが表示される()
    {
        User::create([
            'name' => 'taro',
            'email' => 'taro@example.com',
            'password' => 'password1234',
            'role' => 'user',
        ]);

        $response = $this->from('/login')->followingRedirects()->post('/login', [
            'email' => 'taro@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSee('ログイン情報が登録されていません');
    }

    /* ==========================================================
     * ID3: ログイン認証機能（管理者）
     * ========================================================== */

    /** @test 管理者ログイン：メールアドレスが未入力の場合 */
    public function 管理者ログイン_メールアドレスが未入力の場合はバリデーションメッセージが表示される()
    {
        $response = $this->from('/admin/login')->followingRedirects()->post('/admin/login', [
            'email' => '',
            'password' => 'ysnb5884',
        ]);

        $response->assertSee('メールアドレスを入力してください');
    }

    /** @test 管理者ログイン：パスワードが未入力の場合 */
    public function 管理者ログイン_パスワードが未入力の場合はバリデーションメッセージが表示される()
    {
        $response = $this->from('/admin/login')->followingRedirects()->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSee('パスワードを入力してください');
    }

    /** @test 管理者ログイン：登録内容と一致しない場合 */
    public function 管理者ログイン_登録内容と一致しない場合はエラーメッセージが表示される()
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'ysnb5884',
            'role' => 'admin',
        ]);

        $response = $this->from('/admin/login')->followingRedirects()->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSee('ログイン情報が登録されていません');
    }
}
