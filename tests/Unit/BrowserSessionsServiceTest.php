<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use StanleyKinkelaar\LaravelBrowserSessionsLite\Repositories\SessionRepository;
use StanleyKinkelaar\LaravelBrowserSessionsLite\Services\BrowserSessions;

beforeEach(function () {
    $this->repository = Mockery::mock(SessionRepository::class);
    $this->service = new BrowserSessions($this->repository);
});

it('can list sessions for current user', function () {
    $user = Mockery::mock();
    $user->id = 1;

    Auth::shouldReceive('user')
        ->once()
        ->andReturn($user);

    $sessions = collect([
        ['id' => 'session-1', 'is_current' => true],
        ['id' => 'session-2', 'is_current' => false],
    ]);

    $this->repository->shouldReceive('getSessionsForUser')
        ->with(1)
        ->once()
        ->andReturn($sessions);

    $result = $this->service->listForCurrentUser();

    expect($result)->toHaveCount(2)
        ->and($result->first()['id'])->toBe('session-1');
});

it('returns empty collection when no user is authenticated', function () {
    Auth::shouldReceive('user')
        ->once()
        ->andReturn(null);

    $result = $this->service->listForCurrentUser();

    expect($result)->toBeEmpty();
});

it('can logout other sessions with valid password', function () {
    $user = Mockery::mock();
    $user->id = 1;
    $user->password = Hash::make('password');

    Auth::shouldReceive('user')
        ->once()
        ->andReturn($user);

    Hash::shouldReceive('check')
        ->with('password', $user->password)
        ->once()
        ->andReturn(true);

    $this->repository->shouldReceive('getCurrentSessionId')
        ->once()
        ->andReturn('current-session');

    Auth::shouldReceive('logoutOtherDevices')
        ->with('password')
        ->once();

    $this->repository->shouldReceive('deleteOtherSessionsForUser')
        ->with(1, 'current-session')
        ->once()
        ->andReturn(2);

    $result = $this->service->logoutOtherSessionsWithPassword('password');

    expect($result)->toBe(2);
});

it('throws validation exception when password is incorrect', function () {
    $user = Mockery::mock();
    $user->id = 1;
    $user->password = Hash::make('correct-password');

    Auth::shouldReceive('user')
        ->once()
        ->andReturn($user);

    Hash::shouldReceive('check')
        ->with('wrong-password', $user->password)
        ->once()
        ->andReturn(false);

    $this->service->logoutOtherSessionsWithPassword('wrong-password');
})->throws(ValidationException::class, 'The provided password is incorrect.');

it('throws validation exception when user is not authenticated', function () {
    Auth::shouldReceive('user')
        ->once()
        ->andReturn(null);

    $this->service->logoutOtherSessionsWithPassword('password');
})->throws(ValidationException::class);

it('can force logout others for a specific user', function () {
    $this->repository->shouldReceive('getCurrentSessionId')
        ->once()
        ->andReturn('current-session');

    $this->repository->shouldReceive('deleteOtherSessionsForUser')
        ->with(1, 'current-session')
        ->once()
        ->andReturn(3);

    $result = $this->service->forceLogoutOthersForUser(1);

    expect($result)->toBe(3);
});

it('can get active session count', function () {
    $user = Mockery::mock();
    $user->id = 1;

    Auth::shouldReceive('user')
        ->once()
        ->andReturn($user);

    $sessions = collect([
        ['id' => 'session-1'],
        ['id' => 'session-2'],
        ['id' => 'session-3'],
    ]);

    $this->repository->shouldReceive('getSessionsForUser')
        ->with(1)
        ->once()
        ->andReturn($sessions);

    $count = $this->service->getActiveSessionCount();

    expect($count)->toBe(3);
});

it('can check if user has multiple sessions', function () {
    $user = Mockery::mock();
    $user->id = 1;

    Auth::shouldReceive('user')
        ->once()
        ->andReturn($user);

    $sessions = collect([
        ['id' => 'session-1'],
        ['id' => 'session-2'],
    ]);

    $this->repository->shouldReceive('getSessionsForUser')
        ->with(1)
        ->once()
        ->andReturn($sessions);

    $hasMultiple = $this->service->hasMultipleSessions();

    expect($hasMultiple)->toBeTrue();
});

it('returns false when user has only one session', function () {
    $user = Mockery::mock();
    $user->id = 1;

    Auth::shouldReceive('user')
        ->once()
        ->andReturn($user);

    $sessions = collect([
        ['id' => 'session-1'],
    ]);

    $this->repository->shouldReceive('getSessionsForUser')
        ->with(1)
        ->once()
        ->andReturn($sessions);

    $hasMultiple = $this->service->hasMultipleSessions();

    expect($hasMultiple)->toBeFalse();
});
