@extends('layouts.app')

@section('title', '会員登録')
@section('body-class', 'bg-white')

@section('content')
    <div class="flex flex-col items-center pt-16 px-4">

        <h1 class="text-2xl font-bold mb-10">会員登録</h1>

        <form method="POST" action="{{ route('register') }}" class="w-full max-w-xl">
            @csrf

            {{-- 名前 --}}
            <div class="mb-6">
                <label for="name" class="block font-bold mb-2">名前</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    class="w-full border border-gray-400 px-4 py-3 focus:outline-none focus:border-gray-600"
                >
                @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- メールアドレス --}}
            <div class="mb-6">
                <label for="email" class="block font-bold mb-2">メールアドレス</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="w-full border border-gray-400 px-4 py-3 focus:outline-none focus:border-gray-600"
                >
                @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- パスワード --}}
            <div class="mb-6">
                <label for="password" class="block font-bold mb-2">パスワード</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="w-full border border-gray-400 px-4 py-3 focus:outline-none focus:border-gray-600"
                >
                @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- パスワード確認 --}}
            <div class="mb-10">
                <label for="password_confirmation" class="block font-bold mb-2">パスワード確認</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="w-full border border-gray-400 px-4 py-3 focus:outline-none focus:border-gray-600"
                >
            </div>

            {{-- 登録ボタン --}}
            <button
                type="submit"
                class="w-full bg-black text-white font-bold py-4 hover:bg-gray-800 transition"
            >
                登録する
            </button>

        </form>

        {{-- ログインリンク --}}
        <a href="{{ route('login') }}" class="mt-6 text-blue-500 hover:underline">
            ログインはこちら
        </a>

    </div>
@endsection
