<?php

namespace App\Kafka\DTOs;

use JsonSerializable;

/**
 * Data Transfer Object for user interaction events sent to Kafka
 */
class InteractionEvent implements JsonSerializable
{
    public function __construct(
        public readonly int $userId,
        public readonly int $postId,
        public readonly string $interactionType,
        public readonly string $timestamp,
        public readonly ?array $metadata = null,
        public readonly ?string $eventId = null,
    ) {
    }

    /**
     * Create an instance from array data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            postId: $data['post_id'],
            interactionType: $data['interaction_type'],
            timestamp: $data['timestamp'] ?? now()->toIso8601String(),
            metadata: $data['metadata'] ?? null,
            eventId: $data['event_id'] ?? uniqid('evt_', true),
        );
    }

    /**
     * Create a LIKE event
     */
    public static function like(int $userId, int $postId, ?string $category = null): self
    {
        return new self(
            userId: $userId,
            postId: $postId,
            interactionType: 'LIKE',
            timestamp: now()->toIso8601String(),
            metadata: $category ? ['category' => $category] : null,
            eventId: uniqid('evt_', true),
        );
    }

    /**
     * Create an UNLIKE event
     */
    public static function unlike(int $userId, int $postId): self
    {
        return new self(
            userId: $userId,
            postId: $postId,
            interactionType: 'UNLIKE',
            timestamp: now()->toIso8601String(),
            eventId: uniqid('evt_', true),
        );
    }

    /**
     * Create a VIEW event
     */
    public static function view(int $userId, int $postId, ?int $duration = null, ?string $category = null): self
    {
        $metadata = [];
        if ($duration !== null) {
            $metadata['view_duration_seconds'] = $duration;
        }
        if ($category !== null) {
            $metadata['category'] = $category;
        }

        return new self(
            userId: $userId,
            postId: $postId,
            interactionType: 'VIEW',
            timestamp: now()->toIso8601String(),
            metadata: !empty($metadata) ? $metadata : null,
            eventId: uniqid('evt_', true),
        );
    }

    /**
     * Create a COMMENT event
     */
    public static function comment(int $userId, int $postId, ?int $commentId = null, ?string $category = null): self
    {
        $metadata = [];
        if ($commentId !== null) {
            $metadata['comment_id'] = $commentId;
        }
        if ($category !== null) {
            $metadata['category'] = $category;
        }

        return new self(
            userId: $userId,
            postId: $postId,
            interactionType: 'COMMENT',
            timestamp: now()->toIso8601String(),
            metadata: !empty($metadata) ? $metadata : null,
            eventId: uniqid('evt_', true),
        );
    }

    /**
     * Create a SHARE event
     */
    public static function share(int $userId, int $postId, ?string $platform = null, ?string $category = null): self
    {
        $metadata = [];
        if ($platform !== null) {
            $metadata['platform'] = $platform;
        }
        if ($category !== null) {
            $metadata['category'] = $category;
        }

        return new self(
            userId: $userId,
            postId: $postId,
            interactionType: 'SHARE',
            timestamp: now()->toIso8601String(),
            metadata: !empty($metadata) ? $metadata : null,
            eventId: uniqid('evt_', true),
        );
    }

    /**
     * Create a BOOKMARK event
     */
    public static function bookmark(int $userId, int $postId, ?string $category = null): self
    {
        return new self(
            userId: $userId,
            postId: $postId,
            interactionType: 'BOOKMARK',
            timestamp: now()->toIso8601String(),
            metadata: $category ? ['category' => $category] : null,
            eventId: uniqid('evt_', true),
        );
    }

    /**
     * Create a CLICK event
     */
    public static function click(int $userId, int $postId, ?string $source = null): self
    {
        return new self(
            userId: $userId,
            postId: $postId,
            interactionType: 'CLICK',
            timestamp: now()->toIso8601String(),
            metadata: $source ? ['source' => $source] : null,
            eventId: uniqid('evt_', true),
        );
    }

    /**
     * Convert to array format matching Spring Boot DTO
     */
    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'postId' => $this->postId,
            'interactionType' => $this->interactionType,
            'timestamp' => $this->timestamp,
            'metadata' => $this->metadata,
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
