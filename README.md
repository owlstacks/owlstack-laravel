<p align="center">
  <a href="https://fopost.com">
    <picture>
      <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/fopost/fopost-docs/refs/heads/main/static/img/logo-light-transparent.png">
      <img src="https://raw.githubusercontent.com/fopost/fopost-docs/refs/heads/main/static/img/logo-dark-transparent.png" alt="Fopost" height="200px">
    </picture>
  </a>
</p>

<p align="center">
  <strong>Laravel integration for social media publishing</strong>
</p>

<p align="center">
  <a href="https://github.com/fopost/social-laravel/actions/workflows/tests.yml"><img src="https://img.shields.io/github/actions/workflow/status/fopost/social-laravel/tests.yml?branch=master&style=flat-square&label=tests" alt="Tests"></a>
  <a href="https://packagist.org/packages/fopost/social-laravel"><img src="https://img.shields.io/packagist/v/fopost/social-laravel.svg?style=flat-square" alt="Latest Version"></a>
  <a href="https://packagist.org/packages/fopost/social-laravel"><img src="https://img.shields.io/packagist/dt/fopost/social-laravel.svg?style=flat-square" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/fopost/social-laravel"><img src="https://img.shields.io/packagist/php-v/fopost/social-laravel.svg?style=flat-square" alt="PHP Version"></a>
  <a href="https://packagist.org/packages/fopost/social-laravel"><img src="https://img.shields.io/badge/laravel-10.x%20|%2011.x%20|%2012.x-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel Version"></a>
  <a href="LICENSE"><img src="https://img.shields.io/packagist/l/fopost/social-laravel.svg?style=flat-square" alt="License"></a>
  <a href="https://github.com/fopost/social-laravel"><img src="https://img.shields.io/github/stars/fopost/social-laravel?style=flat-square" alt="GitHub Stars"></a>
</p>

---

# Fopost for Laravel

