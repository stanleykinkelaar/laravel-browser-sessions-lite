# Contributing to Laravel Browser Sessions Lite

First off, thank you for considering contributing to Laravel Browser Sessions Lite! 🎉

We love receiving contributions from our community. Here are some guidelines to help you get started.

## 🤝 How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check existing issues to avoid duplicates. When creating a bug report, include:

- **Clear descriptive title**
- **Steps to reproduce** the issue
- **Expected behavior**
- **Actual behavior**
- **Laravel version** and **PHP version**
- **Package version**
- **Code samples** (if applicable)

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, include:

- **Clear descriptive title**
- **Use case** - why would this feature be useful?
- **Proposed solution** (optional)
- **Alternative solutions** you've considered

### Pull Requests

1. **Fork the repository** and create your branch from `main`
2. **Write tests** for your changes
3. **Update documentation** if needed
4. **Ensure tests pass**
5. **Follow code style** (Laravel Pint)
6. **Write meaningful commit messages**

## 🔧 Development Setup

### Prerequisites

- **PHP 8.2+** (PHP 8.3+ recommended for latest Pest version)
- **Composer 2.x**
- **Git**
- **SQLite PDO extension** (for integration tests)

### Installing PHP Extensions (if needed)

#### Ubuntu/Debian/WSL

```bash
sudo apt-get update
sudo apt-get install php-sqlite3 php-mbstring php-xml php-curl
```

#### macOS (Homebrew)

```bash
brew install php
# SQLite is usually included
```

#### Windows

