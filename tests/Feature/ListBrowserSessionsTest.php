<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use StanleyKinkelaar\LaravelBrowserSessionsLite\Services\BrowserSessions;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Setup session table
    DB::statement('CREATE TABLE IF NOT EXISTS sessions (
        id varchar(255) NOT NULL PRIMARY KEY,
        user_id bigint unsigned NULL,
        ip_address varchar(45) NULL,
        user_agent text NULL,
        payload longtext NOT NULL,
        last_activity int NOT NULL
    )');

    $this->user = createTestUser();
    $this->service = app(BrowserSessions::class);
});

it('lists all sessions for authenticated user', function () {
    insertSessionsForUser($this->user->id);

    $this->actingAs($this->user);

    $sessions = $this->service->listForCurrentUser();

    expect($sessions)->toHaveCount(3)
        ->and($sessions->first()['ip_address'])->toBe('127.0.0.1');
});

it('identifies current session correctly', function () {
    insertSessionsForUser($this->user->id);

    $this->actingAs($this->user);

    $sessions = $this->service->listForCurrentUser();

    $currentSessions = $sessions->where('is_current', true);

    expect($currentSessions)->toHaveCount(1);
});

it('orders sessions by last activity descending', function () {
    DB::table('sessions')->insert([
        [
            'id' => 'old-session',
            'user_id' => $this->user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Old Browser',
            'payload' => '',
            'last_activity' => now()->subDays(5)->timestamp,
        ],
        [
            'id' => 'new-session',
            'user_id' => $this->user->id,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'New Browser',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ],
    ]);

    $this->actingAs($this->user);

    $sessions = $this->service->listForCurrentUser();

    expect($sessions->first()['user_agent'])->toBe('New Browser')
        ->and($sessions->last()['user_agent'])->toBe('Old Browser');
});

it('detects device hints correctly', function () {
    DB::table('sessions')->insert([
        [
            'id' => 'ios-device',
            'user_id' => $this->user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ],
        [
            'id' => 'android-device',
            'user_id' => $this->user->id,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Linux; Android 10) AppleWebKit/537.36',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ],
        [
            'id' => 'chrome-browser',
            'user_id' => $this->user->id,
            'ip_address' => '10.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0) Chrome/91.0',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ],
    ]);

    $this->actingAs($this->user);

    $sessions = $this->service->listForCurrentUser();

    $deviceHints = $sessions->pluck('device_hint')->toArray();

    expect($deviceHints)->toContain('iOS Device')
        ->and($deviceHints)->toContain('Android Device')
        ->and($deviceHints)->toContain('Chrome Browser');
});

it('only shows sessions for authenticated user', function () {
    $otherUserId = 999;

    // Insert sessions for current user
    insertSessionsForUser($this->user->id);

    // Insert sessions for another user
    DB::table('sessions')->insert([
        'id' => 'other-user-session',
        'user_id' => $otherUserId,
        'ip_address' => '10.10.10.10',
        'user_agent' => 'Other User Browser',
        'payload' => '',
        'last_activity' => now()->timestamp,
    ]);

    $this->actingAs($this->user);

    $sessions = $this->service->listForCurrentUser();

    expect($sessions)->toHaveCount(3)
        ->and($sessions->pluck('ip_address')->toArray())->not->toContain('10.10.10.10');
});

it('returns formatted last active time', function () {
    DB::table('sessions')->insert([
        'id' => 'recent-session',
        'user_id' => $this->user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Browser',
        'payload' => '',
        'last_activity' => now()->subMinutes(5)->timestamp,
    ]);

    $this->actingAs($this->user);

    $sessions = $this->service->listForCurrentUser();

    expect($sessions->first()['last_active_at'])->toBeInstanceOf(\Carbon\Carbon::class);
});

// Helper functions
function createTestUser()
{
    return new class
    {
        public int $id = 1;

        public string $email = 'test@example.com';

        public string $password;

        public function __construct()
        {
            $this->password = Hash::make('password');
        }

        public function getAuthIdentifier()
        {
            return $this->id;
        }
    };
}

function insertSessionsForUser(int $userId): void
{
    DB::table('sessions')->insert([
        [
            'id' => 'session-1',
            'user_id' => $userId,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Macintosh)',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ],
        [
            'id' => 'session-2',
            'user_id' => $userId,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Windows)',
            'payload' => '',
            'last_activity' => now()->subHour()->timestamp,
        ],
        [
            'id' => 'session-3',
            'user_id' => $userId,
            'ip_address' => '10.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Linux)',
            'payload' => '',
            'last_activity' => now()->subHours(2)->timestamp,
        ],
    ]);
}