Laravel integration for [Fopost Core](https://github.com/fopost/social-core) — publish content to **11 social media platforms** (Telegram, X/Twitter, Facebook, LinkedIn, Instagram, Discord, Slack, Reddit, Pinterest, WhatsApp, and Tumblr) from your Laravel application.

> **Note:** This package was previously `alihesari/larasap`. It has been rewritten from scratch to use `fopost/social-core` as its engine.

## Requirements

- PHP 8.1+
- Laravel 10, 11, or 12

## Installation

```bash
composer require fopost/social-laravel
```

Publish the config file:

```bash
php artisan vendor:publish --tag=fopost-config
```

## Configuration

Add your credentials to `.env`:

```dotenv
# Telegram
TELEGRAM_BOT_TOKEN=your-bot-token
TELEGRAM_BOT_USERNAME=your_bot
TELEGRAM_CHANNEL_USERNAME=@your_channel
TELEGRAM_CHANNEL_SIGNATURE=              # Optional footer signature
TELEGRAM_PARSE_MODE=HTML                  # HTML or Markdown

# X (Twitter)
TWITTER_CONSUMER_KEY=your-key
TWITTER_CONSUMER_SECRET=your-secret
TWITTER_ACCESS_TOKEN=your-token
TWITTER_ACCESS_TOKEN_SECRET=your-token-secret

# Facebook
FACEBOOK_APP_ID=your-app-id
FACEBOOK_APP_SECRET=your-app-secret
FACEBOOK_PAGE_ACCESS_TOKEN=your-page-token
FACEBOOK_PAGE_ID=your-page-id
FACEBOOK_GRAPH_VERSION=v21.0

# LinkedIn
LINKEDIN_ACCESS_TOKEN=your-access-token
LINKEDIN_PERSON_ID=your-person-id          # For personal posts
LINKEDIN_ORGANIZATION_ID=                   # For company page posts

# Reddit
REDDIT_CLIENT_ID=your-client-id
REDDIT_CLIENT_SECRET=your-client-secret
REDDIT_ACCESS_TOKEN=your-access-token
REDDIT_USERNAME=your-username

# Discord
DISCORD_BOT_TOKEN=your-bot-token
DISCORD_CHANNEL_ID=your-channel-id
DISCORD_WEBHOOK_URL=                        # Optional: use webhook instead

# Slack
SLACK_BOT_TOKEN=xoxb-your-token
SLACK_CHANNEL=#your-channel

# Instagram
INSTAGRAM_ACCESS_TOKEN=your-access-token
INSTAGRAM_ACCOUNT_ID=your-account-id

# Pinterest
PINTEREST_ACCESS_TOKEN=your-access-token
PINTEREST_BOARD_ID=your-board-id

# WhatsApp
WHATSAPP_ACCESS_TOKEN=your-access-token
WHATSAPP_PHONE_NUMBER_ID=your-phone-number-id

# Tumblr
TUMBLR_ACCESS_TOKEN=your-access-token
TUMBLR_BLOG_IDENTIFIER=your-blog.tumblr.com

# Proxy (optional — for restricted networks)
FOPOST_PROXY_HOST=localhost
FOPOST_PROXY_PORT=9050
FOPOST_PROXY_TYPE=7
```

Only platforms with valid credentials are registered. If you leave Twitter credentials empty, only Telegram and Facebook will be available.

## Usage

### Via Dependency Injection (recommended)

```php
use Fopost\Social\Laravel\SendTo;

class PostController extends Controller
{
    public function publish(SendTo $sendTo)
    {
        // Telegram
        $result = $sendTo->telegram('Hello from Laravel!');

        // X (Twitter)
        $result = $sendTo->twitter('Hello from Laravel!');
        $result = $sendTo->x('Hello from Laravel!'); // alias

        // Facebook
        $result = $sendTo->facebook('Check this out!', 'link', [
            'link' => 'https://example.com',
        ]);

        // LinkedIn
        $result = $sendTo->linkedin('Post to LinkedIn!');

        // Discord
        $result = $sendTo->discord('Hello Discord!');

        // Slack
        $result = $sendTo->slack('Hello Slack!');

        // Reddit
        $result = $sendTo->reddit('Post body', [
            'title' => 'Post Title',
            'subreddit' => 'test',
        ]);

        // Instagram
        $result = $sendTo->instagram('Caption', [
            'path' => '/path/to/image.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        // Pinterest
        $result = $sendTo->pinterest('Pin description', [
            'image' => '/path/to/image.jpg',
            'link' => 'https://example.com',
            'title' => 'Pin Title',
        ]);

        // WhatsApp
        $result = $sendTo->whatsapp('Hello!', ['to' => '+1234567890']);

        // Tumblr
        $result = $sendTo->tumblr('Blog post content');
    }
}
```

### Via Facade

```php
use Fopost\Social\Laravel\Facades\FopostSocial;

FopostSocial::telegram('Hello from the facade!');
FopostSocial::twitter('Tweet from the facade!');
FopostSocial::linkedin('Post from the facade!');
```

### Return Value

All methods return a `Fopost\Social\Publishing\PublishResult`:

```php
$result = $sendTo->telegram('Hello!');

$result->success;      // bool
$result->platformName; // 'telegram'
$result->externalId;   // '12345' (message ID)
$result->externalUrl;  // URL if available
$result->error;        // error message if failed
$result->failed();     // bool
```

### Telegram Features

```php
// Text message
$sendTo->telegram('Simple text message');

// Photo with caption
$sendTo->telegram('Photo caption', [
    'type' => 'photo',
    'file' => '/path/to/image.jpg',
]);

// Video
$sendTo->telegram('Video caption', [
    'type' => 'video',
    'file' => '/path/to/video.mp4',
    'duration' => 120,
    'width' => 1920,
    'height' => 1080,
]);

// Audio
$sendTo->telegram('Audio caption', [
    'type' => 'audio',
    'file' => '/path/to/audio.mp3',
    'duration' => 200,
]);

// Document
$sendTo->telegram('Document caption', [
    'type' => 'document',
    'file' => '/path/to/file.pdf',
]);

// Voice message
$sendTo->telegram('', [
    'type' => 'voice',
    'file' => '/path/to/voice.ogg',
    'duration' => 15,
]);

// Location
$sendTo->telegram('', [
    'type' => 'location',
    'latitude' => 51.5074,
    'longitude' => -0.1278,
    'live_period' => 600, // optional
]);

// Venue
$sendTo->telegram('', [
    'type' => 'venue',
    'latitude' => 51.5074,
    'longitude' => -0.1278,
    'title' => 'Coffee Shop',
    'address' => '123 Main St',
]);

// Contact
$sendTo->telegram('', [
    'type' => 'contact',
    'phone_number' => '+1234567890',
    'first_name' => 'John',
    'last_name' => 'Doe',
]);

// Media group
$sendTo->telegram('Album caption', [
    'type' => 'media_group',
    'files' => [
        ['type' => 'photo', 'media' => '/path/to/img1.jpg'],
        ['type' => 'photo', 'media' => '/path/to/img2.jpg'],
    ],
]);

// Inline keyboard
$sendTo->telegram('Click below!', null, [
    [['text' => 'Visit', 'url' => 'https://example.com']],
]);
```

### Twitter / X Features

```php
// Text tweet
$sendTo->twitter('Hello Twitter!');

// Tweet with media
$sendTo->twitter('Check this photo!', [
    'path' => '/path/to/image.jpg',
    'mime_type' => 'image/jpeg',
]);

// Multiple media
$sendTo->twitter('Multiple images!', [
    ['path' => '/path/to/img1.jpg', 'mime_type' => 'image/jpeg'],
    ['path' => '/path/to/img2.jpg', 'mime_type' => 'image/jpeg'],
]);
```

### Facebook Features

```php
// Link post
$sendTo->facebook('Check this article!', 'link', [
    'link' => 'https://example.com/article',
]);

// Photo post
$sendTo->facebook('Beautiful photo!', 'photo', [
    'photo' => '/path/to/image.jpg',
]);

// Video post
$sendTo->facebook('Watch this!', 'video', [
    'video' => '/path/to/video.mp4',
    'title' => 'My Video',
    'description' => 'A great video.',
]);
```

### LinkedIn Features

```php
// Text post
$sendTo->linkedin('Hello LinkedIn!');

// Post with image
$sendTo->linkedin('Check out this image!', [
    'path' => '/path/to/image.jpg',
    'mime_type' => 'image/jpeg',
]);
```

### Discord Features

```php
// Text message
$sendTo->discord('Hello Discord!');

// Message with media
$sendTo->discord('Check this out!', [
    'path' => '/path/to/image.jpg',
    'mime_type' => 'image/jpeg',
]);
```

### Slack Features

```php
$sendTo->slack('Hello Slack!');
```

### Reddit Features

```php
$sendTo->reddit('Post body text', [
    'title' => 'Post Title',
    'subreddit' => 'your_subreddit',
]);
```

### Instagram Features

```php
$sendTo->instagram('Beautiful photo!', [
    'path' => '/path/to/image.jpg',
    'mime_type' => 'image/jpeg',
]);
```

### Pinterest Features

```php
$sendTo->pinterest('Pin description', [
    'image' => '/path/to/image.jpg',
    'link' => 'https://example.com',
    'title' => 'Pin Title',
]);
```

### WhatsApp Features

```php
$sendTo->whatsapp('Hello!', ['to' => '+1234567890']);
```

### Tumblr Features

```php
// Text post
$sendTo->tumblr('Blog post content');

// Post with media
$sendTo->tumblr('Photo post', [
    'path' => '/path/to/image.jpg',
    'mime_type' => 'image/jpeg',
]);
```

### Using Post Objects Directly

For full control, create `Fopost\Social\Content\Post` objects:

```php
use Fopost\Social\Content\Post;
use Fopost\Social\Content\Media;
use Fopost\Social\Content\MediaCollection;

$post = new Post(
    title: 'My Article',
    body: 'Full article body text...',
    url: 'https://example.com/article',
    tags: ['laravel', 'php'],
    media: new MediaCollection([
        new Media('/path/to/image.jpg', 'image/jpeg', altText: 'Article image'),
    ]),
);

// Publish to a specific platform
$result = $sendTo->publish($post, 'telegram');

// Publish to all configured platforms
$results = $sendTo->toAll($post);
// Returns: ['telegram' => PublishResult, 'twitter' => PublishResult, ...]
```

### Events

The package dispatches events through Laravel's event system:

- `Fopost\Social\Events\PostPublished` — fired on successful publish
- `Fopost\Social\Events\PostFailed` — fired on publish failure

```php
// In EventServiceProvider or via Event::listen()
use Fopost\Social\Events\PostPublished;
use Fopost\Social\Events\PostFailed;

Event::listen(PostPublished::class, function (PostPublished $event) {
    Log::info("Published to {$event->result->platformName}", [
        'external_id' => $event->result->externalId,
    ]);
});

Event::listen(PostFailed::class, function (PostFailed $event) {
    Log::error("Failed to publish to {$event->result->platformName}", [
        'error' => $event->result->error,
    ]);
});
```

## Architecture

This package is a thin wrapper around `fopost/social-core`. The architecture:

```
Your Laravel App
    └── Fopost\Social\Laravel\SendTo (or Facade)
        └── Fopost\Social\Publishing\Publisher
            └── Fopost\Social\Platforms\{Telegram,Twitter,Facebook,LinkedIn,...}Platform
                └── Fopost\Social\Http\HttpClient (cURL)
```

The service provider wires everything together:
- `FopostConfig` — built from `config/fopost-social.php`
- `HttpClient` — core's cURL client (with optional proxy)
- Platform instances — only registered if credentials are configured
- `PlatformRegistry` — holds all active platforms
- `Publisher` — orchestrates publishing with event dispatch
- `SendTo` — high-level API bound as `'fopost-social'` singleton

## Testing

```bash
composer test
```

Or run PHPUnit directly:

In your own tests, mock `HttpClientInterface` on the container:

```php
use Fopost\Social\Http\Contracts\HttpClientInterface;

$mock = $this->createMock(HttpClientInterface::class);
$mock->method('post')->willReturn([
    'status' => 200,
    'headers' => [],
    'body' => json_encode(['ok' => true, 'result' => ['message_id' => 1]]),
]);

$this->app->instance(HttpClientInterface::class, $mock);
```

## Migration from alihesari/larasap

If upgrading from the old package:

1. Replace `alihesari/larasap` with `fopost/social-laravel` in `composer.json`
2. Rename `config/larasap.php` → `config/fopost-social.php` (see new format above)
3. Replace `Alihesari\Larasap\SendTo` with `Fopost\Social\Laravel\SendTo`
4. Replace static calls (`SendTo::telegram(...)`) with DI or the new `Fopost` facade
5. Update event listeners if you had custom ones
6. The `facebook/graph-sdk` and `facebook/php-business-sdk` dependencies are no longer needed

Key API changes:
- `SendTo::telegram($msg)` → `$sendTo->telegram($msg)` (instance method)
- `SendTo::x($msg)` → `$sendTo->x($msg)` or `$sendTo->twitter($msg)`
- `SendTo::facebook('link', $data)` → `$sendTo->facebook($msg, 'link', $data)`
- All methods now return `PublishResult` instead of raw arrays

## License

MIT


