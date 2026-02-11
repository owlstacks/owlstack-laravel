<?php

declare(strict_types=1);

namespace Synglify\Laravel\Tests\Unit;

use Synglify\Laravel\SendTo;
use Synglify\Laravel\Tests\TestCase;

class SendToTest extends TestCase
{
    // ── Telegram ─────────────────────────────────────────────────────────

    public function testTelegramTextMessage(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('test-token-123/sendMessage'),
                $this->callback(function (array $options) {
                    $p = $options['form_params'];
                    return $p['chat_id'] === '@test_channel'
                        && str_contains($p['text'], 'Hello Telegram');
                }),
            )
            ->willReturn($this->telegramSuccess());

        /** @var SendTo $sendTo */
        $sendTo = $this->app->make(SendTo::class);
        $result = $sendTo->telegram('Hello Telegram');

        $this->assertTrue($result->success);
        $this->assertSame('telegram', $result->platformName);
        $this->assertSame('123', $result->externalId);
    }

    public function testTelegramWithPhotoAttachment(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('sendPhoto'),
                $this->callback(function (array $options) {
                    return isset($options['form_params']['photo'])
                        && isset($options['form_params']['caption']);
                }),
            )
            ->willReturn($this->telegramSuccess(['message_id' => 456]));

        /** @var SendTo $sendTo */
        $sendTo = $this->app->make(SendTo::class);
        $result = $sendTo->telegram('Photo caption', [
            'type' => 'photo',
            'file' => '/path/to/image.jpg',
        ]);

        $this->assertTrue($result->success);
        $this->assertSame('456', $result->externalId);
    }

    public function testTelegramWithVideoAttachment(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('sendVideo'),
                $this->callback(function (array $options) {
                    return isset($options['form_params']['video']);
                }),
            )
            ->willReturn($this->telegramSuccess(['message_id' => 789]));

        /** @var SendTo $sendTo */
        $sendTo = $this->app->make(SendTo::class);
        $result = $sendTo->telegram('Video caption', [
            'type' => 'video',
            'file' => '/path/to/video.mp4',
        ]);

        $this->assertTrue($result->success);
    }

    public function testTelegramWithInlineKeyboard(): void
    {
        $keyboard = [[['text' => 'Visit', 'url' => 'https://example.com']]];

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('sendMessage'),
                $this->callback(function (array $options) {
                    return isset($options['form_params']['reply_markup']);
                }),
            )
            ->willReturn($this->telegramSuccess());

        /** @var SendTo $sendTo */
        $sendTo = $this->app->make(SendTo::class);
        $result = $sendTo->telegram('With keyboard', null, $keyboard);

        $this->assertTrue($result->success);
    }

    public function testTelegramWithSignature(): void
    {
        // Enable signature in config
        $this->app['config']->set('synglify.platforms.telegram.channel_signature', '— Test Bot');

        // Re-register to pick up new config
        $this->app->forgetInstance(\Synglify\Core\Config\SynglifyConfig::class);
        $this->app->forgetInstance(\Synglify\Core\Platforms\PlatformRegistry::class);
        $this->app->forgetInstance(\Synglify\Core\Platforms\Telegram\TelegramPlatform::class);
        $this->app->forgetInstance(\Synglify\Core\Publishing\Publisher::class);
        $this->app->forgetInstance(SendTo::class);
        $this->app->forgetInstance('synglify');
        (new \Synglify\Laravel\SynglifyServiceProvider($this->app))->register();

        // Re-bind the mock HTTP client after re-registration
        $this->app->instance(\Synglify\Core\Http\Contracts\HttpClientInterface::class, $this->httpClient);

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->anything(),
                $this->callback(function (array $options) {
                    $text = $options['form_params']['text'] ?? '';
                    return str_contains($text, '— Test Bot');
                }),
            )
            ->willReturn($this->telegramSuccess());

        /** @var SendTo $sendTo */
        $sendTo = $this->app->make(SendTo::class);
        $result = $sendTo->telegram('Hello with signature');

        $this->assertTrue($result->success);
    }

    public function testTelegramLocation(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('sendLocation'),
                $this->callback(function (array $options) {
                    $p = $options['form_params'];
                    return $p['latitude'] === 51.5074
                        && $p['longitude'] === -0.1278;
                }),
            )
            ->willReturn($this->telegramSuccess(['message_id' => 300]));

        /** @var SendTo $sendTo */
        $sendTo = $this->app->make(SendTo::class);
        $result = $sendTo->telegram('', [
            'type' => 'location',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
        ]);

        $this->assertTrue($result->success);
        $this->assertSame('300', $result->externalId);
    }

    public function testTelegramVenue(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('sendVenue'),
                $this->callback(function (array $options) {
                    $p = $options['form_params'];
                    return $p['title'] === 'Coffee Shop'
                        && $p['address'] === '123 Main St';
                }),
            )
            ->willReturn($this->telegramSuccess(['message_id' => 301]));

        /** @var SendTo $sendTo */
        $sendTo = $this->app->make(SendTo::class);
        $result = $sendTo->telegram('', [
            'type' => 'venue',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
            'title' => 'Coffee Shop',
            'address' => '123 Main St',
        ]);

        $this->assertTrue($result->success);
    }

    public function testTelegramContact(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('sendContact'),
                $this->callback(function (array $options) {
                    $p = $options['form_params'];
                    return $p['phone_number'] === '+1234567890'
                        && $p['first_name'] === 'John';
                }),
            )
            ->willReturn($this->telegramSuccess(['message_id' => 302]));

        /** @var SendTo $sendTo */
        $sendTo = $this->app->make(SendTo::class);
        $result = $sendTo->telegram('', [
            'type' => 'contact',
            'phone_number' => '+1234567890',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertTrue($result->success);
    }

    // ── Twitter / X ──────────────────────────────────────────────────────

    public function testTwitterTextMessage(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('api.x.com/2/tweets'),
                $this->callback(function (array $options) {
                    $body = $options['json'] ?? [];
                    return str_contains($body['text'] ?? '', 'Hello Twitter');
                }),
            )
            ->willReturn($this->twitterSuccess());

        /** @var SendTo $sendTo */
        $sendTo = $this->app->make(SendTo::class);
        $result = $sendTo->twitter('Hello Twitter');

        $this->assertTrue($result->success);
        $this->assertSame('twitter', $result->platformName);
        $this->assertSame('1234567890', $result->externalId);
    }

    public function testXIsAliasForTwitter(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('api.x.com'),
                $this->anything(),
            )
            ->willReturn($this->twitterSuccess());

        /** @var SendTo $sendTo */
        $sendTo = $this->app->make(SendTo::class);
        $result = $sendTo->x('Hello X');

        $this->assertTrue($result->success);
        $this->assertSame('twitter', $result->platformName);
    }

    // ── Facebook ─────────────────────────────────────────────────────────

    public function testFacebookLinkPost(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('graph.facebook.com'),
                $this->callback(function (array $options) {
                    $p = $options['form_params'] ?? [];
                    return isset($p['message']) && isset($p['link']);
                }),
            )
            ->willReturn($this->facebookSuccess());

        /** @var SendTo $sendTo */
        $sendTo = $this->app->make(SendTo::class);
        $result = $sendTo->facebook('Check this out!', 'link', [
            'link' => 'https://example.com/article',
        ]);

        $this->assertTrue($result->success);
        $this->assertSame('facebook', $result->platformName);
    }

    // ── Generic ──────────────────────────────────────────────────────────

    public function testPublishDirectly(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn($this->telegramSuccess());

        $post = new \Synglify\Core\Content\Post(
            title: 'Direct Post',
            body: 'Published via Post object',
        );

        /** @var SendTo $sendTo */
        $sendTo = $this->app->make(SendTo::class);
        $result = $sendTo->publish($post, 'telegram');

        $this->assertTrue($result->success);
    }

    public function testToAllPublishesToAllPlatforms(): void
    {
        // Expect 3 calls — one per platform
        $this->httpClient
            ->expects($this->exactly(3))
            ->method('post')
            ->willReturnOnConsecutiveCalls(
                $this->telegramSuccess(),
                $this->twitterSuccess(),
                $this->facebookSuccess(),
            );

        $post = new \Synglify\Core\Content\Post(
            title: 'Cross-platform',
            body: 'Post to all platforms',
        );

        /** @var SendTo $sendTo */
        $sendTo = $this->app->make(SendTo::class);
        $results = $sendTo->toAll($post);

        $this->assertCount(3, $results);
        $this->assertArrayHasKey('telegram', $results);
        $this->assertArrayHasKey('twitter', $results);
        $this->assertArrayHasKey('facebook', $results);
    }

    public function testTelegramFailureReturnsFailedResult(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn([
                'status' => 400,
                'headers' => [],
                'body' => json_encode([
                    'ok' => false,
                    'description' => 'Bad Request: chat not found',
                    'error_code' => 400,
                ]),
            ]);

        /** @var SendTo $sendTo */
        $sendTo = $this->app->make(SendTo::class);
        $result = $sendTo->telegram('This will fail');

        $this->assertFalse($result->success);
        $this->assertTrue($result->failed());
        $this->assertNotNull($result->error);
    }
}
