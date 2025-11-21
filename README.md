# Laravel Browser Sessions Lite

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stanleykinkelaar/laravel-browser-sessions-lite.svg?style=flat-square)](https://packagist.org/packages/stanleykinkelaar/laravel-browser-sessions-lite)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/stanleykinkelaar/laravel-browser-sessions-lite/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/stanleykinkelaar/laravel-browser-sessions-lite/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/stanleykinkelaar/laravel-browser-sessions-lite/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/stanleykinkelaar/laravel-browser-sessions-lite/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/stanleykinkelaar/laravel-browser-sessions-lite.svg?style=flat-square)](https://packagist.org/packages/stanleykinkelaar/laravel-browser-sessions-lite)

A lightweight, **zero-dependency** Laravel package for managing browser sessions with simple device detection and secure logout functionality. Perfect for applications that need basic session management without the overhead of external device detection libraries.

## Features

- 📱 **Zero Dependencies** - No external device detection libraries (no jenssegers/agent or mobiledetect)
- 🔒 **Secure Session Management** - Password-verified logout of other browser sessions
- 🎨 **Beautiful UI** - Jetstream-style Blade view with Tailwind CSS
- 🌐 **JSON API** - Full REST API support for headless applications
- 🔍 **Simple Device Hints** - Regex-based device detection (iOS, Android, browsers)
- ⚡ **Laravel 10, 11 & 12** - Full support for modern Laravel versions
- 🧪 **Fully Tested** - Comprehensive Pest test suite

## Requirements

- PHP 8.2+
- Laravel 10.x, 11.x, or 12.x
- Database session driver (`SESSION_DRIVER=database`)

## Installation

Install the package via Composer:

```bash
composer require stanleykinkelaar/laravel-browser-sessions-lite
```

### Setup Database Sessions

This package requires Laravel's database session driver. Update your `.env`:

```env
SESSION_DRIVER=database
```

Create the sessions table if you haven't already:

```bash
php artisan session:table
php artisan migrate
```

### Publish Configuration (Optional)

Publish the config file to customize routes and middleware:

```bash
php artisan vendor:publish --tag="browser-sessions-lite-config"
```

### Publish Views (Optional)

Customize the Blade view:

```bash
php artisan vendor:publish --tag="browser-sessions-lite-views"
```

## Configuration

The published config file (`config/browser-sessions-lite.php`):

```php
return [
    /*
     * Middleware applied to browser sessions routes
     */
    'middleware' => ['web', 'auth'],

    /*
     * URI prefix for routes (results in /user/browser-sessions)
     */
    'prefix' => 'user',
];
```

## Usage

### Web Routes

The package automatically registers these routes:

```
GET    /user/browser-sessions         - View all sessions (Blade UI)
DELETE /user/browser-sessions/others  - Logout other sessions
```

Simply visit `/user/browser-sessions` in your browser to manage sessions.

### Programmatic Usage

#### List Sessions for Current User

```php
use StanleyKinkelaar\LaravelBrowserSessionsLite\Facades\LaravelBrowserSessionsLite;

$sessions = LaravelBrowserSessionsLite::listForCurrentUser();

foreach ($sessions as $session) {
    echo $session['device_hint'];     // "iOS Device", "Chrome Browser", etc.
    echo $session['ip_address'];       // "192.168.1.1"
    echo $session['is_current'];       // true/false
    echo $session['last_active_at'];   // Carbon instance
}
```

#### Logout Other Sessions (with password verification)

```php
try {
    $deletedCount = LaravelBrowserSessionsLite::logoutOtherSessionsWithPassword('user-password');

    echo "Logged out {$deletedCount} sessions";
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "Invalid password";
}
```

#### Force Logout (admin use - skips password check)

```php
$deletedCount = LaravelBrowserSessionsLite::forceLogoutOthersForUser($userId);
```

#### Check for Multiple Sessions

```php
if (LaravelBrowserSessionsLite::hasMultipleSessions()) {
    echo "You have active sessions on other devices";
}

$count = LaravelBrowserSessionsLite::getActiveSessionCount();
```

### JSON API Usage

All routes support JSON responses when using `Accept: application/json` header.

#### List Sessions (JSON)

```bash
curl -X GET https://your-app.com/user/browser-sessions \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

Response:

```json
{
  "sessions": [
    {
      "id": "abc123",
      "ip_address": "192.168.1.1",
      "user_agent": "Mozilla/5.0...",
      "last_active_at": "2024-01-15 10:30:00",
      "is_current": true,
      "device_hint": "Chrome Browser"
    }
  ],
  "count": 1
}
```

#### Logout Other Sessions (JSON)

```bash
curl -X DELETE https://your-app.com/user/browser-sessions/others \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{"password": "user-password"}'
```

Response:

```json
{
  "message": "Successfully logged out other browser sessions.",
  "deleted_count": 2
}
```

### Device Detection

The package uses simple regex patterns for device hints (zero dependencies):

| User Agent Contains | Device Hint |
|---------------------|-------------|
| iPhone/iPad/iPod | iOS Device |
| Android | Android Device |
| Edg | Edge Browser |
| Chrome | Chrome Browser |
| Firefox | Firefox Browser |
| Safari | Safari Browser |
| Windows | Windows PC |
| Macintosh | Mac Computer |
| Linux | Linux PC |

## Blade View

The included Blade view provides a Jetstream-style UI:

- List of all active sessions with device hints
- IP addresses and last activity timestamps
- "Current Device" badge highlighting
- Password-protected logout form
- Success/error flash messages
- Responsive Tailwind CSS design

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

Run static analysis:

```bash
composer analyse
```

## Example Integration

### In a Laravel Blade Component

```blade
<x-app-layout>
    <x-slot name="header">
        <h2>Browser Sessions</h2>
    </x-slot>

    <div class="py-12">
        @include('browser-sessions-lite::browser-sessions')
    </div>
</x-app-layout>
```

### In a Vue/React SPA

```javascript
// Fetch sessions
const response = await fetch('/user/browser-sessions', {
    headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
    }
});

const { sessions, count } = await response.json();

// Logout other sessions
await fetch('/user/browser-sessions/others', {
    method: 'DELETE',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({ password: 'user-password' })
});
```

## Why Zero Dependencies?

This package intentionally avoids external device detection libraries like `jenssegers/agent` or `mobiledetect/mobiledetectlib` to:

- Reduce package size and installation time
- Minimize dependency conflicts
- Provide simple, maintainable regex-based hints
- Focus on core session management functionality

If you need advanced device detection (browser versions, OS versions, etc.), consider using a dedicated package alongside this one.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Stanley Kinkelaar](https://github.com/stanleykinkelaar)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
