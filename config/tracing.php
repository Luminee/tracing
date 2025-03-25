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
     | You can provide an array of URI's that must be ignored (eg. 'api/*')
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
        'phpinfo' => true,  // Php version
        'messages' => true,  // Messages
        'time' => true,  // Time Data Logger
        'memory' => true,  // Memory usage
        'exceptions' => true,  // Exception display
        'log' => true,  // Logs from Monolog (merged in messages if enabled)
        'db' => true,  // Show database (PDO) queries and bindings
        'views' => true,  // Views with their data
        'route' => true,  // Current route information
        'auth' => false, // Display Laravel authentication status
        'gate' => true,  // Display Laravel Gate checks
        'session' => true,  // Display session data
        'symfony_request' => true,  // Only one can be enabled..
        'mail' => true,  // Catch mail messages
        'laravel' => false, // Laravel version and environment
        'events' => false, // All events fired
        'default_request' => false, // Regular or special Symfony request logger
        'logs' => false, // Add the latest log messages
        'files' => false, // Show the included files
        'config' => false, // Display config settings
        'cache' => false, // Display cache events
        'models' => true,  // Display models
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
        'auth' => [
            'show_name' => true,   // Also show the users name/email in the debugbar
        ],
        'db' => [
            'with_params' => true,   // Render SQL with the parameters substituted
            'backtrace' => true,   // Use a backtrace to find the origin of the query in your files.
            'backtrace_exclude_paths' => [],   // Paths to exclude from backtrace. (in addition to defaults)
            'timeline' => false,  // Add the queries to the timeline
            'explain' => [                 // Show EXPLAIN output on queries
                'enabled' => false,
                'types' => ['SELECT'],
                // // workaround ['SELECT'] only. https://github.com/barryvdh/laravel-debugbar/issues/888 ['SELECT', 'INSERT', 'UPDATE', 'DELETE']; for MySQL 5.6.3+
            ],
            'hints' => false,    // Show hints for common mistakes
        ],
        'mail' => [
            'full_log' => false
        ],
        'views' => [
            'data' => false,    //Note: Can slow down the application, because the data can be quite large..
        ],
        'route' => [
            'label' => true  // show complete route on bar
        ],
        'logs' => [
            'file' => null
        ],
        'cache' => [
            'values' => true // collect cache values
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
