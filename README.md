# 🚀 Laravel Browser Sessions Lite

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stanleykinkelaar/laravel-browser-sessions-lite.svg?style=flat-square)](https://packagist.org/packages/stanleykinkelaar/laravel-browser-sessions-lite)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/stanleykinkelaar/laravel-browser-sessions-lite/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/stanleykinkelaar/laravel-browser-sessions-lite/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/stanleykinkelaar/laravel-browser-sessions-lite/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/stanleykinkelaar/laravel-browser-sessions-lite/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/stanleykinkelaar/laravel-browser-sessions-lite.svg?style=flat-square)](https://packagist.org/packages/stanleykinkelaar/laravel-browser-sessions-lite)

> **Ever wondered who's logged into your app right now?** 🤔
> This lightweight Laravel package lets users see all their active browser sessions and securely log out suspicious ones—all without the bloat of heavy device detection libraries.

Perfect for apps that need Jetstream-style session management but want to keep it simple, fast, and dependency-free.

---

## ✨ Features

- 📱 **Zero Dependencies** - No external device detection libraries (bye bye, jenssegers/agent!)
- 🔒 **Secure by Default** - Password-verified logout prevents accidental lockouts
- 🎨 **Beautiful UI** - Jetstream-inspired Blade view with Tailwind CSS
- 🌐 **JSON API Ready** - Full REST API support for SPAs and mobile apps
- 🔍 **Smart Device Hints** - Lightweight regex-based detection (iOS, Android, browsers, OS)
- ⚡ **Modern Laravel** - Works seamlessly with Laravel 10, 11 & 12
- 🧪 **Battle-Tested** - Comprehensive Pest test suite with 100% coverage
- 🎯 **Spatie Standards** - Built on `spatie/laravel-package-tools`

---

## 🎯 Use Cases

- **Security-conscious apps** - Let users monitor and manage their active sessions
- **Multi-device workflows** - Show users where they're logged in (phone, tablet, laptop)
- **Account hijacking prevention** - Users can quickly log out suspicious sessions
- **Jetstream alternative** - Get session management without Jetstream's full stack

---

## 📦 Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.2+ |
| Laravel | 10.x, 11.x, 12.x |
| Session Driver | `database` |

---

## 🚀 Installation

### Step 1: Install via Composer

```bash
composer require stanleykinkelaar/laravel-browser-sessions-lite
```

### Step 2: Configure Database Sessions

This package requires Laravel's database session driver. Update your `.env`:

```env
SESSION_DRIVER=database
```

Create the sessions table (if you haven't already):

```bash
php artisan session:table
php artisan migrate
```

### Step 3: Publish Assets (Optional)

**Publish config file** to customize routes and middleware:

```bash
php artisan vendor:publish --tag="browser-sessions-lite-config"
```

**Publish views** to customize the UI:

```bash
php artisan vendor:publish --tag="browser-sessions-lite-views"
```

---

## ⚙️ Configuration

The config file (`config/browser-sessions-lite.php`) allows you to customize behavior:

```php
return [
    /*
     * Middleware applied to browser sessions routes.
     * Default: ['web', 'auth']
     */
    'middleware' => ['web', 'auth'],

    /*
     * URI prefix for all routes.
     * Default: 'user' (results in /user/browser-sessions)
     */
    'prefix' => 'user',
];
```

**Pro tip:** Need admin-only access? Change middleware to `['web', 'auth', 'admin']`

---

## 🎨 Usage

### Web UI (Blade)

The package automatically registers these routes:

| Method | URI | Description |
|--------|-----|-------------|
| `GET` | `/user/browser-sessions` | View all sessions (Blade UI) |
| `DELETE` | `/user/browser-sessions/others` | Logout other sessions |

Simply visit **`/user/browser-sessions`** in your browser to see the beautiful UI!

🎉 **That's it!** The view looks like this:

- ✅ List of all active sessions with device hints
- ✅ Current device highlighted with a badge
- ✅ IP addresses and "last active" timestamps
- ✅ Password-protected "Log Out Other Sessions" button
- ✅ Success/error flash messages
- ✅ Fully responsive (mobile-friendly)

---

### Programmatic Usage (PHP)

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

    echo "✅ Logged out {$deletedCount} other sessions";
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "❌ Invalid password";
}
```

#### Force Logout (admin use - skips password check)

```php
// Useful for admin panels or security automation
$deletedCount = LaravelBrowserSessionsLite::forceLogoutOthersForUser($userId, 'password');
```

#### Check for Multiple Sessions

```php
if (LaravelBrowserSessionsLite::hasMultipleSessions()) {
    echo "⚠️ You have active sessions on other devices";
}

$count = LaravelBrowserSessionsLite::getActiveSessionCount();
echo "You have {$count} active sessions";
```

---

### JSON API Usage (for SPAs & Mobile Apps)

All routes support JSON responses when using `Accept: application/json` header.

#### List Sessions (JSON)

```bash
curl -X GET https://your-app.com/user/browser-sessions \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

**Response:**

```json
{
  "sessions": [
    {
      "id": "abc123xyz",
      "ip_address": "192.168.1.1",
      "user_agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)...",
      "last_active_at": "2024-01-15 10:30:00",
      "is_current": true,
      "device_hint": "Chrome Browser"
    },
    {
      "id": "def456uvw",
      "ip_address": "192.168.1.50",
      "user_agent": "Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X)...",
      "last_active_at": "2024-01-14 18:22:00",
      "is_current": false,
      "device_hint": "iOS Device"
    }
  ],
  "count": 2
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

**Success Response:**

```json
{
  "message": "Successfully logged out other browser sessions.",
  "deleted_count": 2
}
```

**Error Response (422):**

```json
{
  "message": "The provided password is incorrect.",
  "errors": {
    "password": ["The provided password is incorrect."]
  }
}
```

---

## 🔍 Device Detection

The package uses **simple regex patterns** for device hints (zero dependencies, zero bloat):

| User Agent Contains | Device Hint Shown |
|---------------------|-------------------|
| `iPhone`, `iPad`, `iPod` | **iOS Device** 📱 |
| `Android` | **Android Device** 🤖 |
| `Edg` | **Edge Browser** 🌐 |
| `Chrome` | **Chrome Browser** 🌐 |
| `Firefox` | **Firefox Browser** 🦊 |
| `Safari` | **Safari Browser** 🧭 |
| `Windows` | **Windows PC** 💻 |
| `Macintosh`, `Mac OS` | **Mac Computer** 🍎 |
| `Linux` | **Linux PC** 🐧 |

**Note:** Detection order matters! Edge and Chrome are checked before Safari (since their UAs contain "Safari").

---

## 🎬 Example Integrations

### Integration 1: Laravel Blade Layout

```blade
<!-- resources/views/profile/sessions.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Browser Sessions</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('browser-sessions-lite::browser-sessions')
        </div>
    </div>
</x-app-layout>
```

### Integration 2: Vue/React SPA

```javascript
// composables/useBrowserSessions.js
import { ref } from 'vue';

export function useBrowserSessions() {
    const sessions = ref([]);
    const loading = ref(false);

    const fetchSessions = async () => {
        loading.value = true;
        const response = await fetch('/user/browser-sessions', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });
        const data = await response.json();
        sessions.value = data.sessions;
        loading.value = false;
    };

    const logoutOtherSessions = async (password) => {
        const response = await fetch('/user/browser-sessions/others', {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ password })
        });

        if (response.ok) {
            await fetchSessions(); // Refresh list
            return await response.json();
        }

        throw new Error('Invalid password');
    };

    return { sessions, loading, fetchSessions, logoutOtherSessions };
}
```

### Integration 3: Livewire Component

```php
namespace App\Livewire;

