<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\RestTime;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト時刻を固定（2026年4月11日 10:00:00）
        Carbon::setTestNow(Carbon::create(2026, 4, 11, 10, 0, 0));

        $this->user = User::create([
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
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
     * ID4: 日時取得機能
     * ========================================================== */

    /** @test 現在の日時情報がUI上で表示されている */
    public function 勤怠打刻画面_現在日時が画面に表示される()
    {
        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('2026年4月11日');
        $response->assertSee('10:00');
    }

    /* ==========================================================
     * ID5: ステータス確認機能
     * ========================================================== */

    /** @test 勤務外ステータスが正しく表示される */
    public function ステータス_勤務外が画面に表示される()
    {
        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertSee('勤務外');
    }

    /** @test 出勤中ステータスが正しく表示される */
    public function ステータス_出勤中が画面に表示される()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'status' => '出勤中',
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertSee('出勤中');
    }

    /** @test 休憩中ステータスが正しく表示される */
    public function ステータス_休憩中が画面に表示される()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'status' => '休憩中',
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertSee('休憩中');
    }

    /** @test 退勤済ステータスが正しく表示される */
    public function ステータス_退勤済が画面に表示される()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertSee('退勤済');
    }

    /* ==========================================================
     * ID6: 出勤機能
     * ========================================================== */

    /** @test 出勤ボタンを押すとステータスが出勤中になる */
    public function 出勤_ボタンを押すとステータスが出勤中に変わる()
    {
        $response = $this->actingAs($this->user)
            ->followingRedirects()
            ->post('/attendance', ['action' => 'clock_in']);

        $response->assertSee('出勤中');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'status' => '出勤中',
        ]);
    }

    /** @test 出勤は一日に一回のみである */
    public function 出勤_一日一回のみ有効()
    {
        // 2回POSTしても勤怠レコードは1件のみ作成される
        $this->actingAs($this->user)->post('/attendance', ['action' => 'clock_in']);
        $this->actingAs($this->user)->post('/attendance', ['action' => 'clock_in']);

        $this->assertEquals(
            1,
            Attendance::where('user_id', $this->user->id)
                ->whereDate('work_date', now()->toDateString())
                ->count()
        );
    }

    /** @test 出勤時刻が勤怠一覧画面で確認できる */
    public function 出勤_出勤時刻が勤怠一覧画面で確認できる()
    {
        $this->actingAs($this->user)->post('/attendance', ['action' => 'clock_in']);

        $response = $this->actingAs($this->user)->get('/attendance/list');

        $response->assertSee('10:00');
    }

    /* ==========================================================
     * ID7: 休憩機能
     * ========================================================== */

    /** @test 休憩入ボタンを押すとステータスが休憩中になる */
    public function 休憩_入ボタンでステータスが休憩中になる()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'status' => '出勤中',
        ]);

        $response = $this->actingAs($this->user)
            ->followingRedirects()
            ->post('/attendance', ['action' => 'rest_start']);

        $response->assertSee('休憩中');
    }

    /** @test 休憩は一日に何回でもできる */
    public function 休憩_一日に何回でも取得できる()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'status' => '出勤中',
        ]);

        // 1回目
        $this->actingAs($this->user)->post('/attendance', ['action' => 'rest_start']);
        $this->actingAs($this->user)->post('/attendance', ['action' => 'rest_end']);

        // 2回目
        $this->actingAs($this->user)->post('/attendance', ['action' => 'rest_start']);
        $this->actingAs($this->user)->post('/attendance', ['action' => 'rest_end']);

        $this->assertEquals(
            2,
            RestTime::where('attendance_id', $attendance->id)->count()
        );
    }

    /** @test 休憩戻ボタンを押すとステータスが出勤中になる */
    public function 休憩_戻ボタンでステータスが出勤中に戻る()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'status' => '休憩中',
        ]);
        $attendance->restTimes()->create(['rest_start' => '09:30:00']);

        $response = $this->actingAs($this->user)
            ->followingRedirects()
            ->post('/attendance', ['action' => 'rest_end']);

        $response->assertSee('出勤中');
    }

    /** @test 休憩戻も一日に何回でもできる */
    public function 休憩_戻も一日に何回でも実行できる()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'status' => '出勤中',
        ]);

        $this->actingAs($this->user)->post('/attendance', ['action' => 'rest_start']);
        $this->actingAs($this->user)->post('/attendance', ['action' => 'rest_end']);
        $this->actingAs($this->user)->post('/attendance', ['action' => 'rest_start']);
        $this->actingAs($this->user)->post('/attendance', ['action' => 'rest_end']);

        $this->assertEquals(
            2,
            RestTime::where('attendance_id', $attendance->id)
                ->whereNotNull('rest_end')
                ->count()
        );
    }

    /** @test 休憩時刻が勤怠一覧画面で確認できる */
    public function 休憩_休憩時間が勤怠一覧画面で確認できる()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);
        $attendance->restTimes()->create([
            'rest_start' => '12:00:00',
            'rest_end' => '13:00:00',
        ]);

        $response = $this->actingAs($this->user)->get('/attendance/list');

        // 合計休憩 1:00 が表示される
        $response->assertSee('1:00');
    }

    /* ==========================================================
     * ID8: 退勤機能
     * ========================================================== */

    /** @test 退勤ボタンを押すとステータスが退勤済になる */
    public function 退勤_ボタンを押すとステータスが退勤済になる()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'status' => '出勤中',
        ]);

        $response = $this->actingAs($this->user)
            ->followingRedirects()
            ->post('/attendance', ['action' => 'clock_out']);

        $response->assertSee('退勤済');
    }

    /** @test 退勤時刻が勤怠一覧画面で確認できる */
    public function 退勤_退勤時刻が勤怠一覧画面で確認できる()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'status' => '出勤中',
        ]);

        $this->actingAs($this->user)->post('/attendance', ['action' => 'clock_out']);

        $response = $this->actingAs($this->user)->get('/attendance/list');

        $response->assertSee('10:00');
    }

    /* ==========================================================
     * ID9: 勤怠一覧情報取得機能（一般ユーザー）
     * ========================================================== */

    /** @test 自分が行った勤怠情報が全て表示されている */
    public function 勤怠一覧_自分の勤怠情報が全て表示される()
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

        $response = $this->actingAs($this->user)->get('/attendance/list');

        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('10:15');
        $response->assertSee('19:30');
    }

    /** @test 勤怠一覧画面に遷移した際に現在の月が表示される */
    public function 勤怠一覧_現在の月が表示される()
    {
        $response = $this->actingAs($this->user)->get('/attendance/list');

        $response->assertSee('2026/04');
    }

    /** @test 前月ボタンを押すと前月の情報が表示される */
    public function 勤怠一覧_前月ボタンで前月の情報が表示される()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-03-15',
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->user)->get('/attendance/list?month=2026-03');

        $response->assertSee('2026/03');
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }

    /** @test 翌月ボタンを押すと翌月の情報が表示される */
    public function 勤怠一覧_翌月ボタンで翌月の情報が表示される()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-05-15',
            'clock_in' => '08:45:00',
            'clock_out' => '17:45:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->user)->get('/attendance/list?month=2026-05');

        $response->assertSee('2026/05');
        $response->assertSee('08:45');
        $response->assertSee('17:45');
    }

    /** @test 詳細を押すとその日の勤怠詳細画面に遷移する */
    public function 勤怠一覧_詳細リンクで勤怠詳細画面に遷移する()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        $listResponse = $this->actingAs($this->user)->get('/attendance/list');
        $listResponse->assertSee('/attendance/detail/' . $attendance->id);

        $detailResponse = $this->actingAs($this->user)->get('/attendance/detail/' . $attendance->id);
        $detailResponse->assertStatus(200);
    }

    /* ==========================================================
     * ID10: 勤怠詳細情報取得機能（一般ユーザー）
     * ========================================================== */

    /** @test 勤怠詳細画面の名前がログインユーザーの氏名である */
    public function 勤怠詳細_名前がログインユーザーの氏名である()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->user)->get('/attendance/detail/' . $attendance->id);

        $response->assertSee('テスト太郎');
    }

    /** @test 勤怠詳細画面の日付が選択した日付である */
    public function 勤怠詳細_日付が選択した日付である()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->user)->get('/attendance/detail/' . $attendance->id);

        $response->assertSee('2026年4月10日');
    }

    /** @test 出勤退勤時間がログインユーザーの打刻と一致している */
    public function 勤怠詳細_出勤退勤時間が打刻と一致する()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:15:00',
            'clock_out' => '18:25:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->user)->get('/attendance/detail/' . $attendance->id);

        $response->assertSee('09:15');
        $response->assertSee('18:25');
    }

    /** @test 休憩時間が打刻と一致している */
    public function 勤怠詳細_休憩時間が打刻と一致する()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);
        $attendance->restTimes()->create([
            'rest_start' => '12:30:00',
            'rest_end' => '13:30:00',
        ]);

        $response = $this->actingAs($this->user)->get('/attendance/detail/' . $attendance->id);

        $response->assertSee('12:30');
        $response->assertSee('13:30');
    }

    /* ==========================================================
     * ID11: 勤怠詳細情報修正機能（一般ユーザー）
     * ========================================================== */

    /** @test 出勤時間が退勤時間より後の場合エラーメッセージが表示される */
    public function 修正_出勤時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->user)
            ->from('/attendance/detail/' . $attendance->id)
            ->followingRedirects()
            ->post('/attendance/detail/' . $attendance->id, [
                'action' => 'correction',
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'rest_start' => [],
                'rest_end' => [],
                'remarks' => 'テスト修正理由',
            ]);

        $response->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    /** @test 休憩開始時間が退勤時間より後の場合エラーメッセージが表示される */
    public function 修正_休憩開始時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->user)
            ->from('/attendance/detail/' . $attendance->id)
            ->followingRedirects()
            ->post('/attendance/detail/' . $attendance->id, [
                'action' => 'correction',
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'rest_start' => ['19:00'],
                'rest_end' => ['20:00'],
                'remarks' => 'テスト修正理由',
            ]);

        $response->assertSee('休憩時間が不適切な値です');
    }

    /** @test 休憩終了時間が退勤時間より後の場合エラーメッセージが表示される */
    public function 修正_休憩終了時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->user)
            ->from('/attendance/detail/' . $attendance->id)
            ->followingRedirects()
            ->post('/attendance/detail/' . $attendance->id, [
                'action' => 'correction',
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'rest_start' => ['12:00'],
                'rest_end' => ['19:00'],
                'remarks' => 'テスト修正理由',
            ]);

        $response->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

    /** @test 備考欄が未入力の場合エラーメッセージが表示される */
    public function 修正_備考欄が未入力の場合エラーメッセージが表示される()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($this->user)
            ->from('/attendance/detail/' . $attendance->id)
            ->followingRedirects()
            ->post('/attendance/detail/' . $attendance->id, [
                'action' => 'correction',
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'rest_start' => [],
                'rest_end' => [],
                'remarks' => '',
            ]);

        $response->assertSee('備考を記入してください');
    }

    /** @test 修正申請処理が正常に実行される */
    public function 修正_修正申請処理が正常に実行される()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        $this->actingAs($this->user)
            ->post('/attendance/detail/' . $attendance->id, [
                'action' => 'correction',
                'clock_in' => '10:00',
                'clock_out' => '19:00',
                'rest_start' => [],
                'rest_end' => [],
                'remarks' => '電車遅延のため',
            ]);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $this->user->id,
            'remarks' => '電車遅延のため',
            'status' => '承認待ち',
        ]);
    }

    /** @test 承認待ちにログインユーザーが行った申請が全て表示される */
    public function 申請一覧_承認待ちにユーザー自身の申請が全て表示される()
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
            'remarks' => '申請理由Aです',
            'status' => '承認待ち',
        ]);
        StampCorrectionRequest::create([
            'attendance_id' => $attendance2->id,
            'user_id' => $this->user->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remarks' => '申請理由Bです',
            'status' => '承認待ち',
        ]);

        $response = $this->actingAs($this->user)->get('/stamp_correction_request/list');

        $response->assertSee('申請理由Aです');
        $response->assertSee('申請理由Bです');
    }

    /** @test 承認済みに承認済みの修正申請が全て表示される */
    public function 申請一覧_承認済みに承認された申請が全て表示される()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $this->user->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remarks' => '承認済みの申請です',
            'status' => '承認済み',
        ]);

        $response = $this->actingAs($this->user)->get('/stamp_correction_request/list');

        $response->assertSee('承認済みの申請です');
    }

    /** @test 各申請の詳細リンクで勤怠詳細画面に遷移する */
    public function 申請一覧_詳細リンクで勤怠詳細画面に遷移する()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-04-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => '退勤済',
        ]);

        StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $this->user->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remarks' => '修正申請理由',
            'status' => '承認待ち',
        ]);

        $listResponse = $this->actingAs($this->user)->get('/stamp_correction_request/list');
        $listResponse->assertSee('/attendance/detail/' . $attendance->id);

        $detailResponse = $this->actingAs($this->user)->get('/attendance/detail/' . $attendance->id);
        $detailResponse->assertStatus(200);
    }
}
