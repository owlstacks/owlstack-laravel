<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Platform Configurations
    |--------------------------------------------------------------------------
    */

    'platforms' => [

        'telegram' => [
            'api_token' => env('TELEGRAM_BOT_TOKEN', ''),
            'bot_username' => env('TELEGRAM_BOT_USERNAME', ''),
            'channel_username' => env('TELEGRAM_CHANNEL_USERNAME', ''),
            'channel_signature' => env('TELEGRAM_CHANNEL_SIGNATURE', ''),
            'parse_mode' => env('TELEGRAM_PARSE_MODE', 'HTML'),
        ],

        'twitter' => [
            'consumer_key' => env('TWITTER_CONSUMER_KEY', ''),
            'consumer_secret' => env('TWITTER_CONSUMER_SECRET', ''),
            'access_token' => env('TWITTER_ACCESS_TOKEN', ''),
            'access_token_secret' => env('TWITTER_ACCESS_TOKEN_SECRET', ''),
        ],

        'facebook' => [
            'app_id' => env('FACEBOOK_APP_ID', ''),
            'app_secret' => env('FACEBOOK_APP_SECRET', ''),
            'page_access_token' => env('FACEBOOK_PAGE_ACCESS_TOKEN', ''),
            'page_id' => env('FACEBOOK_PAGE_ID', ''),
            'default_graph_version' => env('FACEBOOK_GRAPH_VERSION', 'v21.0'),
        ],

        'linkedin' => [
            'access_token' => env('LINKEDIN_ACCESS_TOKEN', ''),
            'person_id' => env('LINKEDIN_PERSON_ID', ''),
            'organization_id' => env('LINKEDIN_ORGANIZATION_ID', ''),
        ],

        'reddit' => [
            'client_id' => env('REDDIT_CLIENT_ID', ''),
            'client_secret' => env('REDDIT_CLIENT_SECRET', ''),
            'access_token' => env('REDDIT_ACCESS_TOKEN', ''),
            'username' => env('REDDIT_USERNAME', ''),
        ],

        'discord' => [
            'bot_token' => env('DISCORD_BOT_TOKEN', ''),
            'channel_id' => env('DISCORD_CHANNEL_ID', ''),
            'webhook_url' => env('DISCORD_WEBHOOK_URL', ''),
        ],

        'slack' => [
            'bot_token' => env('SLACK_BOT_TOKEN', ''),
            'channel' => env('SLACK_CHANNEL', ''),
        ],

        'instagram' => [
            'access_token' => env('INSTAGRAM_ACCESS_TOKEN', ''),
            'instagram_account_id' => env('INSTAGRAM_ACCOUNT_ID', ''),
        ],

        'pinterest' => [
            'access_token' => env('PINTEREST_ACCESS_TOKEN', ''),
            'board_id' => env('PINTEREST_BOARD_ID', ''),
        ],

        'whatsapp' => [
            'access_token' => env('WHATSAPP_ACCESS_TOKEN', ''),
            'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID', ''),
        ],

        'tumblr' => [
            'access_token' => env('TUMBLR_ACCESS_TOKEN', ''),
            'blog_identifier' => env('TUMBLR_BLOG_IDENTIFIER', ''),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Proxy Configuration
    |--------------------------------------------------------------------------
    */

    'proxy' => [
        'type' => env('OWLSTACK_PROXY_TYPE', ''),
        'hostname' => env('OWLSTACK_PROXY_HOST', ''),
        'port' => env('OWLSTACK_PROXY_PORT', ''),
        'username' => env('OWLSTACK_PROXY_USERNAME', ''),
        'password' => env('OWLSTACK_PROXY_PASSWORD', ''),
    ],

];
