<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Tracing Settings
     |--------------------------------------------------------------------------
     |
     | Tracing is enabled by default, when debug is set to true in app.php.
     | You can override the value by setting enable to true or false instead of null.
     |
     | You can provide an array of URI's that must be ignored (e.g. 'api/*')
     |
     */

    'enabled' => env('TRACING_ENABLED', false),

    /*
     |--------------------------------------------------------------------------
     | Middleware Settings
     |--------------------------------------------------------------------------
     |
     | Middleware for Tracing
     |
     */

    'middleware' => [
        'excluded_paths' => [
            //
        ]
    ],

    /*
     |--------------------------------------------------------------------------
     | Custom Error Handler for Deprecated warnings
     |--------------------------------------------------------------------------
     |
     | When enabled, the Tracing shows deprecated warnings for Symfony components
     | in the Messages tab.
     |
     */
    'error_handler' => false,

    /*
     |--------------------------------------------------------------------------
     | DataCollectors
     |--------------------------------------------------------------------------
     |
     | Enable/disable DataCollectors
     |
     */

    'collectors' => [
        'time' => true,  // Time Data Logger
        'db' => true,  // Show database (PDO) queries and bindings
    ],

    /*
     |--------------------------------------------------------------------------
     | Extra options
     |--------------------------------------------------------------------------
     |
     | Configure some DataCollectors
     |
     */

    'options' => [
        'time' => [
            'memory_usage' => false
        ],
        'db' => [
            'timeline' => env('TRACING_DB_TIMELINE', false),  // Add the queries to the timeline
        ],
    ],

    'views' => [
        'zipkin' => env('ZIPKIN_ENABLED', false),
        'json' => env('JSON_ENABLED', false),
    ],

    /*
     |--------------------------------------------------------------------------
     | Zipkin Settings
     |--------------------------------------------------------------------------
     |
     | Transport data to Zipkin
     |
     */
    'zipkin' => [

        'connection' => [
            'host' => env('ZIPKIN_HOST', 'localhost'),
            'port' => env('ZIPKIN_PORT', 9411),
            'serviceName' => env('ZIPKIN_SERVICE_NAME', 'Laravel'),
        ],
    ],

];
