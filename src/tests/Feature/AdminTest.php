<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\RestTime;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト時刻を固定（2026年4月11日 10:00:00）
        Carbon::setTestNow(Carbon::create(2026, 4, 11, 10, 0, 0));

        $this->admin = User::create([
            'name' => '管理者太郎',
            'email' => 'admin@example.com',
            'password' => 'ysnb5884',
            'role' => 'admin',
        ]);

        $this->user = User::create([
            'name' => 'テスト花子',
            'email' => 'hanako@example.com',
            'password' => 'password1234',
            'role' => 'user',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /* ==========================================================
     * ID12: 勤怠一覧情報取得機能（管理者）
     * ========================================================== */

    /** @test その日になされた全ユーザーの勤怠情報が正確に確認できる */
    public function 管理者勤怠一覧_その日の全ユーザー勤怠情報が表示される()
    {
        $user2 = User::create([
            'name' => 'テスト次郎',
            'email' => 'jiro@example.com',
            'password' => 'password1234',
            'role' => 'user',
        ]);

        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-11',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);
        Attendance::create([
            'user_id' => $user2->id,
            'work_date' => '2026-04-11',
            'clock_in' => '10:15:00',
            'clock_out' => '19:30:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('テスト花子');
        $response->assertSee('テスト次郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('10:15');
        $response->assertSee('19:30');
    }

    /** @test 遷移した際に現在の日付が表示される */
    public function 管理者勤怠一覧_遷移時に現在の日付が表示される()
    {
        $response = $this->actingAs($this->admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2026年4月11日');
        $response->assertSee('2026/04/11');
    }

    /** @test 前日ボタンを押した時に前日の勤怠情報が表示される */
    public function 管理者勤怠一覧_前日ボタンで前日の勤怠情報が表示される()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/attendance/list?date=2026-04-10');

        $response->assertStatus(200);
        $response->assertSee('2026年4月10日');
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }

    /** @test 翌日ボタンを押した時に翌日の勤怠情報が表示される */
    public function 管理者勤怠一覧_翌日ボタンで翌日の勤怠情報が表示される()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-12',
            'clock_in' => '08:45:00',
            'clock_out' => '17:45:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/attendance/list?date=2026-04-12');

        $response->assertStatus(200);
        $response->assertSee('2026年4月12日');
        $response->assertSee('08:45');
        $response->assertSee('17:45');
    }

    /* ==========================================================
     * ID13: 勤怠詳細情報取得・修正機能（管理者）
     * ========================================================== */

    /** @test 勤怠詳細画面に表示されるデータが選択したものになっている */
    public function 管理者勤怠詳細_選択した勤怠のデータが表示される()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:15:00',
            'clock_out' => '18:25:00',
            'status' => '退勤済',
        ]);
        $attendance->restTimes()->create([
            'rest_start' => '12:30:00',
            'rest_end' => '13:30:00',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('テスト花子');
        $response->assertSee('2026年4月10日');
        $response->assertSee('09:15');
        $response->assertSee('18:25');
        $response->assertSee('12:30');
        $response->assertSee('13:30');
    }

    /** @test 出勤時間が退勤時間より後の場合エラーメッセージが表示される */
    public function 管理者勤怠詳細_出勤時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->admin)
            ->from('/admin/attendance/' . $attendance->id)
            ->followingRedirects()
            ->post('/admin/attendance/' . $attendance->id, [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'rest_start' => [],
                'rest_end' => [],
                'rest_id' => [],
                'remarks' => 'テスト修正理由',
            ]);

        $response->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    /** @test 休憩開始時間が退勤時間より後の場合エラーメッセージが表示される */
    public function 管理者勤怠詳細_休憩開始時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->admin)
            ->from('/admin/attendance/' . $attendance->id)
            ->followingRedirects()
            ->post('/admin/attendance/' . $attendance->id, [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'rest_start' => ['19:00'],
                'rest_end' => ['20:00'],
                'rest_id' => [''],
                'remarks' => 'テスト修正理由',
            ]);

        $response->assertSee('休憩時間が不適切な値です');
    }

    /** @test 休憩終了時間が退勤時間より後の場合エラーメッセージが表示される */
    public function 管理者勤怠詳細_休憩終了時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->admin)
            ->from('/admin/attendance/' . $attendance->id)
            ->followingRedirects()
            ->post('/admin/attendance/' . $attendance->id, [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'rest_start' => ['12:00'],
                'rest_end' => ['19:00'],
                'rest_id' => [''],
                'remarks' => 'テスト修正理由',
            ]);

        $response->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

    /** @test 備考欄が未入力の場合エラーメッセージが表示される */
    public function 管理者勤怠詳細_備考欄が未入力の場合エラーメッセージが表示される()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->admin)
            ->from('/admin/attendance/' . $attendance->id)
            ->followingRedirects()
            ->post('/admin/attendance/' . $attendance->id, [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'rest_start' => [],
                'rest_end' => [],
                'rest_id' => [],
                'remarks' => '',
            ]);

        $response->assertSee('備考を記入してください');
    }

    /* ==========================================================
     * ID14: ユーザー情報取得機能（管理者）
     * ========================================================== */

    /** @test 管理者ユーザーが全一般ユーザーの氏名・メールアドレスを確認できる */
    public function 管理者スタッフ一覧_全一般ユーザーの氏名とメールアドレスが表示される()
    {
        $user2 = User::create([
            'name' => 'テスト次郎',
            'email' => 'jiro@example.com',
            'password' => 'password1234',
            'role' => 'user',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSee('テスト花子');
        $response->assertSee('hanako@example.com');
        $response->assertSee('テスト次郎');
        $response->assertSee('jiro@example.com');
    }

    /** @test ユーザーの勤怠情報が正しく表示される */
    public function 管理者スタッフ別勤怠_ユーザーの勤怠情報が正しく表示される()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-05',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '10:15:00',
            'clock_out' => '19:30:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/attendance/staff/' . $this->user->id);

        $response->assertStatus(200);
        $response->assertSee('テスト花子');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('10:15');
        $response->assertSee('19:30');
    }

    /** @test 前月ボタンを押した時に前月の情報が表示される */
    public function 管理者スタッフ別勤怠_前月ボタンで前月の勤怠情報が表示される()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-03-15',
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/attendance/staff/' . $this->user->id . '?month=2026-03');

        $response->assertStatus(200);
        $response->assertSee('2026/03');
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }

    /** @test 翌月ボタンを押した時に翌月の情報が表示される */
    public function 管理者スタッフ別勤怠_翌月ボタンで翌月の勤怠情報が表示される()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-05-15',
            'clock_in' => '08:45:00',
            'clock_out' => '17:45:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/attendance/staff/' . $this->user->id . '?month=2026-05');

        $response->assertStatus(200);
        $response->assertSee('2026/05');
        $response->assertSee('08:45');
        $response->assertSee('17:45');
    }

    /** @test 詳細を押すとその日の勤怠詳細画面に遷移する */
    public function 管理者スタッフ別勤怠_詳細リンクで勤怠詳細画面に遷移する()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        $listResponse = $this->actingAs($this->admin)
            ->get('/admin/attendance/staff/' . $this->user->id);
        $listResponse->assertSee('/admin/attendance/' . $attendance->id);

        $detailResponse = $this->actingAs($this->admin)
            ->get('/admin/attendance/' . $attendance->id);
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('テスト花子');
        $detailResponse->assertSee('2026年4月10日');
    }

    /* ==========================================================
     * ID15: 勤怠情報修正機能（管理者）
     * ========================================================== */

    /** @test 承認待ちの修正申請が全て表示されている */
    public function 管理者修正申請一覧_承認待ちの申請が全て表示される()
    {
        $attendance1 = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-09',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);
        $attendance2 = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        StampCorrectionRequest::create([
            'attendance_id' => $attendance1->id,
            'user_id' => $this->user->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remarks' => '承認待ち申請Aです',
            'status' => '承認待ち',
        ]);
        StampCorrectionRequest::create([
            'attendance_id' => $attendance2->id,
            'user_id' => $this->user->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remarks' => '承認待ち申請Bです',
            'status' => '承認待ち',
        ]);

        $response = $this->actingAs($this->admin)->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('承認待ち申請Aです');
        $response->assertSee('承認待ち申請Bです');
        $response->assertSee('テスト花子');
    }

    /** @test 承認済みの修正申請が全て表示されている */
    public function 管理者修正申請一覧_承認済みの申請が全て表示される()
    {
        $attendance1 = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-09',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);
        $attendance2 = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        StampCorrectionRequest::create([
            'attendance_id' => $attendance1->id,
            'user_id' => $this->user->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remarks' => '承認済み申請Aです',
            'status' => '承認済み',
        ]);
        StampCorrectionRequest::create([
            'attendance_id' => $attendance2->id,
            'user_id' => $this->user->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remarks' => '承認済み申請Bです',
            'status' => '承認済み',
        ]);

        $response = $this->actingAs($this->admin)->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('承認済み申請Aです');
        $response->assertSee('承認済み申請Bです');
    }

    /** @test 修正申請の詳細内容が正しく表示されている */
    public function 管理者修正申請詳細_申請の詳細内容が正しく表示される()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);
        $attendance->restTimes()->create([
            'rest_start' => '12:00:00',
            'rest_end' => '13:00:00',
        ]);

        $correctionRequest = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $this->user->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remarks' => '電車遅延のため修正申請',
            'status' => '承認待ち',
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/stamp_correction_request/approve/' . $correctionRequest->id);

        $response->assertStatus(200);
        $response->assertSee('テスト花子');
        $response->assertSee('2026年4月10日');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('電車遅延のため修正申請');
    }

    /** @test 修正申請の承認処理が正しく行われる */
    public function 管理者修正申請承認_承認処理が正しく行われる()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        $correctionRequest = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $this->user->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remarks' => '承認対象の申請',
            'status' => '承認待ち',
        ]);

        $this->actingAs($this->admin)
            ->post('/stamp_correction_request/approve/' . $correctionRequest->id);

        // 申請ステータスが承認済みに更新されている
        $this->assertDatabaseHas('stamp_correction_requests', [
            'id' => $correctionRequest->id,
            'status' => '承認済み',
        ]);

        // 勤怠情報が申請内容で更新されている
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);
    }
}