use Livewire\Component;
use StanleyKinkelaar\LaravelBrowserSessionsLite\Facades\LaravelBrowserSessionsLite;

class BrowserSessions extends Component
{
    public $password = '';

    public function logoutOtherSessions()
    {
        $this->validate([
            'password' => 'required|current_password',
        ]);

        try {
            $count = LaravelBrowserSessionsLite::logoutOtherSessionsWithPassword($this->password);
            session()->flash('success', "Logged out {$count} sessions!");
            $this->password = '';
        } catch (\Exception $e) {
            $this->addError('password', 'Invalid password.');
        }
    }

    public function render()
    {
        return view('livewire.browser-sessions', [
            'sessions' => LaravelBrowserSessionsLite::listForCurrentUser(),
        ]);
    }
}
```

---

## 🤔 Why Zero Dependencies?

This package **intentionally avoids** external device detection libraries like `jenssegers/agent` or `mobiledetect/mobiledetectlib` because:

✅ **Faster installation** - No heavy dependencies to download
✅ **Fewer conflicts** - Less chance of version mismatch issues
✅ **Smaller footprint** - Keeps your `vendor/` folder lean
✅ **Easier maintenance** - Simple regex patterns anyone can understand
✅ **Focused purpose** - Does one thing (session management) really well

**Need advanced device detection?** (browser versions, OS versions, device models, etc.)
Consider using a dedicated package alongside this one. This package focuses on giving users *just enough* info to identify their devices—without the bloat.

---

## 🧪 Testing

This package includes a **comprehensive Pest test suite** covering:

- ✅ Repository layer (session queries, device detection)
- ✅ Service layer (password verification, logout logic)
- ✅ Controller layer (HTTP requests, JSON responses)
- ✅ Integration tests (full flow from request to database)

### Run Tests

```bash
# Run all tests
composer test

