# AGENTS.md

Instructions for AI coding agents (OpenAI Codex, GitHub Copilot Workspace, GPT-based tools) working in this repository.

## Identity

This is **owlstack-laravel**, a Laravel integration package for [Owlstack Core](https://github.com/owlstacks/owlstack-core). It provides a thin, idiomatic Laravel wrapper around the framework-agnostic core library for publishing content to social media platforms.

## Setup

```bash
composer install
```

## Testing

```bash
# Run all tests
./vendor/bin/phpunit

# Run unit tests only
./vendor/bin/phpunit --testsuite=Unit

# Run feature tests only
./vendor/bin/phpunit --testsuite=Feature
```

All tests must pass before submitting changes.

## Code Style

- PHP 8.1+ with `declare(strict_types=1);` in every file.
- Follow PSR-12 coding standard.
- PSR-4 autoloading: `Owlstack\Laravel\` maps to `src/`.
- Fully type all parameters and return types.
- Use readonly constructor promotion for value objects.
- Use named arguments for clarity when constructing objects.

## Architecture Rules

1. **Thin wrapper only.** This package delegates all platform logic, formatting, and HTTP transport to `owlstack/owlstack-core`. Do not duplicate or override core behavior.
2. **Laravel conventions.** Use service providers, facades, config files, and event dispatching in the standard Laravel way.
3. **Auto-discovery.** The service provider and facade alias are registered via `composer.json` `extra.laravel` — no manual registration needed.
4. **Config-driven.** All credentials and settings come from `config/owlstack.php`, populated from `.env` variables. Never hardcode credentials.
5. **Event bridging.** Core events (`PostPublished`, `PostFailed`) are dispatched through Laravel's event system via `LaravelEventDispatcher`.
6. **One class per file.** Each class, interface, and trait lives in its own file.

## File Organization

| Path | Purpose |
|---|---|
| `src/OwlstackServiceProvider.php` | Service provider — registers core services into the Laravel container |
| `src/SendTo.php` | High-level API for publishing to platforms |
| `src/Facades/Owlstack.php` | Laravel facade for `SendTo` |
| `src/Events/LaravelEventDispatcher.php` | Bridges core's `EventDispatcherInterface` to Laravel's event system |
| `config/owlstack.php` | Publishable configuration file |
| `tests/Unit/` | Unit tests (SendTo, ServiceProvider) |
| `tests/Feature/` | Feature/integration tests (Publishing) |
| `tests/fixtures/` | Test fixture files |
| `examples/laravel-12/` | Example Laravel 12 application |

## Key Classes

| Class | Role |
|---|---|
| `OwlstackServiceProvider` | Registers `OwlstackConfig`, `HttpClient`, platform instances, `PlatformRegistry`, `Publisher`, and `SendTo` as singletons |
| `SendTo` | Provides methods like `telegram()`, `twitter()`, `facebook()`, etc. for quick publishing |
| `Owlstack` (Facade) | Static proxy to `SendTo` — resolves the `'owlstack'` binding |
| `LaravelEventDispatcher` | Implements core's `EventDispatcherInterface` using Laravel's `Dispatcher` |

## Adding a New Feature

1. If the feature is platform-specific (new platform, new API method), it belongs in **owlstack-core**, not here.
2. If the feature is Laravel-specific (artisan commands, middleware, queue jobs), add it here.
3. Always write tests — unit tests in `tests/Unit/`, feature tests in `tests/Feature/`.
4. Update `config/owlstack.php` if new configuration keys are needed.

## Commit Guidelines

- Use imperative mood in commit messages (e.g., "Add queue support for publishing").
- One logical change per commit.
- Reference issue numbers when applicable.

## Do Not

- Duplicate platform logic that belongs in `owlstack-core`.
- Use static methods or global state outside facades.
- Hardcode API URLs or credentials.
- Commit real API tokens, secrets, or credentials.
- Suppress errors with the `@` operator.
- Use `var_dump`, `print_r`, or `dd()` in production code.
- Add dependencies on other frameworks (Symfony components are OK if already in Laravel).
