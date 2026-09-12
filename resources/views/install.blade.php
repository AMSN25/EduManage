<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <link rel="manifest" href="/manifest.json">
    <title>{{ __('pwa.install_title') }} - {{ config('app.name', 'EduManage BD') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        .step-number { width: 40px; height: 40px; min-width: 40px; }
        .step-number-lg { width: 56px; height: 56px; min-width: 56px; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-lg mx-auto px-4 py-8">
        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <span class="text-white text-3xl font-bold">EM</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('pwa.install_title') }}</h1>
            <p class="text-gray-500 mt-2">{{ __('pwa.install_description') }}</p>
        </div>

        {{-- Benefits --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('pwa.benefits_title') }}</h2>
            <ul class="space-y-3">
                <li class="flex items-start gap-3">
                    <span class="text-green-500 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <span class="text-sm text-gray-700">{{ __('pwa.benefit_offline') }}</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-500 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <span class="text-sm text-gray-700">{{ __('pwa.benefit_faster') }}</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-500 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <span class="text-sm text-gray-700">{{ __('pwa.benefit_home_screen') }}</span>
                </li>
            </ul>
        </div>

        {{-- Chrome / Android --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#4f46e5" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="#4f46e5"/></svg>
                {{ __('pwa.chrome_android') }}
            </h2>
            <ol class="space-y-4">
                <li class="flex items-start gap-3">
                    <span class="step-number bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center text-sm font-bold">1</span>
                    <p class="text-sm text-gray-700 pt-2">{{ __('pwa.chrome_step1') }}</p>
                </li>
                <li class="flex items-start gap-3">
                    <span class="step-number bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center text-sm font-bold">2</span>
                    <p class="text-sm text-gray-700 pt-2">{{ __('pwa.chrome_step2') }}</p>
                </li>
                <li class="flex items-start gap-3">
                    <span class="step-number bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center text-sm font-bold">3</span>
                    <p class="text-sm text-gray-700 pt-2">{{ __('pwa.chrome_step3') }}</p>
                </li>
            </ol>
        </div>

        {{-- iOS / Safari --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"><rect x="5" y="2" width="14" height="20" rx="3" stroke="#4f46e5" stroke-width="2"/><line x1="12" y1="18" x2="12" y2="18.01" stroke="#4f46e5" stroke-width="2" stroke-linecap="round"/></svg>
                {{ __('pwa.ios_safari') }}
            </h2>
            <ol class="space-y-4">
                <li class="flex items-start gap-3">
                    <span class="step-number bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center text-sm font-bold">1</span>
                    <p class="text-sm text-gray-700 pt-2">{{ __('pwa.ios_step1') }}</p>
                </li>
                <li class="flex items-start gap-3">
                    <span class="step-number bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center text-sm font-bold">2</span>
                    <p class="text-sm text-gray-700 pt-2">{{ __('pwa.ios_step2') }}</p>
                </li>
                <li class="flex items-start gap-3">
                    <span class="step-number bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center text-sm font-bold">3</span>
                    <p class="text-sm text-gray-700 pt-2">{{ __('pwa.ios_step3') }}</p>
                </li>
            </ol>
        </div>

        {{-- Back to Dashboard --}}
        <div class="text-center">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-500 font-medium text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('pwa.back_to_dashboard') }}
            </a>
        </div>
    </div>
</body>
</html>
