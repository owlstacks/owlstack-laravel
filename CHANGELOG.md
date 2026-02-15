# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Complete rewrite on top of `owlstack/owlstack-core` v1.0
- Support for 11 platforms: Telegram, X (Twitter), Facebook, LinkedIn, Instagram, Discord, Slack, Reddit, Pinterest, WhatsApp, and Tumblr
- `SendTo` class with dedicated convenience methods per platform
- `Owlstack` Facade with full docblock coverage for IDE autocompletion
- `LaravelEventDispatcher` to bridge core events into Laravel's event system
- Support for Laravel 10, 11, and 12
- Proxy support with authentication for restricted networks
- Automatic credential-based platform filtering — only configured platforms are registered
- Telegram extended features: location, venue, contact, voice, media groups, inline keyboard, channel signatures
- LinkedIn support with person and organization posting
- Comprehensive PHPUnit test suite (Unit + Feature)
- GitHub Actions CI with PHP 8.1–8.4 and Laravel 10–12 matrix
- Published config file via `vendor:publish --tag=owlstack-config`
- Example Laravel 12 project with controller and routes

### Changed
- Namespace changed from `Alihesari\Larasap` to `Owlstack\Laravel`
- All methods now return `PublishResult` value objects instead of raw arrays
- Facebook Graph API updated to v21.0
- Replaced static `SendTo::platform()` calls with instance methods (DI or Facade)
- Removed dependency on `facebook/graph-sdk` and `facebook/php-business-sdk`

### Removed
- Travis CI config (replaced with GitHub Actions)
- Support for PHP < 8.1
- Support for Laravel < 10
- Legacy `alihesari/larasap` namespace and API

## [1.0.0] - 2024-03-20

### Added
- Initial release as `alihesari/larasap`
- Basic support for Telegram, X (Twitter), and Facebook
- Simple text and media posting
- Basic configuration options

[Unreleased]: https://github.com/alihesari/owlstack-laravel/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/alihesari/owlstack-laravel/releases/tag/v1.0.0 