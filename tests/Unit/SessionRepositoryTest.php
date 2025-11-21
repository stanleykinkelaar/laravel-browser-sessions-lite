<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use StanleyKinkelaar\LaravelBrowserSessionsLite\Repositories\SessionRepository;

beforeEach(function () {
    $this->repository = new SessionRepository;
});

it('can get sessions for a user', function () {
    $userId = 1;

    DB::shouldReceive('table')
        ->with('sessions')
        ->once()
        ->andReturnSelf();

    DB::shouldReceive('where')
        ->with('user_id', $userId)
        ->once()
        ->andReturnSelf();

    DB::shouldReceive('orderBy')
        ->with('last_activity', 'desc')
        ->once()
        ->andReturnSelf();

    DB::shouldReceive('get')
        ->once()
        ->andReturn(collect([
            (object) [
                'id' => 'session-1',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                'last_activity' => now()->timestamp,
            ],
        ]));

    Session::shouldReceive('getId')
        ->andReturn('session-1');

    $sessions = $this->repository->getSessionsForUser($userId);

    expect($sessions)->toHaveCount(1)
        ->and($sessions->first())->toBeArray()
        ->and($sessions->first()['id'])->toBe('session-1')
        ->and($sessions->first()['ip_address'])->toBe('127.0.0.1')
        ->and($sessions->first()['is_current'])->toBeTrue();
});

it('can get the current session id', function () {
    Session::shouldReceive('getId')
        ->once()
        ->andReturn('current-session-id');

    $sessionId = $this->repository->getCurrentSessionId();

    expect($sessionId)->toBe('current-session-id');
});

it('can delete other sessions for a user', function () {
    $userId = 1;
    $currentSessionId = 'current-session-id';

    DB::shouldReceive('table')
        ->with('sessions')
        ->once()
        ->andReturnSelf();

    DB::shouldReceive('where')
        ->with('user_id', $userId)
        ->once()
        ->andReturnSelf();

    DB::shouldReceive('where')
        ->with('id', '!=', $currentSessionId)
        ->once()
        ->andReturnSelf();

    DB::shouldReceive('delete')
        ->once()
        ->andReturn(2);

    $deleted = $this->repository->deleteOtherSessionsForUser($userId, $currentSessionId);

    expect($deleted)->toBe(2);
});

it('correctly detects iOS devices', function () {
    $userAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)';

    DB::shouldReceive('table')->andReturnSelf();
    DB::shouldReceive('where')->andReturnSelf();
    DB::shouldReceive('orderBy')->andReturnSelf();
    DB::shouldReceive('get')->andReturn(collect([
        (object) [
            'id' => 'test',
            'ip_address' => '127.0.0.1',
            'user_agent' => $userAgent,
            'last_activity' => now()->timestamp,
        ],
    ]));

    Session::shouldReceive('getId')->andReturn('test');

    $sessions = $this->repository->getSessionsForUser(1);

    expect($sessions->first()['device_hint'])->toBe('iOS Device');
});

it('correctly detects Android devices', function () {
    $userAgent = 'Mozilla/5.0 (Linux; Android 10) AppleWebKit/537.36';

    DB::shouldReceive('table')->andReturnSelf();
    DB::shouldReceive('where')->andReturnSelf();
    DB::shouldReceive('orderBy')->andReturnSelf();
    DB::shouldReceive('get')->andReturn(collect([
        (object) [
            'id' => 'test',
            'ip_address' => '127.0.0.1',
            'user_agent' => $userAgent,
            'last_activity' => now()->timestamp,
        ],
    ]));

    Session::shouldReceive('getId')->andReturn('test');

    $sessions = $this->repository->getSessionsForUser(1);

    expect($sessions->first()['device_hint'])->toBe('Android Device');
});

it('correctly detects Chrome browser', function () {
    $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/91.0';

    DB::shouldReceive('table')->andReturnSelf();
    DB::shouldReceive('where')->andReturnSelf();
    DB::shouldReceive('orderBy')->andReturnSelf();
    DB::shouldReceive('get')->andReturn(collect([
        (object) [
            'id' => 'test',
            'ip_address' => '127.0.0.1',
            'user_agent' => $userAgent,
            'last_activity' => now()->timestamp,
        ],
    ]));

    Session::shouldReceive('getId')->andReturn('test');

    $sessions = $this->repository->getSessionsForUser(1);

    expect($sessions->first()['device_hint'])->toBe('Chrome Browser');
});

it('handles empty user agent', function () {
    DB::shouldReceive('table')->andReturnSelf();
    DB::shouldReceive('where')->andReturnSelf();
    DB::shouldReceive('orderBy')->andReturnSelf();
    DB::shouldReceive('get')->andReturn(collect([
        (object) [
            'id' => 'test',
            'ip_address' => '127.0.0.1',
            'user_agent' => '',
            'last_activity' => now()->timestamp,
        ],
    ]));

    Session::shouldReceive('getId')->andReturn('test');

    $sessions = $this->repository->getSessionsForUser(1);

    expect($sessions->first()['device_hint'])->toBe('Unknown Device');
});
