@extends('layouts.auth')

@section('title', __('auth.forgot_password'))

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('auth.forgot_password') }}</h1>
        <p class="text-gray-500 mt-1 text-sm">{{ __('auth.forgot_password_message') }}</p>
    </div>

    @if (session('status'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-6">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.email') }}</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('email') border-red-500 @enderror">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 text-white px-4 py-2.5 rounded-lg font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition text-sm">
            {{ __('auth.send_reset_link') }}
        </button>

        <p class="mt-4 text-center text-sm text-gray-600">
            <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-500 font-medium">{{ __('auth.back_to_login') }}</a>
        </p>
    </form>
</div>
@endsection
