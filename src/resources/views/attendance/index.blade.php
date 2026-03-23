@extends('layouts.app')

@section('title', '勤怠登録')

@section('content')
    <div class="flex flex-col items-center justify-center" style="min-height: calc(100vh - 64px);">

        {{-- ステータスバッジ --}}
        <div class="mb-4">
            <span class="bg-gray-300 text-gray-700 text-sm px-4 py-1 rounded-full">
                {{ $status }}
            </span>
        </div>

        {{-- 日付 --}}
        <p class="text-xl mb-3">
            {{ $today->isoFormat('YYYY年M月D日(ddd)') }}
        </p>

        {{-- 時刻（JSで動的更新） --}}
        <p id="clock" class="text-7xl font-bold mb-12">
            {{ $today->format('H:i') }}
        </p>

        {{-- ボタンエリア --}}
        @if ($status === '勤務外')
            <form method="POST" action="{{ url('/attendance') }}">
                @csrf
                <input type="hidden" name="action" value="clock_in">
                <button type="submit"
                    class="bg-black text-white font-bold text-lg px-16 py-4 rounded-full hover:bg-gray-800 transition">
                    出勤
                </button>
            </form>

        @elseif ($status === '出勤中')
            <div class="flex gap-6">
                <form method="POST" action="{{ url('/attendance') }}">
                    @csrf
                    <input type="hidden" name="action" value="clock_out">
                    <button type="submit"
                        class="bg-black text-white font-bold text-lg px-16 py-4 rounded-full hover:bg-gray-800 transition">
                        退勤
                    </button>
                </form>
                <form method="POST" action="{{ url('/attendance') }}">
                    @csrf
                    <input type="hidden" name="action" value="rest_start">
                    <button type="submit"
                        class="bg-white text-black font-bold text-lg px-16 py-4 rounded-full border border-black hover:bg-gray-100 transition">
                        休憩入
                    </button>
                </form>
            </div>

        @elseif ($status === '休憩中')
            <form method="POST" action="{{ url('/attendance') }}">
                @csrf
                <input type="hidden" name="action" value="rest_end">
                <button type="submit"
                    class="bg-white text-black font-bold text-lg px-16 py-4 rounded-full border border-black hover:bg-gray-100 transition">
                    休憩戻
                </button>
            </form>

        @elseif ($status === '退勤済')
            <p class="font-bold text-lg">お疲れ様でした。</p>
        @endif

    </div>
@endsection

@push('scripts')
<script>
    // 時刻をリアルタイム更新
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('clock').textContent = hours + ':' + minutes;
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>
@endpush