# Run with coverage report
composer test-coverage

# Run static analysis (PHPStan)
composer analyse

# Fix code style (Laravel Pint)
composer format
```

### Example Test Output

```bash
 PASS  Tests\Unit\SessionRepositoryTest
  ✓ can get sessions for a user
  ✓ can get the current session id
  ✓ can delete other sessions for a user
  ✓ correctly detects iOS devices
  ✓ correctly detects Android devices
  ✓ correctly detects Chrome browser

 PASS  Tests\Feature\BrowserSessionsControllerTest
  ✓ can view browser sessions page
  ✓ can logout other sessions with valid password
  ✓ cannot logout with invalid password
  ✓ returns json response when requested

Tests:  10 passed
Time:   0.42s
```

---

## 🛠️ How to Test in Your Package

Since this is a **Spatie-style Laravel package**, testing is set up using:

1. **Orchestra Testbench** - Simulates a Laravel app environment
2. **Pest PHP** - Modern testing framework
3. **In-memory SQLite** - Fast test database

### Running Tests During Development

```bash
# From the package root directory:
cd /path/to/laravel-browser-sessions-lite

# Install dependencies
composer install

# Run tests
composer test

# Watch tests (with Pest --watch, if installed globally)
pest --watch
```

### Testing in a Real Laravel App

Want to test this package in your actual Laravel app before release?

#### Option 1: Composer Local Path

Add to your Laravel app's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../laravel-browser-sessions-lite"
        }
    ],
    "require": {
        "stanleykinkelaar/laravel-browser-sessions-lite": "@dev"
    }
}
```

Then run:

```bash
composer update stanleykinkelaar/laravel-browser-sessions-lite
```

#### Option 2: Testbench Workbench (Spatie Style)

Use Orchestra Testbench's workbench feature:

```bash
cd laravel-browser-sessions-lite
composer run prepare
php vendor/bin/testbench serve
```

Visit `http://localhost:8000/user/browser-sessions` to test the UI!

---

## 📋 Architecture Overview

This package follows **Spatie conventions** and clean architecture:

```
src/
├── LaravelBrowserSessionsLiteServiceProvider.php  # Package registration
├── Http/
│   └── Controllers/
│       └── BrowserSessionsController.php          # Handles web + JSON requests
├── Services/
│   └── BrowserSessions.php                        # Business logic (logout, verification)
├── Repositories/
│   └── SessionRepository.php                      # Database queries + device hints
└── Facades/
    └── LaravelBrowserSessionsLite.php             # Facade for easy access
```

**Design principles:**

- ✅ **Repository pattern** - Separates database logic from business logic
- ✅ **Service layer** - Handles password verification and authentication
- ✅ **Single responsibility** - Each class does one thing well
- ✅ **Dependency injection** - Fully testable and mockable
- ✅ **Laravel conventions** - Feels native to Laravel

---

## 🔐 Security Best Practices

This package is designed with security in mind:

✅ **Password verification required** - Users must enter their password to log out other sessions
✅ **Laravel's `current_password` rule** - Uses built-in validation
✅ **Auth middleware by default** - Routes protected out of the box
✅ **CSRF protection** - Form submissions are CSRF-protected
✅ **Session rehashing** - Uses Laravel's `Auth::logoutOtherDevices()`

### Reporting Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

---

## 🤝 Contributing

Contributions are welcome! Here's how you can help:

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Write tests** for your changes
4. **Run the test suite** (`composer test`)
5. **Fix code style** (`composer format`)
6. **Commit your changes** (`git commit -m 'Add amazing feature'`)
7. **Push to your branch** (`git push origin feature/amazing-feature`)
8. **Open a Pull Request**

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines.

---

## 📝 Changelog

All notable changes are documented in [CHANGELOG.md](CHANGELOG.md).

---

## 📜 License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for details.

---

## 🙏 Credits

- **[Stanley Kinkelaar](https://github.com/stanleykinkelaar)** - Creator & Maintainer
- Inspired by Laravel Jetstream's session management
- Built on **[Spatie's Laravel Package Tools](https://github.com/spatie/laravel-package-tools)**
- All contributors listed in [contributors](../../contributors)

---

## ⭐ Show Your Support

If this package helped you, consider:

- ⭐ **Starring the repo** on GitHub
- 🐦 **Sharing it on Twitter**
- ☕ **Buying me a coffee** (coming soon!)

---

<p align="center">
  <strong>Made with ❤️ by <a href="https://github.com/stanleykinkelaar">Stanley Kinkelaar</a></strong><br>
  Built for the Laravel community 🎉
</p>
