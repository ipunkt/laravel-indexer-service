<?php

return [
    'endpoint' => [
        'default' => [
            'host' => env('SOLR_HOST'),
            'port' => env('SOLR_PORT', 8983),
            'path' => env('SOLR_PATH', '/solr/'),
            'core' => env('SOLR_CORE', 'default'),
            'username' => env('SOLR_USERNAME'),
            'password' => env('SOLR_PASSWORD'),
            'timeout' => env('SOLR_TIMEOUT', 30),
        ],
    ],
];