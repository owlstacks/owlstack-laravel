# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-02-15

Complete rewrite on top of `owlstack/owlstack-core` v1.0. This is a **breaking release** — see the migration guide in the README.

### Added
- Full integration with `owlstack/owlstack-core` ^1.0 as the publishing engine
- Support for 11 platforms: Telegram, X (Twitter), Facebook, LinkedIn, Instagram, Discord, Slack, Reddit, Pinterest, WhatsApp, and Tumblr
- `SendTo` class with dedicated convenience methods per platform (`telegram()`, `twitter()`, `facebook()`, `linkedin()`, `reddit()`, `discord()`, `slack()`, `instagram()`, `pinterest()`, `whatsapp()`, `tumblr()`)
- `Owlstack` Facade with full docblock coverage for IDE autocompletion
- `LaravelEventDispatcher` to bridge core events (`PostPublished`, `PostFailed`) into Laravel's event system
- Support for Laravel 10, 11, and 12
- Proxy support with authentication for restricted networks
- Automatic credential-based platform filtering — only configured platforms are registered
- Telegram extended features: location, venue, contact, voice, media groups, inline keyboard, channel signatures
- LinkedIn support with person and organization posting
- `publish()` and `toAll()` methods for advanced use with `Post` objects
- Comprehensive PHPUnit test suite (Unit + Feature)
- GitHub Actions CI with PHP 8.1–8.4 and Laravel 10–12 matrix
- Published config file via `vendor:publish --tag=owlstack-config`
- Example Laravel 12 project with controller and routes
- AI agent guidance files (AGENTS.md, CLAUDE.md, .cursorrules, copilot-instructions.md)
- README with centered logo, flat-square badges, and Laravel branding

### Changed
- Namespace changed from `Alihesari\Larasap` to `Owlstack\Laravel`
- All methods now return `PublishResult` value objects instead of raw arrays
- Facebook Graph API updated to v21.0
- Replaced static `SendTo::platform()` calls with instance methods (DI or Facade)
- Migrated from local path repository to Packagist dependency (`owlstack/owlstack-core` ^1.0)
- Set `minimum-stability` to `stable`
- Contact email updated to ali@alihesari.com

### Removed
- Legacy `alihesari/larasap` namespace, API, and all legacy code
- Travis CI config (replaced with GitHub Actions)
- Dependency on `facebook/graph-sdk` and `facebook/php-business-sdk`
- Support for PHP < 8.1
- Support for Laravel < 10

### Since 2.0.0-beta
- Removed all legacy Larasap code
- Rewrote `SendTo` as instance-based API (no more static calls)
- Added `LaravelEventDispatcher` for core event bridging
- Added 8 additional platform convenience methods (LinkedIn, Reddit, Discord, Slack, Instagram, Pinterest, WhatsApp, Tumblr)
- Renamed internal namespace from Synglify to Owlstack
- Migrated to Packagist with stable dependency on `owlstack/owlstack-core` ^1.0
- Replaced Travis CI with GitHub Actions (PHP 8.1–8.4 × Laravel 10–12 matrix)
- Added comprehensive unit and feature tests
- Updated README with logo, badges, and full platform documentation
- Added AI agent guidance files for Copilot, Claude, and Cursor

## [2.0.0-beta] - 2025-12-01

## [1.0.0] - 2024-03-20

### Added
- Initial release as `alihesari/larasap`
- Basic support for Telegram, X (Twitter), and Facebook
- Simple text and media posting
- Basic configuration options

[2.0.0]: https://github.com/alihesari/owlstack-laravel/compare/v2.0.0-beta...v2.0.0
[2.0.0-beta]: https://github.com/alihesari/owlstack-laravel/compare/v1.0.0...v2.0.0-beta
[1.0.0]: https://github.com/alihesari/owlstack-laravel/releases/tag/v1.0.0 