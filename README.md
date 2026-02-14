# Owlstack for Laravel

Laravel integration for [Owlstack Core](https://github.com/alihesari/owlstack-core) — publish content to Telegram, X (Twitter), and Facebook from your Laravel application.

> **Note:** This package was previously `alihesari/larasap`. It has been rewritten from scratch to use `owlstack/owlstack-core` as its engine.

## Requirements

- PHP 8.1+
- Laravel 10, 11, or 12

## Installation

```bash
composer require owlstack/owlstack-laravel
```

Publish the config file:

```bash
php artisan vendor:publish --tag=owlstack-config
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

# Proxy (optional — for restricted networks)
OWLSTACK_PROXY_HOST=localhost
OWLSTACK_PROXY_PORT=9050
OWLSTACK_PROXY_TYPE=7
```

Only platforms with valid credentials are registered. If you leave Twitter credentials empty, only Telegram and Facebook will be available.

## Usage

### Via Dependency Injection (recommended)

```php
use Owlstack\Laravel\SendTo;

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
    }
}
```

### Via Facade

```php
use Owlstack\Laravel\Facades\Owlstack;

Owlstack::telegram('Hello from the facade!');
Owlstack::twitter('Tweet from the facade!');
```

### Return Value

All methods return a `Owlstack\Core\Publishing\PublishResult`:

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

### Using Post Objects Directly

For full control, create `Owlstack\Core\Content\Post` objects:

```php
use Owlstack\Core\Content\Post;
use Owlstack\Core\Content\Media;
use Owlstack\Core\Content\MediaCollection;

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

- `Owlstack\Core\Events\PostPublished` — fired on successful publish
- `Owlstack\Core\Events\PostFailed` — fired on publish failure

```php
// In EventServiceProvider or via Event::listen()
use Owlstack\Core\Events\PostPublished;
use Owlstack\Core\Events\PostFailed;

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

This package is a thin wrapper around `owlstack/owlstack-core`. The architecture:

```
Your Laravel App
    └── Owlstack\Laravel\SendTo (or Facade)
        └── Owlstack\Core\Publishing\Publisher
            └── Owlstack\Core\Platforms\{Telegram,Twitter,Facebook}Platform
                └── Owlstack\Core\Http\HttpClient (cURL)
```

The service provider wires everything together:
- `OwlstackConfig` — built from `config/owlstack.php`
- `HttpClient` — core's cURL client (with optional proxy)
- Platform instances — only registered if credentials are configured
- `PlatformRegistry` — holds all active platforms
- `Publisher` — orchestrates publishing with event dispatch
- `SendTo` — high-level API bound as `'owlstack'` singleton

## Testing

```bash
composer test
# or
./vendor/bin/phpunit
```

In your own tests, mock `HttpClientInterface` on the container:

```php
use Owlstack\Core\Http\Contracts\HttpClientInterface;

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

1. Replace `alihesari/larasap` with `owlstack/owlstack-laravel` in `composer.json`
2. Rename `config/larasap.php` → `config/owlstack.php` (see new format above)
3. Replace `Alihesari\Larasap\SendTo` with `Owlstack\Laravel\SendTo`
4. Replace static calls (`SendTo::telegram(...)`) with DI or the new `Owlstack` facade
5. Update event listeners if you had custom ones
6. The `facebook/graph-sdk` and `facebook/php-business-sdk` dependencies are no longer needed

Key API changes:
- `SendTo::telegram($msg)` → `$sendTo->telegram($msg)` (instance method)
- `SendTo::x($msg)` → `$sendTo->x($msg)` or `$sendTo->twitter($msg)`
- `SendTo::facebook('link', $data)` → `$sendTo->facebook($msg, 'link', $data)`
- All methods now return `PublishResult` instead of raw arrays

## License

MIT


