<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AI Email Assistant') }} - @yield('title')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
<div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <nav class="w-64 bg-white shadow-lg flex-shrink-0">
        <div class="h-full flex flex-col">
            <!-- Logo -->
            <div class="px-6 py-4 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-primary">AI Email Assistant</h1>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 px-4 py-6 overflow-y-auto scrollbar-thin">
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('email.draft') }}"
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-primary hover:text-white transition-colors {{ request()->routeIs('email.draft') ? 'bg-primary text-white' : 'text-gray-700' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Draft Email
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('email.response') }}"
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-primary hover:text-white transition-colors {{ request()->routeIs('email.response') ? 'bg-primary text-white' : 'text-gray-700' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                            </svg>
                            Generate Response
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('email.analyze') }}"
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-primary hover:text-white transition-colors {{ request()->routeIs('email.analyze') ? 'bg-primary text-white' : 'text-gray-700' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Analyze Email
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('template.index') }}"
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-primary hover:text-white transition-colors {{ request()->routeIs('template.*') ? 'bg-primary text-white' : 'text-gray-700' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            Templates
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('email.summarize') }}"
                           class="flex items-center px-4 py-3 rounded-lg hover:bg-primary hover:text-white transition-colors {{ request()->routeIs('email.summarize') ? 'bg-primary text-white' : 'text-gray-700' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Summarize Thread
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Settings -->
            <div class="px-4 py-4 border-t border-gray-200">
                <a href="{{ route('settings') }}"
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-100 transition-colors text-gray-700">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Settings
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <div class="p-8">
            @yield('content')
        </div>
    </main>
</div>

@stack('scripts')
</body>
</html>
