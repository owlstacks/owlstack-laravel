<?php

declare(strict_types=1);

namespace Owlstack\Laravel;

use Owlstack\Core\Config\OwlstackConfig;
use Owlstack\Core\Content\Media;
use Owlstack\Core\Content\MediaCollection;
use Owlstack\Core\Content\Post;
use Owlstack\Core\Platforms\PlatformRegistry;
use Owlstack\Core\Platforms\Telegram\TelegramPlatform;
use Owlstack\Core\Publishing\Publisher;
use Owlstack\Core\Publishing\PublishResult;

/**
 * High-level Laravel API for publishing content to social media platforms.
 *
 * Usage via DI:
 *     public function publish(SendTo $sendTo) {
 *         $sendTo->telegram('Hello world!');
 *     }
 *
 * Usage via Facade:
 *     Owlstack::telegram('Hello world!');
 */
class SendTo
{
    private const TELEGRAM_TEXT_LENGTH = 4096;
    private const TELEGRAM_CAPTION_LENGTH = 1024;

    public function __construct(
        private readonly Publisher $publisher,
        private readonly OwlstackConfig $config,
        private readonly PlatformRegistry $registry,
    ) {
    }

    // ── Telegram ─────────────────────────────────────────────────────────

    /**
     * Publish a message to Telegram.
     *
     * @param string     $message        The text message to send.
     * @param array|null $attachment      Optional attachment: ['type' => 'photo|video|audio|document|voice|location|venue|contact|media_group', ...].
     * @param array|null $inlineKeyboard Optional inline keyboard buttons.
     * @param array      $options        Additional platform-specific options.
     */
    public function telegram(
        string $message,
        ?array $attachment = null,
        ?array $inlineKeyboard = null,
        array $options = [],
    ): PublishResult {
        $credentials = $this->config->credentials('telegram');
        $chatId = $options['chat_id'] ?? $credentials?->get('channel_username');
        $parseMode = $credentials?->get('parse_mode', 'HTML') ?? 'HTML';
        $signature = $credentials?->get('channel_signature', '') ?? '';

        // Handle extended Telegram types that use direct Bot API methods
        if ($attachment !== null) {
            $type = $attachment['type'] ?? '';

            if (in_array($type, ['location', 'venue', 'contact', 'voice'], true)) {
                return $this->handleTelegramExtended($type, $message, $attachment, $inlineKeyboard, $options);
            }
        }

        // Append signature to message text
        if ($signature !== '') {
            $message = $this->assignSignature($message, $attachment !== null ? 'caption' : 'text', $signature);
        }

        // Build Post + options for the core Publisher
        $media = $this->buildMediaFromAttachment($attachment);
        $post = new Post(
            title: '',
            body: $message,
            media: $media,
        );

        $publishOptions = [
            'chat_id' => $chatId,
            'parse_mode' => $parseMode,
        ];

        if ($inlineKeyboard !== null) {
            $publishOptions['inline_keyboard'] = $inlineKeyboard;
        }

        // Pass through extra attachment params as options (duration, width, height, etc.)
        if ($attachment !== null) {
            foreach (['duration', 'width', 'height'] as $key) {
                if (isset($attachment[$key])) {
                    $publishOptions[$key] = $attachment[$key];
                }
            }
        }

        return $this->publisher->publish($post, 'telegram', array_merge($publishOptions, $options));
    }

    // ── Twitter / X ──────────────────────────────────────────────────────

    /**
     * Publish a message to Twitter (X).
     *
     * @param string          $message The tweet text.
     * @param array|null      $media   Optional media: ['path' => ..., 'mime_type' => ...] or array of such items.
     * @param array           $options Additional platform-specific options.
     */
    public function twitter(string $message, ?array $media = null, array $options = []): PublishResult
    {
        $mediaCollection = $this->buildMediaCollection($media);

        $post = new Post(
            title: '',
            body: $message,
            media: $mediaCollection,
        );

        return $this->publisher->publish($post, 'twitter', $options);
    }

