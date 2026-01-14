<?php

namespace App\Kafka\DTOs;

use JsonSerializable;

/**
 * Data Transfer Object for post lifecycle events (created, updated, deleted)
 */
class PostLifecycleEvent implements JsonSerializable
{
    public const TYPE_CREATED = 'POST_CREATED';
    public const TYPE_UPDATED = 'POST_UPDATED';
    public const TYPE_DELETED = 'POST_DELETED';
    public const TYPE_PUBLISHED = 'POST_PUBLISHED';
    public const TYPE_UNPUBLISHED = 'POST_UNPUBLISHED';

    public function __construct(
        public readonly string $eventType,
        public readonly int $postId,
        public readonly int $authorId,
        public readonly string $timestamp,
        public readonly ?array $postData = null,
        public readonly ?string $eventId = null,
    ) {
    }

    /**
     * Create a POST_CREATED event
     */
    public static function created(int $postId, int $authorId, array $postData): self
    {
        return new self(
            eventType: self::TYPE_CREATED,
            postId: $postId,
            authorId: $authorId,
            timestamp: now()->toIso8601String(),
            postData: $postData,
            eventId: uniqid('evt_', true),
        );
    }

    /**
     * Create a POST_UPDATED event
     */
    public static function updated(int $postId, int $authorId, array $postData): self
    {
        return new self(
            eventType: self::TYPE_UPDATED,
            postId: $postId,
            authorId: $authorId,
            timestamp: now()->toIso8601String(),
            postData: $postData,
            eventId: uniqid('evt_', true),
        );
    }

    /**
     * Create a POST_DELETED event
     */
    public static function deleted(int $postId, int $authorId): self
    {
        return new self(
            eventType: self::TYPE_DELETED,
            postId: $postId,
            authorId: $authorId,
            timestamp: now()->toIso8601String(),
            postData: null,
            eventId: uniqid('evt_', true),
        );
    }

    /**
     * Create a POST_PUBLISHED event
     */
    public static function published(int $postId, int $authorId, array $postData): self
    {
        return new self(
            eventType: self::TYPE_PUBLISHED,
            postId: $postId,
            authorId: $authorId,
            timestamp: now()->toIso8601String(),
            postData: $postData,
            eventId: uniqid('evt_', true),
        );
    }

    /**
     * Convert to array format
     */
    public function toArray(): array
    {
        return [
            'eventType' => $this->eventType,
            'postId' => $this->postId,
            'authorId' => $this->authorId,
            'timestamp' => $this->timestamp,
            'postData' => $this->postData,
            'eventId' => $this->eventId,
        ];
    }

    /**
     * JSON serialization
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
