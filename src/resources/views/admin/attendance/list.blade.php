@extends('layouts.app')

@section('title', '勤怠一覧')

@section('content')
    <div class="max-w-4xl mx-auto">

        {{-- ページタイトル --}}
        <h1 class="text-2xl font-bold mb-8 pl-4 border-l-4 border-black">
            {{ $currentDate->format('Y年n月j日') }}の勤怠
        </h1>

        {{-- 日切替 --}}
        <div class="bg-white px-6 py-3 flex items-center justify-between mb-6">
            <a href="{{ url('/admin/attendance/list?date=' . $currentDate->copy()->subDay()->format('Y-m-d')) }}"
                class="text-gray-600 hover:text-black text-sm">← 前日</a>
            <span class="flex items-center gap-2 font-bold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ $currentDate->format('Y/m/d') }}
            </span>
            <a href="{{ url('/admin/attendance/list?date=' . $currentDate->copy()->addDay()->format('Y-m-d')) }}"
                class="text-gray-600 hover:text-black text-sm">翌日 →</a>
        </div>

        {{-- 勤怠テーブル --}}
        <div class="bg-white">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">名前</th>
                        <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">出勤</th>
                        <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">退勤</th>
                        <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">休憩</th>
                        <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">合計</th>
                        <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
                        <tr class="border-b border-gray-200">
                            <td class="py-4 px-4 text-center text-sm">{{ $attendance->user->name }}</td>
                            <td class="py-4 px-4 text-center text-sm">
                                {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
                            </td>
                            <td class="py-4 px-4 text-center text-sm">
                                {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
                            </td>
                            <td class="py-4 px-4 text-center text-sm">
                                @php
                                    $totalRest = 0;
                                    foreach ($attendance->restTimes as $rest) {
                                        if ($rest->rest_start && $rest->rest_end) {
                                            $totalRest += \Carbon\Carbon::parse($rest->rest_start)
                                                ->diffInMinutes(\Carbon\Carbon::parse($rest->rest_end));
                                        }
                                    }
                                    $restHours = intdiv($totalRest, 60);
                                    $restMinutes = $totalRest % 60;
                                @endphp
                                {{ $totalRest > 0 ? sprintf('%d:%02d', $restHours, $restMinutes) : '' }}
                            </td>
                            <td class="py-4 px-4 text-center text-sm">
                                @if ($attendance->clock_in && $attendance->clock_out)
                                    @php
                                        $workMinutes = \Carbon\Carbon::parse($attendance->clock_in)
                                            ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out)) - $totalRest;
                                        $workHours = intdiv($workMinutes, 60);
                                        $workMins  = $workMinutes % 60;
                                    @endphp
                                    {{ sprintf('%d:%02d', $workHours, $workMins) }}
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center text-sm">
                                <a href="{{ url('/admin/attendance/' . $attendance->id) }}"
                                    class="font-bold hover:opacity-70">詳細</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-sm text-gray-400">この日の勤怠データはありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection
