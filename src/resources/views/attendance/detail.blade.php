@extends('layouts.app')

@section('title', '勤怠詳細')

@section('content')
    <div class="max-w-3xl mx-auto">

        {{-- ページタイトル --}}
        <h1 class="text-2xl font-bold mb-8 pl-4 border-l-4 border-black">勤怠詳細</h1>

        @if ($isPending)
            {{-- 承認待ち：読み取り専用表示 --}}
            <div class="bg-white">
                <div class="flex border-b border-gray-200 px-8 py-5">
                    <span class="w-40 text-sm text-gray-500">名前</span>
                    <span class="font-bold">{{ $attendance->user->name }}</span>
                </div>
                <div class="flex border-b border-gray-200 px-8 py-5">
                    <span class="w-40 text-sm text-gray-500">日付</span>
                    <span class="font-bold mr-16">{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}</span>
                    <span class="font-bold">{{ \Carbon\Carbon::parse($attendance->work_date)->isoFormat('M月D日') }}</span>
                </div>
                <div class="flex border-b border-gray-200 px-8 py-5">
                    <span class="w-40 text-sm text-gray-500">出勤・退勤</span>
                    <span class="font-bold mr-8">{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</span>
                    <span class="mr-8">〜</span>
                    <span class="font-bold">{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</span>
                </div>
                @foreach ($restTimes as $index => $rest)
                    <div class="flex border-b border-gray-200 px-8 py-5">
                        <span class="w-40 text-sm text-gray-500">休憩{{ $index > 0 ? $index + 1 : '' }}</span>
                        <span class="font-bold mr-8">{{ $rest->rest_start ? \Carbon\Carbon::parse($rest->rest_start)->format('H:i') : '' }}</span>
                        <span class="mr-8">〜</span>
                        <span class="font-bold">{{ $rest->rest_end ? \Carbon\Carbon::parse($rest->rest_end)->format('H:i') : '' }}</span>
                    </div>
                @endforeach
                <div class="flex border-b border-gray-200 px-8 py-5">
                    <span class="w-40 text-sm text-gray-500">備考</span>
                    <span class="font-bold">{{ $attendance->stampCorrectionRequests->sortByDesc('created_at')->first()->remarks ?? '' }}</span>
                </div>
            </div>

            {{-- 承認待ちメッセージ --}}
            <div class="flex justify-end mt-6">
                <p class="text-red-500 text-sm">* 承認待ちのため修正はできません。</p>
            </div>

        @else
            {{-- 通常：編集可能フォーム --}}
            <form method="POST" action="{{ url('/attendance/detail/' . $attendance->id) }}">
                @csrf

                {{-- エラーメッセージ --}}
                @if ($errors->any())
                    <div class="mb-6">
                        @foreach ($errors->all() as $error)
                            <p class="text-red-500 text-sm">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="bg-white">
                    {{-- 名前 --}}
                    <div class="flex items-center border-b border-gray-200 px-8 py-5">
                        <span class="w-40 text-sm text-gray-500">名前</span>
                        <span class="font-bold">{{ $attendance->user->name }}</span>
                    </div>

                    {{-- 日付 --}}
                    <div class="flex items-center border-b border-gray-200 px-8 py-5">
                        <span class="w-40 text-sm text-gray-500">日付</span>
                        <span class="font-bold mr-16">{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}</span>
                        <span class="font-bold">{{ \Carbon\Carbon::parse($attendance->work_date)->isoFormat('M月D日') }}</span>
                    </div>

                    {{-- 出勤・退勤 --}}
                    <div class="flex items-center border-b border-gray-200 px-8 py-5">
                        <span class="w-40 text-sm text-gray-500">出勤・退勤</span>
                        <input type="text" name="clock_in"
                            value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}"
                            class="border border-gray-400 px-3 py-2 w-24 text-center focus:outline-none">
                        <span class="mx-4">〜</span>
                        <input type="text" name="clock_out"
                            value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}"
                            class="border border-gray-400 px-3 py-2 w-24 text-center focus:outline-none">
                    </div>

                    {{-- 休憩（既存分） --}}
                    @foreach ($restTimes as $index => $rest)
                        <div class="flex items-center border-b border-gray-200 px-8 py-5">
                            <span class="w-40 text-sm text-gray-500">休憩{{ $index > 0 ? $index + 1 : '' }}</span>
                            <input type="text" name="rest_start[]"
                                value="{{ old('rest_start.' . $index, $rest->rest_start ? \Carbon\Carbon::parse($rest->rest_start)->format('H:i') : '') }}"
                                class="border border-gray-400 px-3 py-2 w-24 text-center focus:outline-none">
                            <span class="mx-4">〜</span>
                            <input type="text" name="rest_end[]"
                                value="{{ old('rest_end.' . $index, $rest->rest_end ? \Carbon\Carbon::parse($rest->rest_end)->format('H:i') : '') }}"
                                class="border border-gray-400 px-3 py-2 w-24 text-center focus:outline-none">
                            <input type="hidden" name="rest_id[]" value="{{ $rest->id }}">
                        </div>
                    @endforeach

                    {{-- 追加休憩行（空欄） --}}
                    <div class="flex items-center border-b border-gray-200 px-8 py-5">
                        <span class="w-40 text-sm text-gray-500">休憩{{ $restTimes->count() > 0 ? $restTimes->count() + 1 : 2 }}</span>
                        <input type="text" name="rest_start[]" value="{{ old('rest_start.' . $restTimes->count()) }}"
                            class="border border-gray-400 px-3 py-2 w-24 text-center focus:outline-none">
                        <span class="mx-4">〜</span>
                        <input type="text" name="rest_end[]" value="{{ old('rest_end.' . $restTimes->count()) }}"
                            class="border border-gray-400 px-3 py-2 w-24 text-center focus:outline-none">
                        <input type="hidden" name="rest_id[]" value="">
                    </div>

                    {{-- 備考 --}}
                    <div class="flex items-start border-b border-gray-200 px-8 py-5">
                        <span class="w-40 text-sm text-gray-500 mt-2">備考</span>
                        <textarea name="remarks" rows="3"
                            class="border border-gray-400 px-3 py-2 w-72 focus:outline-none">{{ old('remarks', $attendance->stampCorrectionRequests->sortByDesc('created_at')->first()->remarks ?? '') }}</textarea>
                    </div>
                </div>

                {{-- 修正ボタン --}}
                <div class="flex justify-end mt-6">
                    <button type="submit"
                        class="bg-black text-white font-bold px-10 py-3 hover:bg-gray-800 transition">
                        修正
                    </button>
                </div>

            </form>
        @endif

    </div>
@endsection
