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

    ],

    /*
    |--------------------------------------------------------------------------
    | Proxy Configuration
    |--------------------------------------------------------------------------
    */

    'proxy' => [
        'type' => env('SYNGLIFY_PROXY_TYPE', ''),
        'hostname' => env('SYNGLIFY_PROXY_HOST', ''),
        'port' => env('SYNGLIFY_PROXY_PORT', ''),
        'username' => env('SYNGLIFY_PROXY_USERNAME', ''),
        'password' => env('SYNGLIFY_PROXY_PASSWORD', ''),
    ],

];
