<?php
return [

    /**
     * Set to false to disable the healthcheck route
     */
    'enable' => true,

    /**
     * The GET route where the healthcheck is performed
     */
    'route' => '/healthcheck',

    /**
     * Decides which healthchecks are performed
     *
     * Available:
     * - database
     * - storage
     * - solr
     */
    'checks' => [
        'redis',
        'solr',
    ],

    /**
     * Database options:
     *
     * 'database' => 'tablename'.
     * Tested by doing a select limit 1 on
     */
    'database' => [
        'dbname' => 'dbtable',
    ],

    /**
     * Storage options:
     *
     * '/path/to/check/'
     * Tested by trying to write the current date to PATH/healthcheck.txt
     */
    'storage' => [
        storage_path()
    ],

    /**
     * Redis options:
     *
     * 'redis connection name'
     * Redis connection name to test
     */
    'redis' => [
        'default',
    ],

    /**
     * Solr options
     * Array of instance configuration, each has to configure host, port, path and core
     */
    'solr' => [
        //  solr instance check
        [
            'endpoint' => [
                'default' => [
                    'host' => env('SOLR_HOST'),
                    'port' => env('SOLR_PORT', 8983),
                    'path' => env('SOLR_PATH', '/solr/'),
                    'core' => env('SOLR_CORE', 'default'),
                    'username' => env('SOLR_USERNAME'),
                    'password' => env('SOLR_PASSWORD'),
                    'timeout' => env('SOLR_TIMEOUT', 30),
                ]
            ]
        ],
    ],
];
