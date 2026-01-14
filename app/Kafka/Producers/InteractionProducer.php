<?php

namespace App\Kafka\Producers;

use App\Kafka\DTOs\InteractionEvent;
use App\Kafka\DTOs\PostLifecycleEvent;
use Illuminate\Support\Facades\Log;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

/**
 * Kafka Producer for sending interaction and post lifecycle events
 * to the recommendation service
 */
class InteractionProducer
{
    /**
     * Topic for user interaction events (like, view, comment, share)
     */
    private const INTERACTION_TOPIC = 'user-interaction-events';

    /**
     * Topic for post lifecycle events (created, updated, deleted)
     */
    private const POST_LIFECYCLE_TOPIC = 'post-lifecycle-events';

    /**
     * Maximum retries for failed messages
     */
    private const MAX_RETRIES = 3;

    /**
     * Publish a user interaction event to Kafka
     */
    public function publishInteraction(InteractionEvent $event): bool
    {
        return $this->publish(
            topic: self::INTERACTION_TOPIC,
            key: "{$event->userId}-{$event->postId}",
            body: $event->toArray(),
            context: "interaction {$event->interactionType}"
        );
    }

    /**
     * Publish a post lifecycle event to Kafka
     */
    public function publishPostLifecycle(PostLifecycleEvent $event): bool
    {
        return $this->publish(
            topic: self::POST_LIFECYCLE_TOPIC,
            key: (string) $event->postId,
            body: $event->toArray(),
            context: "post lifecycle {$event->eventType}"
        );
    }

    /**
     * Convenience method: Publish a like event
     */
    public function publishLike(int $userId, int $postId, ?string $category = null): bool
    {
        $event = InteractionEvent::like($userId, $postId, $category);
        return $this->publishInteraction($event);
    }

    /**
     * Convenience method: Publish an unlike event
     */
    public function publishUnlike(int $userId, int $postId): bool
    {
        $event = InteractionEvent::unlike($userId, $postId);
        return $this->publishInteraction($event);
    }

    /**
     * Convenience method: Publish a view event
     */
    public function publishView(int $userId, int $postId, ?int $duration = null, ?string $category = null): bool
    {
        $event = InteractionEvent::view($userId, $postId, $duration, $category);
        return $this->publishInteraction($event);
    }

    /**
     * Convenience method: Publish a comment event
     */
    public function publishComment(int $userId, int $postId, ?int $commentId = null, ?string $category = null): bool
    {
        $event = InteractionEvent::comment($userId, $postId, $commentId, $category);
        return $this->publishInteraction($event);
    }

    /**
     * Convenience method: Publish a share event
     */
    public function publishShare(int $userId, int $postId, ?string $platform = null, ?string $category = null): bool
    {
        $event = InteractionEvent::share($userId, $postId, $platform, $category);
        return $this->publishInteraction($event);
    }

    /**
     * Convenience method: Publish a bookmark event
     */
    public function publishBookmark(int $userId, int $postId, ?string $category = null): bool
    {
        $event = InteractionEvent::bookmark($userId, $postId, $category);
        return $this->publishInteraction($event);
    }

    /**
     * Convenience method: Publish a post created event
     */
    public function publishPostCreated(int $postId, int $authorId, array $postData): bool
    {
        $event = PostLifecycleEvent::created($postId, $authorId, $postData);
        return $this->publishPostLifecycle($event);
    }

    /**
     * Convenience method: Publish a post updated event
     */
    public function publishPostUpdated(int $postId, int $authorId, array $postData): bool
    {
        $event = PostLifecycleEvent::updated($postId, $authorId, $postData);
        return $this->publishPostLifecycle($event);
    }

    /**
     * Convenience method: Publish a post deleted event
     */
    public function publishPostDeleted(int $postId, int $authorId): bool
    {
        $event = PostLifecycleEvent::deleted($postId, $authorId);
        return $this->publishPostLifecycle($event);
    }

    /**
     * Generic publish method with retry logic
     */
    private function publish(string $topic, string $key, array $body, string $context): bool
    {
        $attempt = 0;

        while ($attempt < self::MAX_RETRIES) {
            try {
                $message = new Message(
                    headers: [
                        'content-type' => 'application/json',
                        'source' => 'study-sync-backend',
                        'timestamp' => now()->toIso8601String(),
                    ],
                    body: $body,
                    key: $key
                );

                Kafka::publishOn($topic)
                    ->withMessage($message)
                    ->send();

                Log::info("Kafka: Published {$context} event", [
                    'topic' => $topic,
                    'key' => $key,
                    'event_id' => $body['eventId'] ?? null,
                ]);

                return true;

            } catch (\Exception $e) {
                $attempt++;
                Log::warning("Kafka: Failed to publish {$context} (attempt {$attempt})", [
                    'topic' => $topic,
                    'key' => $key,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt >= self::MAX_RETRIES) {
                    Log::error("Kafka: Exhausted retries for {$context}", [
                        'topic' => $topic,
                        'key' => $key,
                        'body' => $body,
                        'error' => $e->getMessage(),
                    ]);
                    return false;
                }

                // Exponential backoff
                usleep(100000 * pow(2, $attempt)); // 100ms, 200ms, 400ms
            }
        }

        return false;
    }
}
