<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'EduManage BD') }} - @yield('title', __('auth.login'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">
    {{-- Language Toggle --}}
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-14">
                <a href="{{ route('login') }}" class="text-xl font-bold text-indigo-600">
                    {{ config('app.name', 'EduManage BD') }}
                </a>
                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}"
                       class="text-sm px-3 py-1.5 rounded {{ app()->getLocale() === 'en' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        {{ __('dashboard.english') }}
                    </a>
                    <a href="{{ route('login') }}?lang=bn"
                       class="text-sm px-3 py-1.5 rounded {{ app()->getLocale() === 'bn' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        {{ __('dashboard.bangla') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="flex items-center justify-center min-h-[calc(100vh-3.5rem)] px-4 py-8">
        <div class="w-full max-w-md">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
