<?php

declare(strict_types=1);

namespace Fopost\Social\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Fopost\Social\Content\Post;
use Fopost\Social\Publishing\PublishResult;

/**
 * Fopost Facade.
 *
 * @method static PublishResult telegram(string $message, ?array $attachment = null, array $inlineKeyboard = [], array $options = [])
 * @method static PublishResult twitter(string $message, ?array $media = null, array $options = [])
 * @method static PublishResult x(string $message, ?array $media = null, array $options = [])
 * @method static PublishResult facebook(string $message, string $type = 'link', array $data = [], array $options = [])
 * @method static PublishResult linkedin(string $message, ?array $media = null, array $options = [])
 * @method static PublishResult reddit(string $message, array $options = [])
 * @method static PublishResult discord(string $message, ?array $media = null, array $options = [])
 * @method static PublishResult slack(string $message, array $options = [])
 * @method static PublishResult instagram(string $message, ?array $media = null, array $options = [])
 * @method static PublishResult pinterest(string $message, array $data = [], array $options = [])
 * @method static PublishResult whatsapp(string $message, array $options = [])
 * @method static PublishResult tumblr(string $message, ?array $media = null, array $options = [])
 * @method static PublishResult publish(Post $post, string $platform, array $options = [])
 * @method static array<string, PublishResult> toAll(Post $post, array $options = [])
 *
 * @see \Fopost\Social\Laravel\SendTo
 */
class FopostSocial extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'fopost-social';
    }
}
