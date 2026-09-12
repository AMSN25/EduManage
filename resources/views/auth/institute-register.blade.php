@extends('layouts.auth')

@section('title', __('auth.register_institute'))

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('auth.institute_registration') }}</h1>
        <p class="text-gray-500 mt-1 text-sm">{{ __('auth.create_account') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        {{-- Institute Details --}}
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">{{ __('auth.institute_registration') }}</h2>

        <div class="mb-4">
            <label for="institute_name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.institute_name') }} *</label>
            <input type="text" name="institute_name" id="institute_name" value="{{ old('institute_name') }}" required
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('institute_name') border-red-500 @enderror">
            @error('institute_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="institute_email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.institute_email') }} *</label>
            <input type="email" name="institute_email" id="institute_email" value="{{ old('institute_email') }}" required
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('institute_email') border-red-500 @enderror">
            @error('institute_email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="institute_phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.institute_phone') }}</label>
                <input type="text" name="institute_phone" id="institute_phone" value="{{ old('institute_phone') }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label for="institute_address" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.institute_address') }}</label>
                <input type="text" name="institute_address" id="institute_address" value="{{ old('institute_address') }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
        </div>

        {{-- Admin Details --}}
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3 mt-6">{{ __('auth.admin_name') }}</h2>

        <div class="mb-4">
            <label for="admin_name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.admin_name') }} *</label>
            <input type="text" name="admin_name" id="admin_name" value="{{ old('admin_name') }}" required
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('admin_name') border-red-500 @enderror">
            @error('admin_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.admin_email') }} *</label>
                <input type="email" name="admin_email" id="admin_email" value="{{ old('admin_email') }}" required
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('admin_email') border-red-500 @enderror">
                @error('admin_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="admin_phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.admin_phone') }}</label>
                <input type="text" name="admin_phone" id="admin_phone" value="{{ old('admin_phone') }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
        </div>

        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.password') }} *</label>
            <input type="password" name="password" id="password" required
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('password') border-red-500 @enderror">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.password_confirmation') }} *</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 text-white px-4 py-2.5 rounded-lg font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition text-sm">
            {{ __('auth.create_account') }}
        </button>

        <p class="mt-4 text-center text-sm text-gray-600">
            {{ __('auth.has_account') }}
            <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-500 font-medium">{{ __('auth.login') }}</a>
        </p>
    </form>
</div>
@endsection
