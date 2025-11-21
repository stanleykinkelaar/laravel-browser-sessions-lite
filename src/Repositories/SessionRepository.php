<?php

namespace StanleyKinkelaar\LaravelBrowserSessionsLite\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SessionRepository
{
    /**
     * Get all sessions for a specific user.
     */
    public function getSessionsForUser(int $userId): Collection
    {
        $sessions = DB::table(config('session.table', 'sessions'))
            ->where('user_id', $userId)
            ->orderBy('last_activity', 'desc')
            ->get();

        return $sessions->map(function ($session) {
            return $this->transformSession($session);
        });
    }

    /**
     * Get the current session ID.
     */
    public function getCurrentSessionId(): string
    {
        return Session::getId();
    }

    /**
     * Delete all sessions for a user except the current one.
     */
    public function deleteOtherSessionsForUser(int $userId, string $currentSessionId): int
    {
        return DB::table(config('session.table', 'sessions'))
            ->where('user_id', $userId)
            ->where('id', '!=', $currentSessionId)
            ->delete();
    }

    /**
     * Transform a session record into a structured array.
     */
    protected function transformSession(object $session): array
    {
        $currentSessionId = $this->getCurrentSessionId();

        return [
            'id' => $session->id,
            'ip_address' => $session->ip_address ?? 'Unknown',
            'user_agent' => $session->user_agent ?? 'Unknown',
            'last_active_at' => Carbon::createFromTimestamp($session->last_activity),
            'is_current' => $session->id === $currentSessionId,
            'device_hint' => $this->parseDeviceHint($session->user_agent ?? ''),
        ];
    }

    /**
     * Parse a simple device hint from user agent (zero dependencies).
     */
    protected function parseDeviceHint(string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Unknown Device';
        }

        // Mobile detection
        if (preg_match('/(iPhone|iPad|iPod)/i', $userAgent)) {
            return 'iOS Device';
        }

        if (preg_match('/Android/i', $userAgent)) {
            return 'Android Device';
        }

        if (preg_match('/(Mobile|Phone)/i', $userAgent)) {
            return 'Mobile Device';
        }

        // Desktop browsers
        if (preg_match('/Edg/i', $userAgent)) {
            return 'Edge Browser';
        }

        if (preg_match('/Chrome/i', $userAgent)) {
            return 'Chrome Browser';
        }

        if (preg_match('/Firefox/i', $userAgent)) {
            return 'Firefox Browser';
        }

        if (preg_match('/Safari/i', $userAgent)) {
            return 'Safari Browser';
        }

        if (preg_match('/(Windows|Win64|Win32)/i', $userAgent)) {
            return 'Windows PC';
        }

        if (preg_match('/(Macintosh|Mac OS)/i', $userAgent)) {
            return 'Mac Computer';
        }

        if (preg_match('/Linux/i', $userAgent)) {
            return 'Linux PC';
        }

        return 'Desktop Browser';
    }
}