    /**
     * Alias for twitter().
     */
    public function x(string $message, ?array $media = null, array $options = []): PublishResult
    {
        return $this->twitter($message, $media, $options);
    }

    // ── Facebook ─────────────────────────────────────────────────────────

    /**
     * Publish a message to Facebook.
     *
     * @param string $message The message text.
     * @param string $type    Post type: 'link', 'photo', 'video'.
     * @param array  $data    Type-specific data (e.g. ['link' => '...'], ['photo' => '...'], ['video' => '...']).
     * @param array  $options Additional platform-specific options.
     */
    public function facebook(string $message, string $type = 'link', array $data = [], array $options = []): PublishResult
    {
        $media = null;

        if ($type === 'photo' && isset($data['photo'])) {
            $media = new MediaCollection([
                new Media($data['photo'], 'image/jpeg'),
            ]);
        } elseif ($type === 'video' && isset($data['video'])) {
            $media = new MediaCollection([
                new Media($data['video'], 'video/mp4'),
            ]);
        }

        $url = $data['link'] ?? null;

        $post = new Post(
            title: $data['title'] ?? '',
            body: $message,
            url: $url,
            media: $media,
            metadata: $data,
        );

        $publishOptions = array_merge(['type' => $type], $options);

        return $this->publisher->publish($post, 'facebook', $publishOptions);
    }

    // ── LinkedIn ─────────────────────────────────────────────────────────

    /**
     * Publish a message to LinkedIn.
     *
     * @param string     $message The post text (max 3000 characters).
     * @param array|null $media   Optional media: ['path' => ..., 'mime_type' => ...].
     * @param array      $options Additional platform-specific options.
     */
    public function linkedin(string $message, ?array $media = null, array $options = []): PublishResult
    {
        $mediaCollection = $this->buildMediaCollection($media);

        $post = new Post(
            title: '',
            body: $message,
            media: $mediaCollection,
        );

        return $this->publisher->publish($post, 'linkedin', $options);
    }

    // ── Other Platforms ──────────────────────────────────────────────────

    /**
     * Publish a message to Reddit.
     *
     * @param string $message The post text.
     * @param array  $options Additional platform-specific options (e.g. 'subreddit', 'title').
     */
    public function reddit(string $message, array $options = []): PublishResult
    {
        $post = new Post(
            title: $options['title'] ?? '',
            body: $message,
            url: $options['url'] ?? null,
        );

        return $this->publisher->publish($post, 'reddit', $options);
    }

    /**
     * Publish a message to Discord.
     *
     * @param string     $message The message text.
     * @param array|null $media   Optional media: ['path' => ..., 'mime_type' => ...].
     * @param array      $options Additional platform-specific options.
     */
    public function discord(string $message, ?array $media = null, array $options = []): PublishResult
    {
        $mediaCollection = $this->buildMediaCollection($media);

        $post = new Post(
            title: '',
            body: $message,
            media: $mediaCollection,
        );

        return $this->publisher->publish($post, 'discord', $options);
    }

    /**
     * Publish a message to Slack.
     *
     * @param string $message The message text.
     * @param array  $options Additional platform-specific options.
     */
    public function slack(string $message, array $options = []): PublishResult
    {
        $post = new Post(
            title: '',
            body: $message,
        );

        return $this->publisher->publish($post, 'slack', $options);
    }

    /**
     * Publish content to Instagram.
     *
     * @param string     $message The caption text.
     * @param array|null $media   Media: ['path' => ..., 'mime_type' => ...] (required for Instagram).
     * @param array      $options Additional platform-specific options.
     */
    public function instagram(string $message, ?array $media = null, array $options = []): PublishResult
    {
        $mediaCollection = $this->buildMediaCollection($media);

        $post = new Post(
            title: '',
            body: $message,
            media: $mediaCollection,
        );

        return $this->publisher->publish($post, 'instagram', $options);
    }

