<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /* ==========================================================
     * ID16: メール認証機能
     * ========================================================== */

    /** @test 会員登録後、認証メールが送信される */
    public function 会員登録後に認証メールが送信される()
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** @test メール認証誘導画面で「認証はこちらから」ボタンを押すと認証メールが再送される */
    public function 認証はこちらからボタンを押すと認証メールが再送される()
    {
        Notification::fake();

        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password1234',
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->post('/email/verification-notification');

        $response->assertStatus(302);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** @test メール認証を完了すると勤怠登録画面に遷移する */
    public function メール認証完了後に勤怠登録画面に遷移する()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password1234',
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        // メール未認証状態では/attendanceにアクセスすると/email/verifyにリダイレクトされる
        $response = $this->get('/attendance');
        $response->assertRedirect('/email/verify');

        // メール認証用の署名付きURLを生成してアクセス
        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        // 認証完了後、勤怠登録画面（/attendance）にリダイレクトされる
        $response->assertRedirect('/attendance');

        // ユーザーのメール認証が完了していることを確認
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
