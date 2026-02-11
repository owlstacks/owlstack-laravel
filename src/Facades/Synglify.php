<?php

declare(strict_types=1);

namespace Synglify\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Synglify\Core\Content\Post;
use Synglify\Core\Publishing\PublishResult;

/**
 * Synglify Facade.
 *
 * @method static PublishResult telegram(string $message, ?array $attachment = null, array $inlineKeyboard = [], array $options = [])
 * @method static PublishResult twitter(string $message, ?array $media = null, array $options = [])
 * @method static PublishResult x(string $message, ?array $media = null, array $options = [])
 * @method static PublishResult facebook(string $message, string $type = 'link', array $data = [], array $options = [])
 * @method static PublishResult publish(Post $post, string $platform, array $options = [])
 * @method static array<string, PublishResult> toAll(Post $post, array $options = [])
 *
 * @see \Synglify\Laravel\SendTo
 */
class Synglify extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'synglify';
    }
}
