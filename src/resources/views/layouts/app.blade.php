<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH 勤怠管理')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('head')
</head>
<body class="@yield('body-class', 'bg-[#F0EFF2]') min-h-screen">

    {{-- ヘッダー --}}
    <header class="bg-black w-full py-4 px-8 flex items-center justify-between">
        <a href="/">
            <img src="{{ asset('images/logo.png') }}" alt="COACHTECH" class="h-8">
        </a>

        {{-- ナビゲーション --}}
        @auth
            @if (auth()->user()->role === 'admin')
                {{-- 管理者ナビ --}}
                <nav class="flex items-center gap-8">
                    <a href="{{ url('/admin/attendance/list') }}" class="text-white text-sm hover:opacity-70">勤怠一覧</a>
                    <a href="{{ url('/admin/staff/list') }}" class="text-white text-sm hover:opacity-70">スタッフ一覧</a>
                    <a href="{{ url('/stamp_correction_request/list') }}" class="text-white text-sm hover:opacity-70">申請一覧</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-white text-sm hover:opacity-70">ログアウト</button>
                    </form>
                </nav>
            @else
                {{-- 一般ユーザーナビ（退勤済みで切替） --}}
                @if (isset($currentStatus) && $currentStatus === '退勤済')
                    {{-- 退勤後ナビ --}}
                    <nav class="flex items-center gap-8">
                        <a href="{{ url('/attendance/list') }}" class="text-white text-sm hover:opacity-70">今月の出勤一覧</a>
                        <a href="{{ url('/stamp_correction_request/list') }}" class="text-white text-sm hover:opacity-70">申請一覧</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-white text-sm hover:opacity-70">ログアウト</button>
                        </form>
                    </nav>
                @else
                    {{-- 退勤前ナビ --}}
                    <nav class="flex items-center gap-8">
                        <a href="{{ url('/attendance') }}" class="text-white text-sm hover:opacity-70">勤怠</a>
                        <a href="{{ url('/attendance/list') }}" class="text-white text-sm hover:opacity-70">勤怠一覧</a>
                        <a href="{{ url('/stamp_correction_request/list') }}" class="text-white text-sm hover:opacity-70">申請</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-white text-sm hover:opacity-70">ログアウト</button>
                        </form>
                    </nav>
                @endif
            @endif
        @endauth
    </header>

    {{-- メインコンテンツ --}}
    <main class="@yield('main-class', 'px-8 py-12')">
        @yield('content')
    </main>

    @stack('scripts')

</body>
</html>