    /**
     * Publish a pin to Pinterest.
     *
     * @param string $message The pin description.
     * @param array  $data    Pin data: ['image' => '...', 'link' => '...', 'title' => '...'].
     * @param array  $options Additional platform-specific options.
     */
    public function pinterest(string $message, array $data = [], array $options = []): PublishResult
    {
        $media = null;
        if (isset($data['image'])) {
            $media = new MediaCollection([
                new Media($data['image'], 'image/jpeg'),
            ]);
        }

        $post = new Post(
            title: $data['title'] ?? '',
            body: $message,
            url: $data['link'] ?? null,
            media: $media,
        );

        return $this->publisher->publish($post, 'pinterest', $options);
    }

    /**
     * Publish a message to WhatsApp.
     *
     * @param string $message The message text.
     * @param array  $options Additional platform-specific options (e.g. 'to' for recipient).
     */
    public function whatsapp(string $message, array $options = []): PublishResult
    {
        $post = new Post(
            title: '',
            body: $message,
        );

        return $this->publisher->publish($post, 'whatsapp', $options);
    }

    /**
     * Publish a post to Tumblr.
     *
     * @param string     $message The post body.
     * @param array|null $media   Optional media: ['path' => ..., 'mime_type' => ...].
     * @param array      $options Additional platform-specific options.
     */
    public function tumblr(string $message, ?array $media = null, array $options = []): PublishResult
    {
        $mediaCollection = $this->buildMediaCollection($media);

        $post = new Post(
            title: $options['title'] ?? '',
            body: $message,
            media: $mediaCollection,
        );

        return $this->publisher->publish($post, 'tumblr', $options);
    }

    // ── Publish Directly ─────────────────────────────────────────────────

    /**
     * Publish a Post directly to a specific platform.
     */
    public function publish(Post $post, string $platform, array $options = []): PublishResult
    {
        try {
            return $this->publisher->publish($post, $platform, $options);
        } catch (\Throwable $e) {
            return new PublishResult(
                success: false,
                platformName: $platform,
                error: $e->getMessage(),
            );
        }
    }

    /**
     * Publish a Post to all configured platforms.
     *
     * @return array<string, PublishResult>
     */
    public function toAll(Post $post, array $options = []): array
    {
        $results = [];

        foreach ($this->registry->names() as $platformName) {
            try {
                $results[$platformName] = $this->publisher->publish($post, $platformName, $options);
            } catch (\Throwable $e) {
                $results[$platformName] = new PublishResult(
                    success: false,
                    platformName: $platformName,
                    error: $e->getMessage(),
                );
            }
        }

        return $results;
    }

    // ── Private helpers ──────────────────────────────────────────────────

    /**
     * Append a signature to the message text.
     */
    private function assignSignature(string $text, string $type, string $signature): string
    {
        if ($signature === '') {
            return $text;
        }

        $maxLength = $type === 'caption'
            ? self::TELEGRAM_CAPTION_LENGTH
            : self::TELEGRAM_TEXT_LENGTH;

        $suffix = "\n\n" . $signature;

        if (mb_strlen($text . $suffix) > $maxLength) {
            $text = mb_substr($text, 0, $maxLength - mb_strlen($suffix));
        }

        return $text . $suffix;
    }

