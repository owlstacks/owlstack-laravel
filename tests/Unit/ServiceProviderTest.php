<?php

declare(strict_types=1);

namespace Fopost\Social\Laravel\Tests\Unit;

use Fopost\Social\Config\FopostConfig;
use Fopost\Social\Http\Contracts\HttpClientInterface;
use Fopost\Social\Platforms\PlatformRegistry;
use Fopost\Social\Platforms\Telegram\TelegramPlatform;
use Fopost\Social\Platforms\Twitter\TwitterPlatform;
use Fopost\Social\Platforms\Facebook\FacebookPlatform;
use Fopost\Social\Publishing\Publisher;
use Fopost\Social\Laravel\Events\LaravelEventDispatcher;
use Fopost\Social\Laravel\SendTo;
use Fopost\Social\Laravel\FopostSocialServiceProvider;
use Fopost\Social\Laravel\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function testServiceProviderIsRegistered(): void
    {
        $this->assertInstanceOf(
            FopostSocialServiceProvider::class,
            $this->app->getProvider(FopostSocialServiceProvider::class),
        );
    }

    public function testFopostConfigIsBound(): void
    {
        $config = $this->app->make(FopostConfig::class);
        $this->assertInstanceOf(FopostConfig::class, $config);
    }

    public function testConfigHasAllPlatforms(): void
    {
        $config = $this->app->make(FopostConfig::class);

        $this->assertTrue($config->hasPlatform('telegram'));
        $this->assertTrue($config->hasPlatform('twitter'));
        $this->assertTrue($config->hasPlatform('facebook'));
    }

    public function testConfigFiltersPlatformsWithEmptyCredentials(): void
    {
        // Override twitter config with empty credentials
        $this->app['config']->set('fopost.platforms.twitter', [
            'consumer_key' => '',
            'consumer_secret' => '',
            'access_token' => '',
            'access_token_secret' => '',
        ]);

        // Re-register so the singleton is rebuilt
        $this->app->forgetInstance(FopostConfig::class);
        (new FopostSocialServiceProvider($this->app))->register();

        $config = $this->app->make(FopostConfig::class);
        $this->assertFalse($config->hasPlatform('twitter'));
        $this->assertTrue($config->hasPlatform('telegram'));
    }

    public function testHttpClientIsBound(): void
    {
        $client = $this->app->make(HttpClientInterface::class);
        // Our TestCase replaces it with a mock, so it's a mock instance
        $this->assertInstanceOf(HttpClientInterface::class, $client);
    }

    public function testPlatformRegistryIsBound(): void
    {
        $registry = $this->app->make(PlatformRegistry::class);
        $this->assertInstanceOf(PlatformRegistry::class, $registry);
    }

    public function testAllPlatformsRegistered(): void
    {
        $registry = $this->app->make(PlatformRegistry::class);

        $this->assertTrue($registry->has('telegram'));
        $this->assertTrue($registry->has('twitter'));
        $this->assertTrue($registry->has('facebook'));
        $this->assertTrue($registry->has('linkedin'));
        $this->assertCount(4, $registry->names());
    }

    public function testTelegramPlatformIsBound(): void
    {
        $telegram = $this->app->make(TelegramPlatform::class);
        $this->assertInstanceOf(TelegramPlatform::class, $telegram);
        $this->assertSame('telegram', $telegram->name());
    }

    public function testTwitterPlatformIsBound(): void
    {
        $twitter = $this->app->make(TwitterPlatform::class);
        $this->assertInstanceOf(TwitterPlatform::class, $twitter);
        $this->assertSame('twitter', $twitter->name());
    }

    public function testFacebookPlatformIsBound(): void
    {
        $facebook = $this->app->make(FacebookPlatform::class);
        $this->assertInstanceOf(FacebookPlatform::class, $facebook);
        $this->assertSame('facebook', $facebook->name());
    }

    public function testPublisherIsBound(): void
    {
        $publisher = $this->app->make(Publisher::class);
        $this->assertInstanceOf(Publisher::class, $publisher);
    }

    public function testEventDispatcherIsBound(): void
    {
        $dispatcher = $this->app->make(LaravelEventDispatcher::class);
        $this->assertInstanceOf(LaravelEventDispatcher::class, $dispatcher);
    }

    public function testSendToIsBound(): void
    {
        $sendTo = $this->app->make('fopost-social');
        $this->assertInstanceOf(SendTo::class, $sendTo);
    }

    public function testSendToIsAlsoBoundByClass(): void
    {
        $sendTo = $this->app->make(SendTo::class);
        $this->assertInstanceOf(SendTo::class, $sendTo);
    }

    public function testConfigPublishing(): void
    {
        $this->artisan('vendor:publish', ['--tag' => 'fopost-config', '--force' => true]);

        // The config file should have been published
        $this->assertFileExists(config_path('fopost.php'));

        // Clean up
        @unlink(config_path('fopost.php'));
    }
}
