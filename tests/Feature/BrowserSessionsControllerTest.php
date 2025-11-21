<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

    // Create a test user
    $this->user = createUser();
});

it('shows browser sessions page to authenticated user', function () {
    // Insert a session for the user
    DB::table('sessions')->insert([
        'id' => 'test-session-1',
        'user_id' => $this->user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0 (Macintosh)',
        'payload' => '',
        'last_activity' => now()->timestamp,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('browser-sessions.index'));

    $response->assertStatus(200)
        ->assertSee('Browser Sessions')
        ->assertSee('127.0.0.1');
});

it('requires authentication to view browser sessions', function () {
    $response = $this->get(route('browser-sessions.index'));

    $response->assertRedirect(route('login'));
});

it('returns json when requested', function () {
    DB::table('sessions')->insert([
        'id' => 'test-session-1',
        'user_id' => $this->user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0',
        'payload' => '',
        'last_activity' => now()->timestamp,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('browser-sessions.index'));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'sessions',
            'count',
        ])
        ->assertJson([
            'count' => 1,
        ]);
});

it('can logout other browser sessions with valid password', function () {
    // Insert multiple sessions
    DB::table('sessions')->insert([
        [
            'id' => 'session-1',
            'user_id' => $this->user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Chrome',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ],
        [
            'id' => 'session-2',
            'user_id' => $this->user->id,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Firefox',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ],
    ]);

    $response = $this->actingAs($this->user)
        ->delete(route('browser-sessions.destroy'), [
            'password' => 'password',
        ]);

    $response->assertRedirect()
        ->assertSessionHas('status', 'Successfully logged out other browser sessions.');
});

it('fails to logout with incorrect password', function () {
    $response = $this->actingAs($this->user)
        ->delete(route('browser-sessions.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response->assertSessionHasErrors('password');
});

it('requires password to logout other sessions', function () {
    $response = $this->actingAs($this->user)
        ->delete(route('browser-sessions.destroy'), []);

    $response->assertSessionHasErrors('password');
});

it('returns json response when destroying sessions via api', function () {
    DB::table('sessions')->insert([
        'id' => 'session-1',
        'user_id' => $this->user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Chrome',
        'payload' => '',
        'last_activity' => now()->timestamp,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson(route('browser-sessions.destroy'), [
            'password' => 'password',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Successfully logged out other browser sessions.',
        ]);
});

// Helper function to create a test user
function createUser()
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
