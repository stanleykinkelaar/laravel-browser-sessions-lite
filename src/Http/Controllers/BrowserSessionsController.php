<?php

namespace StanleyKinkelaar\LaravelBrowserSessionsLite\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use StanleyKinkelaar\LaravelBrowserSessionsLite\Services\BrowserSessions;

class BrowserSessionsController
{
    public function __construct(
        protected BrowserSessions $browserSessions
    ) {}

    /**
     * Display all browser sessions for the authenticated user.
     */
    public function index(Request $request): View|JsonResponse
    {
        $sessions = $this->browserSessions->listForCurrentUser();

        if ($request->expectsJson()) {
            return response()->json([
                'sessions' => $sessions->toArray(),
                'count' => $sessions->count(),
            ]);
        }

        /** @var view-string $viewName */
        $viewName = 'browser-sessions-lite::browser-sessions';

        return view($viewName, [
            'sessions' => $sessions,
        ]);
    }

    /**
     * Logout all other browser sessions.
     */
    public function destroy(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        try {
            $deletedCount = $this->browserSessions->logoutOtherSessionsWithPassword(
                $request->input('password')
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Successfully logged out other browser sessions.',
                    'deleted_count' => $deletedCount,
                ]);
            }

            return back()->with('status', 'Successfully logged out other browser sessions.');
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The provided password is incorrect.',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        }
    }
}
