@extends('layouts.app')

@section('title', '申請一覧')

@section('content')
    <div class="max-w-4xl mx-auto">

        {{-- ページタイトル --}}
        <h1 class="text-2xl font-bold mb-8 pl-4 border-l-4 border-black">申請一覧</h1>

        {{-- タブ --}}
        <div class="flex border-b border-gray-300 mb-6">
            <button onclick="switchTab('pending')" id="tab-pending"
                class="tab-btn px-8 py-3 text-sm font-bold border-b-2 border-black">
                承認待ち
            </button>
            <button onclick="switchTab('approved')" id="tab-approved"
                class="tab-btn px-8 py-3 text-sm text-gray-400 border-b-2 border-transparent">
                承認済み
            </button>
        </div>

        {{-- 承認待ちテーブル --}}
        <div id="panel-pending">
            <div class="bg-white">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">状態</th>
                            <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">名前</th>
                            <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">対象日時</th>
                            <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">申請理由</th>
                            <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">申請日時</th>
                            <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingRequests as $request)
                            <tr class="border-b border-gray-200">
                                <td class="py-4 px-4 text-center text-sm">{{ $request->status }}</td>
                                <td class="py-4 px-4 text-center text-sm">{{ $request->user->name }}</td>
                                <td class="py-4 px-4 text-center text-sm">
                                    {{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}
                                </td>
                                <td class="py-4 px-4 text-center text-sm">{{ $request->remarks }}</td>
                                <td class="py-4 px-4 text-center text-sm">
                                    {{ $request->created_at->format('Y/m/d') }}
                                </td>
                                <td class="py-4 px-4 text-center text-sm">
                                    @if (auth()->user()->role === 'admin')
                                        <a href="{{ url('/stamp_correction_request/approve/' . $request->id) }}"
                                            class="font-bold hover:opacity-70">詳細</a>
                                    @else
                                        <a href="{{ url('/attendance/detail/' . $request->attendance_id) }}"
                                            class="font-bold hover:opacity-70">詳細</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-gray-400">承認待ちの申請はありません</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 承認済みテーブル --}}
        <div id="panel-approved" class="hidden">
            <div class="bg-white">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">状態</th>
                            <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">名前</th>
                            <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">対象日時</th>
                            <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">申請理由</th>
                            <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">申請日時</th>
                            <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($approvedRequests as $request)
                            <tr class="border-b border-gray-200">
                                <td class="py-4 px-4 text-center text-sm">{{ $request->status }}</td>
                                <td class="py-4 px-4 text-center text-sm">{{ $request->user->name }}</td>
                                <td class="py-4 px-4 text-center text-sm">
                                    {{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}
                                </td>
                                <td class="py-4 px-4 text-center text-sm">{{ $request->remarks }}</td>
                                <td class="py-4 px-4 text-center text-sm">
                                    {{ $request->created_at->format('Y/m/d') }}
                                </td>
                                <td class="py-4 px-4 text-center text-sm">
                                    @if (auth()->user()->role === 'admin')
                                        <a href="{{ url('/stamp_correction_request/approve/' . $request->id) }}"
                                            class="font-bold hover:opacity-70">詳細</a>
                                    @else
                                        <a href="{{ url('/attendance/detail/' . $request->attendance_id) }}"
                                            class="font-bold hover:opacity-70">詳細</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-gray-400">承認済みの申請はありません</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    function switchTab(tab) {
        // パネル切替
        document.getElementById('panel-pending').classList.toggle('hidden', tab !== 'pending');
        document.getElementById('panel-approved').classList.toggle('hidden', tab !== 'approved');
        // タブスタイル切替
        document.getElementById('tab-pending').className =
            tab === 'pending'
                ? 'tab-btn px-8 py-3 text-sm font-bold border-b-2 border-black'
                : 'tab-btn px-8 py-3 text-sm text-gray-400 border-b-2 border-transparent';
        document.getElementById('tab-approved').className =
            tab === 'approved'
                ? 'tab-btn px-8 py-3 text-sm font-bold border-b-2 border-black'
                : 'tab-btn px-8 py-3 text-sm text-gray-400 border-b-2 border-transparent';
    }
</script>
@endpush
