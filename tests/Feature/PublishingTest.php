<?php

declare(strict_types=1);

namespace Synglify\Laravel\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Synglify\Core\Events\PostPublished;
use Synglify\Core\Events\PostFailed;
use Synglify\Core\Content\Post;
use Synglify\Laravel\Facades\Synglify;
use Synglify\Laravel\SendTo;
use Synglify\Laravel\Tests\TestCase;

class PublishingTest extends TestCase
{
    public function testFacadeResolves(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn($this->telegramSuccess());

        $result = Synglify::telegram('Via facade');

        $this->assertTrue($result->success);
    }

    public function testPostPublishedEventIsDispatched(): void
    {
        Event::fake([PostPublished::class]);

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn($this->telegramSuccess());

        /** @var SendTo $sendTo */
        $sendTo = $this->app->make(SendTo::class);
        $sendTo->telegram('Event test');

        Event::assertDispatched(PostPublished::class, function (PostPublished $event) {
            return $event->result->success
                && $event->result->platformName === 'telegram';
        });
    }

    public function testPostFailedEventIsDispatched(): void
    {
        Event::fake([PostFailed::class]);

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn([
                'status' => 500,
                'headers' => [],
                'body' => json_encode([
                    'ok' => false,
                    'description' => 'Internal Server Error',
                    'error_code' => 500,
                ]),
            ]);

        /** @var SendTo $sendTo */
        $sendTo = $this->app->make(SendTo::class);
        $sendTo->telegram('Fail test');

        Event::assertDispatched(PostFailed::class, function (PostFailed $event) {
            return $event->result->failed()
                && $event->result->platformName === 'telegram';
        });
    }

    public function testFullPublishWorkflowWithPost(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('sendMessage'),
                $this->callback(function (array $options) {
                    $text = $options['form_params']['text'] ?? '';
                    return str_contains($text, 'Full Workflow Title')
                        && str_contains($text, 'Full workflow body');
                }),
            )
            ->willReturn($this->telegramSuccess());

        $post = new Post(
            title: 'Full Workflow Title',
            body: 'Full workflow body text for testing.',
            tags: ['synglify', 'test'],
        );

        /** @var SendTo $sendTo */
        $sendTo = $this->app->make(SendTo::class);
        $result = $sendTo->publish($post, 'telegram');

        $this->assertTrue($result->success);
        $this->assertSame('123', $result->externalId);
    }

    public function testMissingPlatformReturnsFailedResult(): void
    {
        /** @var SendTo $sendTo */
        $sendTo = $this->app->make(SendTo::class);
        $post = new Post(title: 'Test', body: 'Content');

        $result = $sendTo->publish($post, 'nonexistent_platform');

        $this->assertFalse($result->success);
        $this->assertNotNull($result->error);
    }
}
