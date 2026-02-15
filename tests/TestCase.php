<?php

declare(strict_types=1);

namespace Owlstack\Laravel\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Owlstack\Core\Http\Contracts\HttpClientInterface;
use Owlstack\Laravel\OwlstackServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected HttpClientInterface $httpClient;

    protected function setUp(): void
    {
        parent::setUp();

        // Replace the real HttpClient with a mock
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->app->instance(HttpClientInterface::class, $this->httpClient);
    }

    protected function getPackageProviders($app): array
    {
        return [
            OwlstackServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Configure all platforms with test credentials
        $app['config']->set('owlstack.platforms.telegram', [
            'api_token' => 'test-token-123',
            'bot_username' => 'test_bot',
            'channel_username' => '@test_channel',
            'channel_signature' => '',
            'parse_mode' => 'HTML',
        ]);

        $app['config']->set('owlstack.platforms.twitter', [
            'consumer_key' => 'test_consumer_key',
            'consumer_secret' => 'test_consumer_secret',
            'access_token' => 'test_access_token',
            'access_token_secret' => 'test_access_token_secret',
        ]);

        $app['config']->set('owlstack.platforms.facebook', [
            'app_id' => 'test_app_id',
            'app_secret' => 'test_app_secret',
            'page_access_token' => 'test_page_token',
            'page_id' => '123456789',
            'default_graph_version' => 'v21.0',
        ]);

        $app['config']->set('owlstack.platforms.linkedin', [
            'access_token' => 'test_linkedin_token',
            'person_id' => 'test_person_id',
            'organization_id' => '',
        ]);
    }

    /**
     * Helper: build a successful Telegram API response body.
     */
    protected function telegramSuccess(array $result = ['message_id' => 123]): array
    {
        return [
            'status' => 200,
            'headers' => [],
            'body' => json_encode(['ok' => true, 'result' => $result]),
        ];
    }

    /**
     * Helper: build a successful Twitter API response body.
     */
    protected function twitterSuccess(string $id = '1234567890'): array
    {
        return [
            'status' => 201,
            'headers' => [],
            'body' => json_encode([
                'data' => ['id' => $id, 'text' => 'test'],
            ]),
        ];
    }

    /**
     * Helper: build a successful Facebook API response body.
     */
    protected function facebookSuccess(string $id = '123456789_987654321'): array
    {
        return [
            'status' => 200,
            'headers' => [],
            'body' => json_encode(['id' => $id]),
        ];
    }

    /**
     * Helper: build a successful LinkedIn API response body.
     */
    protected function linkedinSuccess(string $id = 'urn:li:share:123456789'): array
    {
        return [
            'status' => 201,
            'headers' => ['x-restli-id' => $id],
            'body' => json_encode(['id' => $id]),
        ];
    }
}
