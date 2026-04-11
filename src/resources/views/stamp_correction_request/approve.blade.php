@extends('layouts.app')

@section('title', '勤怠詳細')

@section('content')
    <div class="max-w-3xl mx-auto">

        {{-- ページタイトル --}}
        <h1 class="text-2xl font-bold mb-8 pl-4 border-l-4 border-black">勤怠詳細</h1>

        <div class="bg-white">
            {{-- 名前 --}}
            <div class="flex items-center border-b border-gray-200 px-8 py-5">
                <span class="w-40 text-sm text-gray-500">名前</span>
                <span class="font-bold">{{ $attendance->user->name }}</span>
            </div>

            {{-- 日付 --}}
            <div class="flex items-center border-b border-gray-200 px-8 py-5">
                <span class="w-40 text-sm text-gray-500">日付</span>
                <span class="font-bold">{{ \Carbon\Carbon::parse($attendance->work_date)->isoFormat('Y年M月D日') }}</span>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="flex items-center border-b border-gray-200 px-8 py-5">
                <span class="w-40 text-sm text-gray-500">出勤・退勤</span>
                <span class="font-bold mr-8">{{ $correctionRequest->clock_in ? \Carbon\Carbon::parse($correctionRequest->clock_in)->format('H:i') : ($attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}</span>
                <span class="mr-8">〜</span>
                <span class="font-bold">{{ $correctionRequest->clock_out ? \Carbon\Carbon::parse($correctionRequest->clock_out)->format('H:i') : ($attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}</span>
            </div>

            {{-- 休憩 --}}
            @foreach ($restTimes as $index => $rest)
                <div class="flex items-center border-b border-gray-200 px-8 py-5">
                    <span class="w-40 text-sm text-gray-500">休憩{{ $index > 0 ? $index + 1 : '' }}</span>
                    <span class="font-bold mr-8">{{ $rest->rest_start ? \Carbon\Carbon::parse($rest->rest_start)->format('H:i') : '' }}</span>
                    <span class="mr-8">〜</span>
                    <span class="font-bold">{{ $rest->rest_end ? \Carbon\Carbon::parse($rest->rest_end)->format('H:i') : '' }}</span>
                </div>
            @endforeach

            {{-- 休憩2（データがない場合も空欄で表示） --}}
            @if ($restTimes->count() < 2)
                <div class="flex items-center border-b border-gray-200 px-8 py-5">
                    <span class="w-40 text-sm text-gray-500">休憩{{ $restTimes->count() + 1 }}</span>
                    <span class="font-bold mr-8"></span>
                    <span class="mr-8">〜</span>
                    <span class="font-bold"></span>
                </div>
            @endif

            {{-- 備考 --}}
            <div class="flex items-center border-b border-gray-200 px-8 py-5">
                <span class="w-40 text-sm text-gray-500">備考</span>
                <span class="font-bold">{{ $correctionRequest->remarks }}</span>
            </div>
        </div>

        {{-- 承認ボタン --}}
        <div class="flex justify-end mt-6">
            @if ($isApproved)
                <button type="button" disabled
                        class="bg-gray-500 text-white font-bold px-10 py-3 cursor-not-allowed">
                    承認済み
                </button>
            @else
                <form method="POST" action="{{ url('/stamp_correction_request/approve/' . $correctionRequest->id) }}">
                    @csrf
                    <button type="submit"
                            class="bg-black text-white font-bold px-10 py-3 hover:bg-gray-800 transition">
                        承認
                    </button>
                </form>
            @endif
        </div>

    </div>
@endsection
