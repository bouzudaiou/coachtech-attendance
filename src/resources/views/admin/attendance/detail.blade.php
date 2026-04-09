@extends('layouts.app')

@section('title', '勤怠詳細')

@section('content')
    <div class="max-w-3xl mx-auto">

        {{-- ページタイトル --}}
        <h1 class="text-2xl font-bold mb-8 pl-4 border-l-4 border-black">勤怠詳細</h1>

        <form method="POST" action="{{ url('/admin/attendance/' . $attendance->id) }}">
            @csrf

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
                    <input type="text" name="clock_in"
                           value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}"
                           class="border border-gray-400 px-3 py-2 w-24 text-center focus:outline-none">
                    <span class="mx-4">〜</span>
                    <input type="text" name="clock_out"
                           value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}"
                           class="border border-gray-400 px-3 py-2 w-24 text-center focus:outline-none">
                </div>
                @error('clock_in')
                <p class="text-red-500 text-sm px-8 pb-2">{{ $message }}</p>
                @enderror

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
                @error('rest_start')
                <p class="text-red-500 text-sm px-8 pb-2">{{ $message }}</p>
                @enderror
                @error('rest_end')
                <p class="text-red-500 text-sm px-8 pb-2">{{ $message }}</p>
                @enderror

                {{-- 追加休憩行（空欄） --}}
                <div class="flex items-center border-b border-gray-200 px-8 py-5">
                    <span class="w-40 text-sm text-gray-500">休憩{{ $restTimes->count() > 0 ? $restTimes->count() + 1 : '' }}</span>
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
                              class="border border-gray-400 px-3 py-2 w-72 focus:outline-none">{{ old('remarks', $attendance->remarks) }}</textarea>
                </div>
                @error('remarks')
                <p class="text-red-500 text-sm px-8 pb-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- 修正ボタン --}}
            <div class="flex justify-end mt-6">
                <button type="submit"
                        class="bg-black text-white font-bold px-10 py-3 hover:bg-gray-800 transition">
                    修正
                </button>
            </div>

        </form>

    </div>
@endsection
