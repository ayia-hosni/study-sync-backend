<?php

namespace App\Listeners;

use App\Events\PostLiked;
use App\Events\PostViewed;
use App\Events\PostCommented;
use App\Events\PostShared;
use App\Events\PostBookmarked;
use App\Kafka\Producers\InteractionProducer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener that publishes user interaction events to Kafka
 * for the recommendation service to consume.
 *
 * This listener implements ShouldQueue to prevent Kafka
 * publishing from blocking the main request.
 */
class PublishInteractionToKafka implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    private InteractionProducer $producer;

    /**
     * Create the event listener.
     */
    public function __construct(InteractionProducer $producer)
    {
        $this->producer = $producer;
    }

    /**
     * Get the events this listener should subscribe to.
     */
    public function subscribe($events): array
    {
        return [
            PostLiked::class => 'handlePostLiked',
            PostViewed::class => 'handlePostViewed',
            PostCommented::class => 'handlePostCommented',
            PostShared::class => 'handlePostShared',
            PostBookmarked::class => 'handlePostBookmarked',
        ];
    }

    /**
     * Handle post liked event
     */
    public function handlePostLiked(PostLiked $event): void
    {
        try {
            $success = $this->producer->publishLike(
                userId: $event->userId,
                postId: $event->postId,
                category: $event->category ?? null,
            );

            if (!$success) {
                Log::warning('Failed to publish like event to Kafka', [
                    'user_id' => $event->userId,
                    'post_id' => $event->postId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error publishing like event to Kafka', [
                'user_id' => $event->userId,
                'post_id' => $event->postId,
                'error' => $e->getMessage(),
            ]);
            throw $e; // Re-throw for retry
        }
    }

    /**
     * Handle post viewed event
     */
    public function handlePostViewed(PostViewed $event): void
    {
        try {
            $success = $this->producer->publishView(
                userId: $event->userId,
                postId: $event->postId,
                duration: $event->viewDuration ?? null,
                category: $event->category ?? null,
            );

            if (!$success) {
                Log::warning('Failed to publish view event to Kafka', [
                    'user_id' => $event->userId,
                    'post_id' => $event->postId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error publishing view event to Kafka', [
                'user_id' => $event->userId,
                'post_id' => $event->postId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle post commented event
     */
    public function handlePostCommented(PostCommented $event): void
    {
        try {
            $success = $this->producer->publishComment(
                userId: $event->userId,
                postId: $event->postId,
                commentId: $event->commentId ?? null,
                category: $event->category ?? null,
            );

            if (!$success) {
                Log::warning('Failed to publish comment event to Kafka', [
                    'user_id' => $event->userId,
                    'post_id' => $event->postId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error publishing comment event to Kafka', [
                'user_id' => $event->userId,
                'post_id' => $event->postId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle post shared event
     */
    public function handlePostShared(PostShared $event): void
    {
        try {
            $success = $this->producer->publishShare(
                userId: $event->userId,
                postId: $event->postId,
                platform: $event->platform ?? null,
                category: $event->category ?? null,
            );

            if (!$success) {
                Log::warning('Failed to publish share event to Kafka', [
                    'user_id' => $event->userId,
                    'post_id' => $event->postId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error publishing share event to Kafka', [
                'user_id' => $event->userId,
                'post_id' => $event->postId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle post bookmarked event
     */
    public function handlePostBookmarked(PostBookmarked $event): void
    {
        try {
            $success = $this->producer->publishBookmark(
                userId: $event->userId,
                postId: $event->postId,
                category: $event->category ?? null,
            );

            if (!$success) {
                Log::warning('Failed to publish bookmark event to Kafka', [
                    'user_id' => $event->userId,
                    'post_id' => $event->postId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error publishing bookmark event to Kafka', [
                'user_id' => $event->userId,
                'post_id' => $event->postId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
