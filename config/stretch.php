<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Elasticsearch Connection
    |--------------------------------------------------------------------------
    |
    | The name of the default connection to use when none is specified.
    |
    */
    'default' => env('ELASTICSEARCH_DEFAULT_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch Connections
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to your Elasticsearch clusters.
    | You can define multiple connections and switch between them.
    |
    */
    'connections' => [
        'default' => [
            'hosts' => [
                env('ELASTICSEARCH_HOST', 'localhost:9200'),
            ],
            'username' => env('ELASTICSEARCH_USERNAME'),
            'password' => env('ELASTICSEARCH_PASSWORD'),
            'cloud_id' => env('ELASTICSEARCH_CLOUD_ID'),
            'api_key' => env('ELASTICSEARCH_API_KEY'),
            'ssl_verification' => env('ELASTICSEARCH_SSL_VERIFICATION', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Query Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for query execution.
    |
    */
    'query' => [
        'default_size' => 10,
        'max_size' => 10000,
        'timeout' => env('ELASTICSEARCH_TIMEOUT', '30s'),
        'allow_partial_search_results' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Aggregation Settings
    |--------------------------------------------------------------------------
    |
    | Settings for aggregation queries.
    |
    */
    'aggregations' => [
        'max_buckets' => 10000,
        'default_size' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configuration for query logging and debugging.
    |
    */
    'logging' => [
        'enabled' => env('STRETCH_LOGGING_ENABLED', false),
        'channel' => env('STRETCH_LOG_CHANNEL', 'default'),
        'log_queries' => env('STRETCH_LOG_QUERIES', false),
        'log_slow_queries' => env('STRETCH_LOG_SLOW_QUERIES', true),
        'slow_query_threshold' => env('STRETCH_SLOW_QUERY_THRESHOLD', 1000), // milliseconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Query result caching configuration.
    |
    */
    'cache' => [
        'enabled' => env('STRETCH_CACHE_ENABLED', false),
        'ttl' => env('STRETCH_CACHE_TTL', [300, 600]),
        'prefix' => env('STRETCH_CACHE_PREFIX', 'stretch:'),
        'driver' => env('STRETCH_CACHE_DRIVER', 'default'),
    ],
];
