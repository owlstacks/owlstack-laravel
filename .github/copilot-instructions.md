You are working on **owlstack-laravel**, a Laravel integration package for the Owlstack social media publishing platform.

## Key Facts

- This is a **thin wrapper** around `owlstack/owlstack-core`. All platform logic, formatting, and HTTP transport live in the core package.
- Supports Laravel 10.x, 11.x, and 12.x with PHP 8.1+.
- Namespace: `Owlstack\Laravel\`
- Uses Orchestra Testbench for testing.

## Code Conventions

- Always use `declare(strict_types=1);` in every PHP file.
- Follow PSR-12 coding standards and PSR-4 autoloading.
- Fully type all parameters and return types.
- Use readonly constructor promotion for value objects.
- One class per file.

## Architecture

- `OwlstackServiceProvider` registers all core services into the Laravel container.
- `SendTo` is the high-level API — methods like `telegram()`, `twitter()`, `facebook()`.
- `Owlstack` facade proxies to `SendTo`.
- `LaravelEventDispatcher` bridges core events to Laravel's event system.
- Config lives in `config/owlstack.php`, populated from `.env`.

## Rules

- Never duplicate platform logic from owlstack-core.
- Never hardcode API URLs or credentials.
- Never commit real API tokens.
- Never use `dd()`, `var_dump`, or `print_r` in production code.
- Platform-specific changes belong in owlstack-core, not here.
- Laravel-specific features (artisan, queues, middleware) belong here.

## Testing

- Run tests with `./vendor/bin/phpunit` or `composer test`.
- Mock `HttpClientInterface` to avoid real API calls.
- Unit tests in `tests/Unit/`, feature tests in `tests/Feature/`.
