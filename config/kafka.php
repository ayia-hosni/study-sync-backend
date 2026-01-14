<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Kafka Brokers
    |--------------------------------------------------------------------------
    |
    | List of Kafka broker addresses. In production, specify multiple brokers
    | for high availability.
    |
    */
    'brokers' => env('KAFKA_BROKERS', 'localhost:9092'),

    /*
    |--------------------------------------------------------------------------
    | Default Topic Configuration
    |--------------------------------------------------------------------------
    |
    | Default settings for Kafka topics used by the recommendation system.
    |
    */
    'topics' => [
        // User interaction events (like, view, comment, share, bookmark)
        'user_interaction' => env('KAFKA_TOPIC_USER_INTERACTION', 'user-interaction-events'),

        // Post lifecycle events (created, updated, deleted)
        'post_lifecycle' => env('KAFKA_TOPIC_POST_LIFECYCLE', 'post-lifecycle-events'),

        // Post recommendations (recommendations published by Spring service)
        'post_recommendation' => env('KAFKA_TOPIC_POST_RECOMMENDATION', 'post-recommendation-events'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Producer Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the Kafka producer.
    |
    */
    'producer' => [
        // Number of acknowledgments required before considering a request complete
        // 'all' = wait for all in-sync replicas to acknowledge
        'acks' => env('KAFKA_PRODUCER_ACKS', 'all'),

        // Maximum number of retries
        'retries' => env('KAFKA_PRODUCER_RETRIES', 3),

        // Time to wait between retries (ms)
        'retry_backoff_ms' => env('KAFKA_PRODUCER_RETRY_BACKOFF_MS', 100),

        // Compression type (none, gzip, snappy, lz4, zstd)
        'compression' => env('KAFKA_PRODUCER_COMPRESSION', 'snappy'),

        // Enable idempotent producer to prevent duplicates
        'idempotent' => env('KAFKA_PRODUCER_IDEMPOTENT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Consumer Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the Kafka consumer (if consuming from Spring Boot service).
    |
    */
    'consumer' => [
        // Consumer group ID
        'group_id' => env('KAFKA_CONSUMER_GROUP_ID', 'laravel-backend-group'),

        // Where to start reading if no offset is stored
        // 'earliest' = start from beginning, 'latest' = start from end
        'auto_offset_reset' => env('KAFKA_CONSUMER_AUTO_OFFSET_RESET', 'latest'),

        // Enable auto-commit of offsets
        'enable_auto_commit' => env('KAFKA_CONSUMER_AUTO_COMMIT', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | gRPC Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the gRPC server that serves post/user details to
    | the Spring Boot recommendation service.
    |
    */
    'grpc' => [
        // gRPC server host
        'host' => env('GRPC_SERVER_HOST', '0.0.0.0'),

        // gRPC server port
        'port' => env('GRPC_SERVER_PORT', 6001),

        // Maximum message size (bytes)
        'max_message_size' => env('GRPC_MAX_MESSAGE_SIZE', 4194304), // 4MB

        // Enable TLS (should be true in production)
        'tls_enabled' => env('GRPC_TLS_ENABLED', false),

        // TLS certificate path
        'tls_cert' => env('GRPC_TLS_CERT', null),

        // TLS key path
        'tls_key' => env('GRPC_TLS_KEY', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Weights
    |--------------------------------------------------------------------------
    |
    | Weight multipliers for different interaction types.
    | These are sent as metadata to help the recommendation service.
    |
    */
    'event_weights' => [
        'view' => 0.5,
        'click' => 0.8,
        'like' => 1.0,
        'comment' => 2.0,
        'bookmark' => 2.5,
        'share' => 3.0,
    ],
];
