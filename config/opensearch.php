<?php

return [
    'connection' => [
        'host' => env('OPENSEARCH_HOST', 'http://localhost:9200'),
        'username' => env('OPENSEARCH_USERNAME'),
        'password' => env('OPENSEARCH_PASSWORD'),
        'connect_timeout' => (int) env('OPENSEARCH_CONNECT_TIMEOUT', 10),
        'timeout' => (int) env('OPENSEARCH_TIMEOUT', 30),
        'verify' => env('OPENSEARCH_SSL_VERIFY', true),
        'retries' => (int) env('OPENSEARCH_RETRIES', 0),

        'aws' => [
            'region' => env('OPENSEARCH_AWS_REGION'),
            'service' => env('OPENSEARCH_AWS_SERVICE', 'es'),
        ],
    ],

    'migrations' => [
        'directory' => base_path(env('OPENSEARCH_MIGRATIONS_DIRECTORY', 'opensearch/migrations')),
        'table' => env('OPENSEARCH_MIGRATIONS_TABLE', 'opensearch_migrations'),
    ],
];
