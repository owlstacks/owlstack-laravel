<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Synglify\Core\Content\Post;
use Synglify\Laravel\Facades\Synglify;
use Synglify\Laravel\SendTo;

class SocialMediaController extends Controller
{
    /**
     * Test Telegram posting.
     */
    public function testTelegram(SendTo $sendTo)
    {
        $result = $sendTo->telegram('Test message from Laravel 12');

        return response()->json([
            'success' => $result->success,
            'platform' => $result->platformName,
            'external_id' => $result->externalId,
            'error' => $result->error,
        ]);
    }

    /**
     * Test X (Twitter) posting.
     */
    public function testX(SendTo $sendTo)
    {
        $result = $sendTo->x('Test tweet from Laravel 12');

        return response()->json([
            'success' => $result->success,
            'platform' => $result->platformName,
            'external_id' => $result->externalId,
            'error' => $result->error,
        ]);
    }

    /**
     * Test Facebook posting.
     */
    public function testFacebook(SendTo $sendTo)
    {
        $result = $sendTo->facebook('Test post from Laravel 12', 'link', [
            'link' => 'https://example.com',
        ]);

        return response()->json([
            'success' => $result->success,
            'platform' => $result->platformName,
            'external_id' => $result->externalId,
            'error' => $result->error,
        ]);
    }

    /**
     * Test all platforms at once.
     */
    public function testAll(SendTo $sendTo)
    {
        $post = new Post(
            title: 'Cross-Platform Test',
            body: 'Test post from Laravel 12 to all platforms.',
            url: 'https://example.com',
            tags: ['laravel', 'synglify'],
        );

        $results = $sendTo->toAll($post);

        return response()->json(
            collect($results)->map(fn ($r) => [
                'success' => $r->success,
                'external_id' => $r->externalId,
                'error' => $r->error,
            ])->all(),
        );
    }

    /**
     * Demo: Using the Facade instead of DI.
     */
    public function testFacade()
    {
        $result = Synglify::telegram('Hello from the Synglify facade!');

        return response()->json([
            'success' => $result->success,
            'external_id' => $result->externalId,
        ]);
    }
} 