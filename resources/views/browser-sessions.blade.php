<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Browser Sessions</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">Browser Sessions</h2>
                        <span class="text-sm text-gray-500">{{ $sessions->count() }} active {{ Str::plural('session', $sessions->count()) }}</span>
                    </div>

                    <p class="text-sm text-gray-600 mb-6">
                        Manage and log out your active sessions on other browsers and devices.
                    </p>

                    @if (session('status'))
                        <div class="mb-6 px-4 py-3 rounded-lg bg-green-100 border border-green-200 text-green-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 px-4 py-3 rounded-lg bg-red-100 border border-red-200 text-red-800">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Sessions List -->
                    <div class="space-y-4 mb-8">
                        @forelse ($sessions as $session)
                            <div class="flex items-center justify-between p-4 border rounded-lg {{ $session['is_current'] ? 'bg-blue-50 border-blue-200' : 'bg-gray-50 border-gray-200' }}">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <div>
                                            <h3 class="font-semibold text-gray-800">
                                                {{ $session['device_hint'] }}
                                                @if ($session['is_current'])
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-200 text-blue-800">
                                                        Current Device
                                                    </span>
                                                @endif
                                            </h3>
                                            <p class="text-sm text-gray-600">{{ $session['ip_address'] }}</p>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 ml-8">
                                        Last active {{ $session['last_active_at']->diffForHumans() }}
                                    </p>
                                    <p class="text-xs text-gray-400 ml-8 mt-1 truncate max-w-2xl">
                                        {{ $session['user_agent'] }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 text-gray-500">
                                No active sessions found.
                            </div>
                        @endforelse
                    </div>

                    @if ($sessions->count() > 1)
                        <!-- Logout Other Sessions Form -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Log Out Other Browser Sessions</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices.
                            </p>

                            <form method="POST" action="{{ route('browser-sessions.destroy') }}" class="max-w-md">
                                @csrf
                                @method('DELETE')

                                <div class="mb-4">
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                        Password
                                    </label>
                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Enter your password"
                                    >
                                </div>

                                <button
                                    type="submit"
                                    class="px-6 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition"
                                >
                                    Log Out Other Browser Sessions
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
