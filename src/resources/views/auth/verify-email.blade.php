@extends('layouts.app')

@section('title', 'メール認証')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-screen">
        <p class="text-center mb-10">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <form method="POST" action="{{ url('/email/verification-notification') }}">
            @csrf
            <button type="submit"
                    class="border border-gray-400 px-16 py-4 text-lg font-bold mb-8 block">
                認証はこちらから
            </button>
        </form>

        <form method="POST" action="{{ url('/email/verification-notification') }}">
            @csrf
            <button type="submit" class="text-blue-500 text-sm">
                認証メールを再送する
            </button>
        </form>
    </div>
@endsection