    /**
     * Handle extended Telegram types (location, venue, contact, voice)
     * by calling TelegramPlatform methods directly.
     */
    private function handleTelegramExtended(
        string $type,
        string $message,
        array $attachment,
        ?array $inlineKeyboard,
        array $options,
    ): PublishResult {
        $credentials = $this->config->credentials('telegram');
        $chatId = $options['chat_id'] ?? $credentials?->get('channel_username');

        /** @var TelegramPlatform $telegram */
        $telegram = $this->registry->get('telegram');

        $extOptions = [];
        if ($inlineKeyboard !== null) {
            $extOptions['inline_keyboard'] = $inlineKeyboard;
        }
        if (!empty($options['disable_notification'])) {
            $extOptions['disable_notification'] = true;
        }

        try {
            $response = match ($type) {
                'location' => $telegram->sendLocation(
                    $chatId,
                    (float) $attachment['latitude'],
                    (float) $attachment['longitude'],
                    array_merge($extOptions, array_filter([
                        'live_period' => $attachment['live_period'] ?? null,
                    ])),
                ),
                'venue' => $telegram->sendVenue(
                    $chatId,
                    (float) $attachment['latitude'],
                    (float) $attachment['longitude'],
                    $attachment['title'],
                    $attachment['address'],
                    $extOptions,
                ),
                'contact' => $telegram->sendContact(
                    $chatId,
                    $attachment['phone_number'],
                    $attachment['first_name'],
                    array_merge($extOptions, array_filter([
                        'last_name' => $attachment['last_name'] ?? null,
                    ])),
                ),
                'voice' => $telegram->sendVoice(
                    $chatId,
                    $attachment['file'],
                    array_merge($extOptions, array_filter([
                        'caption' => $message !== '' ? $message : null,
                        'duration' => $attachment['duration'] ?? null,
                    ])),
                ),
                default => throw new \InvalidArgumentException("Unknown Telegram type: {$type}"),
            };

            // Extended methods return raw API response arrays
            $messageId = $response['result']['message_id'] ?? null;

            return new PublishResult(
                success: true,
                platformName: 'telegram',
                externalId: $messageId !== null ? (string) $messageId : null,
            );
        } catch (\Throwable $e) {
            return new PublishResult(
                success: false,
                platformName: 'telegram',
                error: $e->getMessage(),
            );
        }
    }

    /**
     * Build a MediaCollection from a single attachment array.
     */
    private function buildMediaFromAttachment(?array $attachment): ?MediaCollection
    {
        if ($attachment === null) {
            return null;
        }

        $type = $attachment['type'] ?? '';

        // Handle media group
        if ($type === 'media_group' && isset($attachment['files'])) {
            return $this->buildMediaGroup($attachment['files']);
        }

        // Single media
        $file = $attachment['file'] ?? null;
        if ($file === null) {
            return null;
        }

        $mimeType = match ($type) {
            'photo' => 'image/jpeg',
            'video' => 'video/mp4',
            'audio' => 'audio/mpeg',
            'document' => 'application/octet-stream',
            default => 'application/octet-stream',
        };

        return new MediaCollection([
            new Media(
                path: $file,
                mimeType: $mimeType,
                duration: $attachment['duration'] ?? null,
                width: $attachment['width'] ?? null,
                height: $attachment['height'] ?? null,
            ),
        ]);
    }

    /**
     * Build a MediaCollection from a media_group files array.
     */
    private function buildMediaGroup(array $files): MediaCollection
    {
        $items = [];

        foreach ($files as $file) {
            $type = $file['type'] ?? 'photo';
            $mimeType = $type === 'video' ? 'video/mp4' : 'image/jpeg';

            $items[] = new Media(
                path: $file['media'],
                mimeType: $mimeType,
            );
        }

        return new MediaCollection($items);
    }

    /**
     * Build a MediaCollection from a Twitter-style media array.
     */
    private function buildMediaCollection(?array $media): ?MediaCollection
    {
        if ($media === null) {
            return null;
        }

        // Single media item: ['path' => ..., 'mime_type' => ...]
        if (isset($media['path'])) {
            return new MediaCollection([
                new Media($media['path'], $media['mime_type'] ?? 'image/jpeg'),
            ]);
        }

        // Multiple media items
        $items = [];
        foreach ($media as $item) {
            if (isset($item['path'])) {
                $items[] = new Media($item['path'], $item['mime_type'] ?? 'image/jpeg');
            }
        }

        return !empty($items) ? new MediaCollection($items) : null;
    }
}
