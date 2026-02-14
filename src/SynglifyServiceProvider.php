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
            if ($config->hasPlatform('reddit')) {
                $registry->register($app->make(\Synglify\Core\Platforms\Reddit\RedditPlatform::class));
            }
            if ($config->hasPlatform('discord')) {
                $registry->register($app->make(\Synglify\Core\Platforms\Discord\DiscordPlatform::class));
            }
            if ($config->hasPlatform('slack')) {
                $registry->register($app->make(\Synglify\Core\Platforms\Slack\SlackPlatform::class));
            }
            if ($config->hasPlatform('instagram')) {
                $registry->register($app->make(\Synglify\Core\Platforms\Instagram\InstagramPlatform::class));
            }
            if ($config->hasPlatform('pinterest')) {
                $registry->register($app->make(\Synglify\Core\Platforms\Pinterest\PinterestPlatform::class));
            }
            if ($config->hasPlatform('whatsapp')) {
                $registry->register($app->make(\Synglify\Core\Platforms\WhatsApp\WhatsAppPlatform::class));
            }
            if ($config->hasPlatform('tumblr')) {
                $registry->register($app->make(\Synglify\Core\Platforms\Tumblr\TumblrPlatform::class));
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
        $this->app->singleton(\Synglify\Core\Platforms\Reddit\RedditPlatform::class, function ($app) {
            $config = $app->make(SynglifyConfig::class);
            return new \Synglify\Core\Platforms\Reddit\RedditPlatform(
                credentials: $config->credentials('reddit'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(\Synglify\Core\Platforms\Reddit\RedditFormatter::class),
            );
        });
        $this->app->singleton(\Synglify\Core\Platforms\Discord\DiscordPlatform::class, function ($app) {
            $config = $app->make(SynglifyConfig::class);
            return new \Synglify\Core\Platforms\Discord\DiscordPlatform(
                credentials: $config->credentials('discord'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(\Synglify\Core\Platforms\Discord\DiscordFormatter::class),
            );
        });
        $this->app->singleton(\Synglify\Core\Platforms\Slack\SlackPlatform::class, function ($app) {
            $config = $app->make(SynglifyConfig::class);
            return new \Synglify\Core\Platforms\Slack\SlackPlatform(
                credentials: $config->credentials('slack'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(\Synglify\Core\Platforms\Slack\SlackFormatter::class),
            );
        });
        $this->app->singleton(\Synglify\Core\Platforms\Instagram\InstagramPlatform::class, function ($app) {
            $config = $app->make(SynglifyConfig::class);
            return new \Synglify\Core\Platforms\Instagram\InstagramPlatform(
                credentials: $config->credentials('instagram'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(\Synglify\Core\Platforms\Instagram\InstagramFormatter::class),
            );
        });
        $this->app->singleton(\Synglify\Core\Platforms\Pinterest\PinterestPlatform::class, function ($app) {
            $config = $app->make(SynglifyConfig::class);
            return new \Synglify\Core\Platforms\Pinterest\PinterestPlatform(
                credentials: $config->credentials('pinterest'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(\Synglify\Core\Platforms\Pinterest\PinterestFormatter::class),
            );
        });
        $this->app->singleton(\Synglify\Core\Platforms\WhatsApp\WhatsAppPlatform::class, function ($app) {
            $config = $app->make(SynglifyConfig::class);
            return new \Synglify\Core\Platforms\WhatsApp\WhatsAppPlatform(
                credentials: $config->credentials('whatsapp'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(\Synglify\Core\Platforms\WhatsApp\WhatsAppFormatter::class),
            );
        });
        $this->app->singleton(\Synglify\Core\Platforms\Tumblr\TumblrPlatform::class, function ($app) {
            $config = $app->make(SynglifyConfig::class);
            return new \Synglify\Core\Platforms\Tumblr\TumblrPlatform(
                credentials: $config->credentials('tumblr'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(\Synglify\Core\Platforms\Tumblr\TumblrFormatter::class),
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
            'reddit' => ['client_id', 'client_secret', 'access_token', 'username'],
            'discord' => ['bot_token', 'channel_id'],
            'slack' => ['bot_token', 'channel'],
            'instagram' => ['access_token', 'instagram_account_id'],
            'pinterest' => ['access_token', 'board_id'],
            'whatsapp' => ['access_token', 'phone_number_id'],
            'tumblr' => ['access_token', 'blog_identifier'],
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
