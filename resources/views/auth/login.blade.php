@extends('layouts.auth')

@section('title', __('auth.login'))

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('auth.welcome_back') }}</h1>
        <p class="text-gray-500 mt-1 text-sm">{{ __('auth.sign_in_message') }}</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.email') }}</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('email') border-red-500 @enderror">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.password') }}</label>
            <input type="password" name="password" id="password" required autocomplete="current-password"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('password') border-red-500 @enderror">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between mb-6">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm text-gray-600">{{ __('auth.remember_me') }}</span>
            </label>
            <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                {{ __('auth.forgot_password') }}
            </a>
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 text-white px-4 py-2.5 rounded-lg font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition text-sm">
            {{ __('auth.login') }}
        </button>

        <p class="mt-4 text-center text-sm text-gray-600">
            {{ __('auth.no_account') }}
            <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-500 font-medium">{{ __('auth.register_institute') }}</a>
        </p>
    </form>
</div>
@endsection
