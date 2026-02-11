<?php

declare(strict_types=1);

namespace Synglify\Laravel;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Synglify\Core\Config\PlatformCredentials;
use Synglify\Core\Config\SynglifyConfig;
use Synglify\Core\Formatting\CharacterTruncator;
use Synglify\Core\Formatting\HashtagExtractor;
use Synglify\Core\Http\Contracts\HttpClientInterface;
use Synglify\Core\Http\HttpClient;
use Synglify\Core\Platforms\Facebook\FacebookFormatter;
use Synglify\Core\Platforms\Facebook\FacebookPlatform;
use Synglify\Core\Platforms\PlatformRegistry;
use Synglify\Core\Platforms\Telegram\TelegramFormatter;
use Synglify\Core\Platforms\Telegram\TelegramPlatform;
use Synglify\Core\Platforms\Twitter\TwitterFormatter;
use Synglify\Core\Platforms\Twitter\TwitterPlatform;
use Synglify\Core\Publishing\Publisher;
use Synglify\Laravel\Events\LaravelEventDispatcher;

class SynglifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/synglify.php',
            'synglify',
        );

        $this->registerConfig();
        $this->registerHttpClient();
        $this->registerFormatters();
        $this->registerPlatforms();
        $this->registerPublisher();
        $this->registerSendTo();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/synglify.php' => config_path('synglify.php'),
            ], 'synglify-config');
        }
    }

    // ── Service registration ─────────────────────────────────────────────

    private function registerConfig(): void
    {
        $this->app->singleton(SynglifyConfig::class, function ($app) {
            $platforms = $app['config']->get('synglify.platforms', []);

            // Filter out platforms with empty credentials
            $configured = [];
            foreach ($platforms as $name => $credentials) {
                if ($this->hasRequiredCredentials($name, $credentials)) {
                    $configured[$name] = $credentials;
                }
            }

            return new SynglifyConfig(
                platforms: $configured,
                options: [
                    'proxy' => $app['config']->get('synglify.proxy', []),
                ],
            );
        });
    }

    private function registerHttpClient(): void
    {
        $this->app->singleton(HttpClientInterface::class, function ($app) {
            $proxyConfig = $app['config']->get('synglify.proxy', []);

            $proxy = null;
            if (!empty($proxyConfig['hostname']) && !empty($proxyConfig['port'])) {
                $proxy = $proxyConfig;
            }

            return new HttpClient(proxy: $proxy);
        });
    }

    private function registerFormatters(): void
    {
        $this->app->singleton(HashtagExtractor::class);
        $this->app->singleton(CharacterTruncator::class);

        $this->app->singleton(TelegramFormatter::class, function ($app) {
            return new TelegramFormatter(
                $app->make(HashtagExtractor::class),
                $app->make(CharacterTruncator::class),
            );
        });

        $this->app->singleton(TwitterFormatter::class, function ($app) {
            return new TwitterFormatter(
                $app->make(HashtagExtractor::class),
                $app->make(CharacterTruncator::class),
            );
        });

        $this->app->singleton(FacebookFormatter::class, function ($app) {
            return new FacebookFormatter(
                $app->make(HashtagExtractor::class),
                $app->make(CharacterTruncator::class),
            );
        });
    }

    private function registerPlatforms(): void
    {
        $this->app->singleton(PlatformRegistry::class, function ($app) {
            $registry = new PlatformRegistry();
            $config = $app->make(SynglifyConfig::class);

            if ($config->hasPlatform('telegram')) {
                $registry->register($app->make(TelegramPlatform::class));
            }

            if ($config->hasPlatform('twitter')) {
                $registry->register($app->make(TwitterPlatform::class));
            }

            if ($config->hasPlatform('facebook')) {
                $registry->register($app->make(FacebookPlatform::class));
            }

            return $registry;
        });

        // Register individual platform classes
        $this->app->singleton(TelegramPlatform::class, function ($app) {
            $config = $app->make(SynglifyConfig::class);

            return new TelegramPlatform(
                credentials: $config->credentials('telegram'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(TelegramFormatter::class),
            );
        });

        $this->app->singleton(TwitterPlatform::class, function ($app) {
            $config = $app->make(SynglifyConfig::class);

            return new TwitterPlatform(
                credentials: $config->credentials('twitter'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(TwitterFormatter::class),
            );
        });

        $this->app->singleton(FacebookPlatform::class, function ($app) {
            $config = $app->make(SynglifyConfig::class);
            $graphVersion = $config->credentials('facebook')?->get('default_graph_version', 'v21.0') ?? 'v21.0';

            return new FacebookPlatform(
                credentials: $config->credentials('facebook'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(FacebookFormatter::class),
                graphVersion: $graphVersion,
            );
        });
    }

    private function registerPublisher(): void
    {
        $this->app->singleton(LaravelEventDispatcher::class, function ($app) {
            return new LaravelEventDispatcher($app->make(Dispatcher::class));
        });

        $this->app->singleton(Publisher::class, function ($app) {
            return new Publisher(
                platforms: $app->make(PlatformRegistry::class),
                eventDispatcher: $app->make(LaravelEventDispatcher::class),
            );
        });
    }

    private function registerSendTo(): void
    {
        $this->app->singleton('synglify', function ($app) {
            return new SendTo(
                publisher: $app->make(Publisher::class),
                config: $app->make(SynglifyConfig::class),
                registry: $app->make(PlatformRegistry::class),
            );
        });

        $this->app->alias('synglify', SendTo::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    /**
     * Determine if a platform has its minimum required credentials configured.
     */
    private function hasRequiredCredentials(string $platform, array $credentials): bool
    {
        $requiredKeys = match ($platform) {
            'telegram' => ['api_token'],
            'twitter' => ['consumer_key', 'consumer_secret', 'access_token', 'access_token_secret'],
            'facebook' => ['app_id', 'app_secret', 'page_access_token', 'page_id'],
            default => [],
        };

        foreach ($requiredKeys as $key) {
            if (empty($credentials[$key])) {
                return false;
            }
        }

        return true;
    }
}
