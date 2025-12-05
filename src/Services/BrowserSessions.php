<?php

namespace StanleyKinkelaar\LaravelBrowserSessionsLite\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use StanleyKinkelaar\LaravelBrowserSessionsLite\Repositories\SessionRepository;

class BrowserSessions
{
    public function __construct(
        protected SessionRepository $repository
    ) {}

    /**
     * List all browser sessions for the currently authenticated user.
     */
    public function listForCurrentUser(): Collection
    {
        $user = Auth::user();

        if (! $user) {
            return collect([]);
        }

        return $this->repository->getSessionsForUser($user->id);
    }

    /**
     * Logout all other sessions after verifying the user's password.
     *
     * @throws ValidationException
     */
    public function logoutOtherSessionsWithPassword(string $password): int
    {
        $user = Auth::user();

        if (! $user) {
            throw ValidationException::withMessages([
                'password' => ['Not authenticated.'],
            ]);
        }

        // Verify password
        if (! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        return $this->forceLogoutOthersForUser($user->id, $password);
    }

    /**
     * Force logout all other sessions for a specific user (admin use).
     */
    public function forceLogoutOthersForUser(int $userId, ?string $password = null): int
    {
        $currentSessionId = $this->repository->getCurrentSessionId();

        // Logout other devices via Laravel's Auth guard
        // Password is required by Laravel's logoutOtherDevices to rehash sessions
        if ($password !== null) {
            Auth::logoutOtherDevices($password);
        }

        // Delete other session records from database
        return $this->repository->deleteOtherSessionsForUser($userId, $currentSessionId);
    }

    /**
     * Get the total count of active sessions for current user.
     */
    public function getActiveSessionCount(): int
    {
        return $this->listForCurrentUser()->count();
    }

    /**
     * Check if user has multiple active sessions.
     */
    public function hasMultipleSessions(): bool
    {
        return $this->getActiveSessionCount() > 1;
    }
}
