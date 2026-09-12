@extends('layouts.auth')

@section('title', __('auth.reset_password'))

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('auth.reset_password') }}</h1>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.email') }}</label>
            <input type="email" name="email" id="email" value="{{ $email ?? old('email') }}" required autofocus
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('email') border-red-500 @enderror">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.password') }}</label>
            <input type="password" name="password" id="password" required
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('password') border-red-500 @enderror">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.password_confirmation') }}</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 text-white px-4 py-2.5 rounded-lg font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition text-sm">
            {{ __('auth.reset_password') }}
        </button>
    </form>
</div>
@endsection