Download PHP from [windows.php.net](https://windows.php.net/download/) with extensions included.

### Clone & Install

```bash
# Clone your fork
git clone https://github.com/YOUR-USERNAME/laravel-browser-sessions-lite.git
cd laravel-browser-sessions-lite

# Install dependencies
composer install
```

## 🧪 Running Tests

This package uses **Pest PHP** for testing.

### Run All Tests

```bash
composer test
```

### Run Specific Test File

```bash
vendor/bin/pest tests/Unit/SessionRepositoryTest.php
```

### Run Tests with Coverage

```bash
composer test-coverage
```

This generates an HTML coverage report in `coverage/` directory.

### Watch Mode (requires Pest globally)

```bash
pest --watch
```

## 📊 Code Quality Tools

### Static Analysis (PHPStan)

```bash
composer analyse
```

Fix any issues before submitting your PR. PHPStan level is set to `max` for strict type checking.

### Code Formatting (Laravel Pint)

```bash
# Check code style
composer format

# Auto-fix code style
vendor/bin/pint
```

**Important:** All code must pass Pint checks before merging.

### Architecture Tests

We use Pest's architecture testing to enforce code standards:

```bash
vendor/bin/pest tests/ArchTest.php
```

Rules enforced:
- No debugging functions (`dd`, `dump`, `var_dump`)
- Strict typing enabled
- No dead code

## 📝 Coding Standards

We follow **Laravel coding standards** and **Spatie conventions**.

### General Rules

✅ **Do:**
- Use type hints for parameters and return types
- Write descriptive variable and method names
- Keep methods focused and small
- Document complex logic with comments
- Follow PSR-12 code style

❌ **Don't:**
- Use debugging functions in production code
- Leave commented-out code
- Add unnecessary dependencies
- Over-engineer simple solutions

### Example: Good Code

```php
/**
 * Get all sessions for a specific user.
 */
public function getSessionsForUser(int $userId): Collection
{
    return DB::table(config('session.table', 'sessions'))
        ->where('user_id', $userId)
        ->orderBy('last_activity', 'desc')
        ->get()
        ->map(fn ($session) => $this->transformSession($session));
}
```

### Example: Bad Code

```php
// Don't do this
public function getSessions($id) {
    $data = DB::table('sessions')->where('user_id', $id)->get();
    // dd($data); // Debugging left in!
    return $data;
}
```

## 🧬 Package Structure

Understanding the package structure helps you make better contributions:

```
src/
├── LaravelBrowserSessionsLiteServiceProvider.php  # Service provider
├── Http/Controllers/
│   └── BrowserSessionsController.php              # Web + JSON controller
├── Services/
│   └── BrowserSessions.php                        # Business logic
├── Repositories/
│   └── SessionRepository.php                      # Data layer
└── Facades/
    └── LaravelBrowserSessionsLite.php             # Facade

tests/
├── Feature/                                       # Integration tests
│   ├── BrowserSessionsControllerTest.php
│   └── ListBrowserSessionsTest.php
├── Unit/                                          # Unit tests
│   ├── BrowserSessionsServiceTest.php
│   └── SessionRepositoryTest.php
└── TestCase.php                                   # Base test case
```

### Design Patterns Used

- **Repository Pattern** - Separates data access from business logic
- **Service Layer** - Handles authentication and password verification
- **Dependency Injection** - All dependencies injected via constructor
- **Facade Pattern** - Provides convenient static access

## ✍️ Writing Tests

### Test Structure

We use **Pest PHP** with descriptive test names:

```php
it('can logout other sessions with valid password', function () {
    // Arrange
    $user = User::factory()->create(['password' => Hash::make('password')]);
    $this->actingAs($user);

    // Act
    $count = $this->service->logoutOtherSessionsWithPassword('password');

    // Assert
    expect($count)->toBeInt();
});
```

### Test Coverage Goals

- **Unit tests** - Cover all service and repository methods
- **Feature tests** - Cover HTTP endpoints and user flows
- **Edge cases** - Test error handling and validation

### Mocking External Dependencies

```php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

it('mocks database queries', function () {
    DB::shouldReceive('table')
        ->with('sessions')
        ->andReturnSelf();

    DB::shouldReceive('where')
        ->andReturnSelf();

    DB::shouldReceive('get')
        ->andReturn(collect([/* test data */]));

    // Your test code...
});
```

## 📦 Testing Locally in a Laravel App

Want to test your changes in a real Laravel application?

### Option 1: Composer Path Repository

Add to your Laravel app's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../laravel-browser-sessions-lite",
            "options": {
                "symlink": true
            }
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

Changes you make to the package will be reflected immediately.

### Option 2: Testbench Workbench

Use Orchestra Testbench's built-in development server:

```bash
cd laravel-browser-sessions-lite
composer run prepare
php vendor/bin/testbench serve
```

Visit `http://localhost:8000/user/browser-sessions` to test the UI.

## 🔄 Git Workflow

### Branch Naming

- `feature/description` - New features
- `fix/description` - Bug fixes
- `docs/description` - Documentation updates
- `refactor/description` - Code improvements

### Commit Messages

We follow [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: add device detection for Linux systems
fix: correct password validation error message
docs: update installation instructions
test: add coverage for edge cases
refactor: simplify session repository query
```

### Pull Request Process

1. **Update your fork**
   ```bash
   git checkout main
   git pull upstream main
   git checkout your-branch
   git rebase main
   ```

2. **Run all checks**
   ```bash
   composer test
   composer analyse
   composer format
   ```

3. **Push to your fork**
   ```bash
   git push origin your-branch
   ```

4. **Create Pull Request**
   - Use a clear title
   - Describe what changed and why
   - Reference related issues (#123)
   - Add screenshots (if UI changes)

5. **Address Review Comments**
   - Be responsive to feedback
   - Push additional commits
   - Re-request review when ready

## 🎯 What Makes a Good PR?

✅ **Good PR:**
- Solves a single problem
- Includes tests
- Updates documentation
- Passes all checks
- Has clear commit messages
- Follows existing code style

❌ **Bad PR:**
- Mixes multiple unrelated changes
- No tests
- Breaks existing tests
- Doesn't follow code style
- Unclear purpose

## 🐛 Debugging Tests

### Test Fails Locally

```bash
# Run with verbose output
vendor/bin/pest --verbose

# Run single test
vendor/bin/pest --filter="can logout other sessions"

# Show full error traces
vendor/bin/pest --bail
```

### PHPStan Issues

```bash
# Generate baseline (if needed)
vendor/bin/phpstan analyse --generate-baseline

# Show errors for specific file
vendor/bin/phpstan analyse src/Services/BrowserSessions.php
```

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Pest PHP Documentation](https://pestphp.com)
- [Spatie Package Tools](https://github.com/spatie/laravel-package-tools)
- [PHPStan Documentation](https://phpstan.org)
- [Laravel Package Development](https://laravelpackage.com)

## 💡 Tips for Contributors

1. **Start small** - Fix a typo, improve docs, add a test
2. **Ask questions** - Open an issue if you're unsure
3. **Be patient** - Reviews take time
4. **Learn from feedback** - Code reviews help everyone improve
5. **Have fun!** - Open source should be enjoyable

## 🎖️ Recognition

All contributors will be:
- Listed in [CHANGELOG.md](CHANGELOG.md)
- Mentioned in release notes
- Added to GitHub contributors page

## 📞 Getting Help

- **Questions?** Open a [GitHub Discussion](../../discussions)
- **Bug reports?** Open an [Issue](../../issues)
- **Security issues?** See [SECURITY.md](SECURITY.md)

## 📄 License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

<p align="center">
  <strong>Thank you for making Laravel Browser Sessions Lite better! 🙌</strong>
</p>
