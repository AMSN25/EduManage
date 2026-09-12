<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <meta name="description" content="{{ config('app.name', 'EduManage BD') }} - School, College & Madrasa Management">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="EduManage">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.svg">
    <title>{{ config('app.name', 'EduManage BD') }} - @yield('title', __('dashboard.dashboard'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/offline-queue.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="hidden lg:flex lg:flex-col w-64 bg-indigo-700 text-white min-h-screen fixed inset-y-0 left-0 z-30">
            {{-- Logo --}}
            <div class="flex items-center justify-center h-16 border-b border-indigo-600/50">
                <a href="{{ route('dashboard') }}" class="text-xl font-bold">
                    {{ config('app.name', 'EduManage BD') }}
                </a>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    {{ __('dashboard.dashboard') }}
                </a>

                @if(auth()->user()->hasRole('super-admin'))
                    <a href="#"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-indigo-200 hover:bg-indigo-600 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        {{ __('dashboard.manage_institutes') }}
                    </a>
                    <a href="#"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-indigo-200 hover:bg-indigo-600 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        {{ __('dashboard.manage_users') }}
                    </a>
                    <a href="#"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-indigo-200 hover:bg-indigo-600 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        {{ __('dashboard.reports') }}
                    </a>
                @endif

                @if(auth()->user()->hasRole('institute-admin'))
                    <a href="{{ route('users.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('users.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        {{ __('user_management.user_management') }}
                    </a>

                    {{-- Academic Structure --}}
                    <div class="pt-4">
                        <p class="px-3 text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-2">{{ __('academic_structure.academic_structure') }}</p>
                    </div>

                    <a href="{{ route('setup-wizard') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('setup-wizard') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        {{ __('academic_structure.setup_wizard') }}
                    </a>

                    <a href="{{ route('academic-years.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('academic-years.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ __('academic_structure.academic_years') }}
                    </a>

                    <a href="{{ route('classes.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('classes.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        {{ __('academic_structure.classes') }}
                    </a>

                    <a href="{{ route('sections.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('sections.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        {{ __('academic_structure.sections') }}
                    </a>

                    <a href="{{ route('groups.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('groups.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ __('academic_structure.groups') }}
                    </a>

                    <a href="{{ route('subjects.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('subjects.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        {{ __('academic_structure.subjects') }}
                    </a>

                    {{-- Student Management --}}
                    <div class="pt-4">
                        <p class="px-3 text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-2">{{ __('student.students') }}</p>
                    </div>

                    <a href="{{ route('students.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('students.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        {{ __('student.students') }}
                    </a>

                    {{-- Attendance --}}
                    <div class="pt-4">
                        <p class="px-3 text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-2">{{ __('attendance.mark_attendance') }}</p>
                    </div>

                    <a href="{{ route('attendance.mark') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('attendance.mark') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        {{ __('attendance.mark_attendance') }}
                    </a>

                    <a href="{{ route('attendance.report') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('attendance.report') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        {{ __('attendance.attendance_report') }}
                    </a>

                    {{-- Exams --}}
                    <div class="pt-4">
                        <p class="px-3 text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-2">{{ __('exams.exams') }}</p>
                    </div>

                    <a href="{{ route('exams.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('exams.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        {{ __('exams.exams') }}
                    </a>

                    <a href="{{ route('marks.entry') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('marks.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        {{ __('exams.mark_entry') }}
                    </a>

                    <a href="{{ route('exams.admit-cards') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('exams.admit-cards') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/></svg>
                        {{ __('exams.admit_cards') }}
                    </a>

                    <a href="{{ route('exams.seat-plan') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('exams.seat-plan') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                        {{ __('exams.seat_plan') }}
                    </a>

                    <a href="{{ route('exams.tabulation') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('exams.tabulation') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        {{ __('exams.tabulation_sheet') }}
                    </a>

                    {{-- Fees --}}
                    <div class="pt-4">
                        <p class="px-3 text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-2">{{ __('fees.fee_collection') }}</p>
                    </div>

                    <a href="{{ route('fees.collect') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('fees.collect') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                        {{ __('fees.collect_fees') }}
                    </a>

                    <a href="{{ route('fees.dues-report') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('fees.dues-report') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        {{ __('fees.dues_report') }}
                    </a>

                    <a href="{{ route('fees.types') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('fees.types') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6h.008v.008H6V6z"/></svg>
                        {{ __('fees.fee_types') }}
                    </a>

                    <a href="{{ route('fees.structures') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('fees.structures') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                        {{ __('fees.fee_structures') }}
                    </a>

                    @if(auth()->user()->can('generateMonthly', \App\Models\FeeStructure::class))
                        <form method="POST" action="{{ route('fees.generate-monthly') }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-indigo-200 hover:bg-indigo-600 hover:text-white w-full">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ __('fees.generate_monthly') }}
                            </button>
                        </form>
                    @endif
                @endif

                {{-- SMS & Notices --}}
                @if(auth()->user()->hasRole('institute-admin'))
                    <div class="pt-4">
                        <p class="px-3 text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-2">{{ __('sms.sms') }}</p>
                    </div>

                    <a href="{{ route('sms.dashboard') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('sms.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-12.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 4.5h16.5a1.5 1.5 0 011.5 1.5v12a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5V6a1.5 1.5 0 011.5-1.5z"/></svg>
                        {{ __('sms.sms_dashboard') }}
                    </a>

                    <a href="{{ route('notices.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('notices.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586-.205-3.124-.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46"/></svg>
                        {{ __('sms.notices') }}
                    </a>
                @endif

                @if(auth()->user()->hasRole('teacher'))
                    <div class="pt-4">
                        <p class="px-3 text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-2">{{ __('attendance.mark_attendance') }}</p>
                    </div>

                    <a href="{{ route('attendance.mark') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('attendance.mark') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        {{ __('attendance.mark_attendance') }}
                    </a>

                    <a href="{{ route('attendance.report') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('attendance.report') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        {{ __('attendance.attendance_report') }}
                    </a>

                    <div class="pt-4">
                        <p class="px-3 text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-2">{{ __('exams.mark_entry') }}</p>
                    </div>

                    <a href="{{ route('marks.entry') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('marks.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        {{ __('exams.mark_entry') }}
                    </a>
                @endif

                @if(auth()->user()->hasRole('super-admin'))
                    <div class="pt-4">
                        <p class="px-3 text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-2">Super Admin</p>
                    </div>

                    <a href="{{ route('super-admin.dashboard') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('super-admin.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Dashboard
                    </a>

                    <a href="{{ route('super-admin.institutes') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('super-admin.institutes*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Institutes
                    </a>
                @endif

                @if(auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('institute-admin'))
                    <a href="{{ route('institute.settings') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('institute.settings') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ __('dashboard.settings') }}
                    </a>
                @endif

                <a href="{{ route('profile') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('profile') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    {{ __('user_management.my_profile') }}
                </a>

                <a href="{{ route('install') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('install') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    {{ __('pwa.install_title') }}
                </a>
            </nav>

            {{-- User Info --}}
            <div class="border-t border-indigo-600/50 px-4 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-sm font-medium">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-indigo-300 truncate">
                            @if(auth()->user()->hasRole('super-admin'))
                                {{ __('dashboard.super_admin') }}
                            @elseif(auth()->user()->hasRole('institute-admin'))
                                {{ __('dashboard.institute_admin') }}
                            @else
                                {{ ucfirst(auth()->getFirstRole()->name ?? '') }}
                            @endif
                        </p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-indigo-200 hover:bg-indigo-600 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        {{ __('auth.logout') }}
                    </button>
                </form>
            </div>
        </aside>

        {{-- Mobile Header --}}
        <div class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-indigo-700 text-white h-14 flex items-center justify-between px-4">
            <a href="{{ route('dashboard') }}" class="text-lg font-bold">{{ config('app.name', 'EduManage BD') }}</a>
            <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobile-menu" class="hidden lg:hidden fixed inset-0 z-[55]">
            <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('mobile-menu').classList.add('hidden')"></div>
            <div class="fixed inset-y-0 left-0 w-64 bg-indigo-700 text-white overflow-y-auto">
                <div class="flex items-center justify-between h-16 px-4 border-b border-indigo-600/50">
                    <span class="text-lg font-bold">{{ config('app.name', 'EduManage BD') }}</span>
                    <button onclick="document.getElementById('mobile-menu').classList.add('hidden')" class="p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <nav class="px-4 py-6 space-y-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                        {{ __('dashboard.dashboard') }}
                    </a>
                    @if(auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('institute-admin'))
                        <a href="{{ route('institute.settings') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('institute.settings') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600 hover:text-white' }}">
                            {{ __('dashboard.settings') }}
                        </a>
                    @endif
                </nav>
                <div class="px-4 py-4 border-t border-indigo-600/50">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2.5 rounded-lg text-sm font-medium text-indigo-200 hover:bg-indigo-600 hover:text-white">
                            {{ __('auth.logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <main class="flex-1 lg:ml-64 pt-14 lg:pt-0">
            <div class="px-4 sm:px-6 lg:px-8 py-8">
                @if (session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
                {!! $slot ?? '' !!}
            </div>
        </main>
    </div>

    @livewireScripts

    {{-- Offline Sync Indicator --}}
    <div id="offline-sync-indicator" class="hidden fixed bottom-20 lg:bottom-6 right-4 z-[70] bg-amber-500 text-white px-4 py-2.5 rounded-lg shadow-lg text-sm font-medium items-center gap-2">
        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        <span><span id="offline-sync-count">0</span> {{ __('sms.queued') }} — {{ __('pwa.will_sync') }}</span>
    </div>

    {{-- Service Worker Registration --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then((reg) => {
                    console.log('SW registered:', reg.scope);
                }).catch((err) => {
                    console.log('SW registration failed:', err);
                });
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
