<?php

return [
    /**
     * Default Values
     */
    'defaults' => [
        /**
         * Maximum limit of index responses
         */
        'max-limit' => 50,
    ],

    /**
     * Route Options
     */
    'routes' => [
        /**
         * configure routes by package
         */
        'configure' => true,

        /**
         * public route option
         * No JWT check, no user authenticated
         */
        'public-route' => [
            'prefix' => 'public',
            'controller' => \Ipunkt\LaravelJsonApi\Http\Controllers\JsonApiController::class,
        ],

        /**
         * secure route option
         * JWT check, user authenticated
         */
        'secure-route' => [
            'prefix' => 'secure',
            'controller' => \Ipunkt\LaravelJsonApi\Http\Controllers\JsonApiController::class,
            'middleware' => 'fixed.token',
            'token' => env('SERVICE_SECURE_TOKEN'),
        ],
    ],

    /**
     * Response Options
     */
    'response' => [
        /**
         * Handle Resource Request Responses
         */
        'resources' => [
            /**
             * What about links?
             */
            'links' => [
                /**
                 * self link is optional
                 */
                'self' => false,
            ],

            /**
             * related item calls
             */
            'item' => [
                /**
                 * What about links?
                 */
                'links' => [
                    /**
                     * self link is optional
                     */
                    'self' => false,
                ],
            ],
        ],

        /**
         * Handle Relationship Request Responses
         */
        'relationships' => [
            /**
             * What about links?
             */
            'links' => [
                /**
                 * self link is optional
                 */
                'self' => false,
                /**
                 * related link is optional
                 */
                'related' => false,
            ],

            /**
             * related item calls
             */
            'item' => [
                /**
                 * What about links?
                 */
                'links' => [
                    /**
                     * self link is optional
                     */
                    'self' => false,
                    /**
                     * related link is optional
                     */
                    'related' => false,
                ],
            ],
        ],
    ],
];