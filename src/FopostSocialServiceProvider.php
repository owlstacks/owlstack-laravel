<?php

declare(strict_types=1);

namespace Fopost\Social\Laravel;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Fopost\Social\Config\PlatformCredentials;
use Fopost\Social\Config\FopostConfig;
use Fopost\Social\Formatting\CharacterTruncator;
use Fopost\Social\Formatting\HashtagExtractor;
use Fopost\Social\Http\Contracts\HttpClientInterface;
use Fopost\Social\Http\HttpClient;
use Fopost\Social\Platforms\Facebook\FacebookFormatter;
use Fopost\Social\Platforms\Facebook\FacebookPlatform;
use Fopost\Social\Platforms\PlatformRegistry;
use Fopost\Social\Platforms\Telegram\TelegramFormatter;
use Fopost\Social\Platforms\Telegram\TelegramPlatform;
use Fopost\Social\Platforms\Twitter\TwitterFormatter;
use Fopost\Social\Platforms\Twitter\TwitterPlatform;
use Fopost\Social\Publishing\Publisher;
use Fopost\Social\Laravel\Events\LaravelEventDispatcher;

class FopostSocialServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/fopost-social.php',
            'fopost-social',
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
                __DIR__ . '/../config/fopost-social.php' => config_path('fopost.php'),
            ], 'fopost-config');
        }
    }

    // ── Service registration ─────────────────────────────────────────────

    private function registerConfig(): void
    {
        $this->app->singleton(FopostConfig::class, function ($app) {
            $platforms = $app['config']->get('fopost.platforms', []);

            // Filter out platforms with empty credentials
            $configured = [];
            foreach ($platforms as $name => $credentials) {
                if ($this->hasRequiredCredentials($name, $credentials)) {
                    $configured[$name] = $credentials;
                }
            }

            return new FopostConfig(
                platforms: $configured,
                options: [
                    'proxy' => $app['config']->get('fopost.proxy', []),
                ],
            );
        });
    }

    private function registerHttpClient(): void
    {
        $this->app->singleton(HttpClientInterface::class, function ($app) {
            $proxyConfig = $app['config']->get('fopost.proxy', []);

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
            $config = $app->make(FopostConfig::class);

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
                $registry->register($app->make(\Fopost\Social\Platforms\Reddit\RedditPlatform::class));
            }
            if ($config->hasPlatform('discord')) {
                $registry->register($app->make(\Fopost\Social\Platforms\Discord\DiscordPlatform::class));
            }
            if ($config->hasPlatform('slack')) {
                $registry->register($app->make(\Fopost\Social\Platforms\Slack\SlackPlatform::class));
            }
            if ($config->hasPlatform('instagram')) {
                $registry->register($app->make(\Fopost\Social\Platforms\Instagram\InstagramPlatform::class));
            }
            if ($config->hasPlatform('pinterest')) {
                $registry->register($app->make(\Fopost\Social\Platforms\Pinterest\PinterestPlatform::class));
            }
            if ($config->hasPlatform('whatsapp')) {
                $registry->register($app->make(\Fopost\Social\Platforms\WhatsApp\WhatsAppPlatform::class));
            }
            if ($config->hasPlatform('tumblr')) {
                $registry->register($app->make(\Fopost\Social\Platforms\Tumblr\TumblrPlatform::class));
            }
            if ($config->hasPlatform('linkedin')) {
                $registry->register($app->make(\Fopost\Social\Platforms\LinkedIn\LinkedInPlatform::class));
            }

            return $registry;
        });

        // Register individual platform classes
        $this->app->singleton(TelegramPlatform::class, function ($app) {
            $config = $app->make(FopostConfig::class);
            return new TelegramPlatform(
                credentials: $config->credentials('telegram'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(TelegramFormatter::class),
            );
        });
        $this->app->singleton(TwitterPlatform::class, function ($app) {
            $config = $app->make(FopostConfig::class);
            return new TwitterPlatform(
                credentials: $config->credentials('twitter'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(TwitterFormatter::class),
            );
        });
        $this->app->singleton(FacebookPlatform::class, function ($app) {
            $config = $app->make(FopostConfig::class);
            $graphVersion = $config->credentials('facebook')?->get('default_graph_version', 'v21.0') ?? 'v21.0';
            return new FacebookPlatform(
                credentials: $config->credentials('facebook'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(FacebookFormatter::class),
                graphVersion: $graphVersion,
            );
        });
        $this->app->singleton(\Fopost\Social\Platforms\Reddit\RedditPlatform::class, function ($app) {
            $config = $app->make(FopostConfig::class);
            return new \Fopost\Social\Platforms\Reddit\RedditPlatform(
                credentials: $config->credentials('reddit'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(\Fopost\Social\Platforms\Reddit\RedditFormatter::class),
            );
        });
        $this->app->singleton(\Fopost\Social\Platforms\Discord\DiscordPlatform::class, function ($app) {
            $config = $app->make(FopostConfig::class);
            return new \Fopost\Social\Platforms\Discord\DiscordPlatform(
                credentials: $config->credentials('discord'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(\Fopost\Social\Platforms\Discord\DiscordFormatter::class),
            );
        });
        $this->app->singleton(\Fopost\Social\Platforms\Slack\SlackPlatform::class, function ($app) {
            $config = $app->make(FopostConfig::class);
            return new \Fopost\Social\Platforms\Slack\SlackPlatform(
                credentials: $config->credentials('slack'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(\Fopost\Social\Platforms\Slack\SlackFormatter::class),
            );
        });
        $this->app->singleton(\Fopost\Social\Platforms\Instagram\InstagramPlatform::class, function ($app) {
            $config = $app->make(FopostConfig::class);
            return new \Fopost\Social\Platforms\Instagram\InstagramPlatform(
                credentials: $config->credentials('instagram'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(\Fopost\Social\Platforms\Instagram\InstagramFormatter::class),
            );
        });
        $this->app->singleton(\Fopost\Social\Platforms\Pinterest\PinterestPlatform::class, function ($app) {
            $config = $app->make(FopostConfig::class);
            return new \Fopost\Social\Platforms\Pinterest\PinterestPlatform(
                credentials: $config->credentials('pinterest'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(\Fopost\Social\Platforms\Pinterest\PinterestFormatter::class),
            );
        });
        $this->app->singleton(\Fopost\Social\Platforms\WhatsApp\WhatsAppPlatform::class, function ($app) {
            $config = $app->make(FopostConfig::class);
            return new \Fopost\Social\Platforms\WhatsApp\WhatsAppPlatform(
                credentials: $config->credentials('whatsapp'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(\Fopost\Social\Platforms\WhatsApp\WhatsAppFormatter::class),
            );
        });
        $this->app->singleton(\Fopost\Social\Platforms\Tumblr\TumblrPlatform::class, function ($app) {
            $config = $app->make(FopostConfig::class);
            return new \Fopost\Social\Platforms\Tumblr\TumblrPlatform(
                credentials: $config->credentials('tumblr'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(\Fopost\Social\Platforms\Tumblr\TumblrFormatter::class),
            );
        });
        $this->app->singleton(\Fopost\Social\Platforms\LinkedIn\LinkedInPlatform::class, function ($app) {
            $config = $app->make(FopostConfig::class);
            return new \Fopost\Social\Platforms\LinkedIn\LinkedInPlatform(
                credentials: $config->credentials('linkedin'),
                httpClient: $app->make(HttpClientInterface::class),
                formatter: $app->make(\Fopost\Social\Platforms\LinkedIn\LinkedInFormatter::class),
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
        $this->app->singleton('fopost-social', function ($app) {
            return new SendTo(
                publisher: $app->make(Publisher::class),
                config: $app->make(FopostConfig::class),
                registry: $app->make(PlatformRegistry::class),
            );
        });

        $this->app->alias('fopost-social', SendTo::class);
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
            'linkedin' => ['access_token'],
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
