@extends('layouts.app')
@section('title', 'ログイン')
@section('body-class', 'bg-white')
@section('content')
    <div class="flex flex-col items-center pt-16 px-4">
        <h1 class="text-2xl font-bold mb-10">ログイン</h1>
        <form method="POST" action="{{ route('login') }}" class="w-full max-w-xl">
            @csrf
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
            <div class="mb-10">
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
            {{-- ログインボタン --}}
            <button
                type="submit"
                class="w-full bg-black text-white font-bold py-4 hover:bg-gray-800 transition"
            >
                ログインする
            </button>
        </form>
        {{-- 会員登録リンク --}}
        <a href="{{ route('register') }}" class="mt-6 text-blue-500 hover:underline">
            会員登録はこちら
        </a>
    </div>
@endsection
