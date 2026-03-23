@extends('layouts.app')

@section('title', 'スタッフ一覧')

@section('content')
    <div class="max-w-3xl mx-auto">

        {{-- ページタイトル --}}
        <h1 class="text-2xl font-bold mb-8 pl-4 border-l-4 border-black">スタッフ一覧</h1>

        {{-- スタッフテーブル --}}
        <div class="bg-white">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">名前</th>
                        <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">メールアドレス</th>
                        <th class="py-4 px-4 text-center text-sm font-normal text-gray-500">月次勤怠</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="border-b border-gray-200">
                            <td class="py-4 px-4 text-center text-sm">{{ $user->name }}</td>
                            <td class="py-4 px-4 text-center text-sm text-gray-500">{{ $user->email }}</td>
                            <td class="py-4 px-4 text-center text-sm">
                                <a href="{{ url('/admin/attendance/staff/' . $user->id) }}"
                                    class="font-bold hover:opacity-70">詳細</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-8 text-center text-sm text-gray-400">スタッフが登録されていません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection
