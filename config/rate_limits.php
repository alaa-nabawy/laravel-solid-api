<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the rate limiting configuration for different
    | types of endpoints in your application.
    |
    */

    'public' => [
        'auth' => [
            'get'    => 10,     // GET requests per minute
            'post'   => 5,     // POST requests per minute (login, signup)
            'put'    => 3,      // PUT requests per minute
            'patch'  => 3,    // PATCH requests per minute
            'delete' => 2,   // DELETE requests per minute
        ],
        'general' => [
            'get'    => 60,     // General public GET requests
            'post'   => 10,    // General public POST requests
            'put'    => 5,      // General public PUT requests
            'patch'  => 5,    // General public PATCH requests
            'delete' => 3,   // General public DELETE requests
        ],
        'search' => [
            'get'    => 30,     // Public search requests
            'post'   => 5,     // Public search POST requests
            'put'    => 2,      // Public search PUT requests
            'patch'  => 2,    // Public search PATCH requests
            'delete' => 1,   // Public search DELETE requests
        ],
        'upload' => [
            'get'    => 10,     // Public upload GET requests
            'post'   => 3,     // Public upload POST requests
            'put'    => 2,      // Public upload PUT requests
            'patch'  => 2,    // Public upload PATCH requests
            'delete' => 1,   // Public upload DELETE requests
        ],
    ],

    'authenticated' => [
        'general' => [
            'authenticated' => [
                'get'    => 200,    // Authenticated user GET requests
                'post'   => 50,    // Authenticated user POST requests
                'put'    => 30,     // Authenticated user PUT requests
                'patch'  => 30,   // Authenticated user PATCH requests
                'delete' => 20,  // Authenticated user DELETE requests
            ],
            'premium' => [
                'get'    => 500,    // Premium user GET requests
                'post'   => 100,   // Premium user POST requests
                'put'    => 60,     // Premium user PUT requests
                'patch'  => 60,   // Premium user PATCH requests
                'delete' => 40,  // Premium user DELETE requests
            ],
            'admin' => [
                'get'    => 1000,   // Admin GET requests
                'post'   => 200,   // Admin POST requests
                'put'    => 100,    // Admin PUT requests
                'patch'  => 100,  // Admin PATCH requests
                'delete' => 50,  // Admin DELETE requests
            ],
        ],
        'search' => [
            'authenticated' => [
                'get'    => 100,    // Authenticated search GET requests
                'post'   => 20,    // Authenticated search POST requests
                'put'    => 10,     // Authenticated search PUT requests
                'patch'  => 10,   // Authenticated search PATCH requests
                'delete' => 5,   // Authenticated search DELETE requests
            ],
            'premium' => [
                'get'    => 300,    // Premium search GET requests
                'post'   => 50,    // Premium search POST requests
                'put'    => 25,     // Premium search PUT requests
                'patch'  => 25,   // Premium search PATCH requests
                'delete' => 15,  // Premium search DELETE requests
            ],
            'admin' => [
                'get'    => 500,    // Admin search GET requests
                'post'   => 100,   // Admin search POST requests
                'put'    => 50,     // Admin search PUT requests
                'patch'  => 50,   // Admin search PATCH requests
                'delete' => 25,  // Admin search DELETE requests
            ],
        ],
        'upload' => [
            'authenticated' => [
                'get'    => 50,     // Authenticated upload GET requests
                'post'   => 10,    // Authenticated upload POST requests
                'put'    => 5,      // Authenticated upload PUT requests
                'patch'  => 5,    // Authenticated upload PATCH requests
                'delete' => 3,   // Authenticated upload DELETE requests
            ],
            'premium' => [
                'get'    => 100,    // Premium upload GET requests
                'post'   => 25,    // Premium upload POST requests
                'put'    => 15,     // Premium upload PUT requests
                'patch'  => 15,   // Premium upload PATCH requests
                'delete' => 10,  // Premium upload DELETE requests
            ],
            'admin' => [
                'get'    => 200,    // Admin upload GET requests
                'post'   => 50,    // Admin upload POST requests
                'put'    => 30,     // Admin upload PUT requests
                'patch'  => 30,   // Admin upload PATCH requests
                'delete' => 20,  // Admin upload DELETE requests
            ],
        ],
        'heavy' => [
            'authenticated' => [
                'get'    => 20,     // Authenticated heavy GET requests
                'post'   => 5,     // Authenticated heavy POST requests
                'put'    => 3,      // Authenticated heavy PUT requests
                'patch'  => 3,    // Authenticated heavy PATCH requests
                'delete' => 2,   // Authenticated heavy DELETE requests
            ],
            'premium' => [
                'get'    => 50,     // Premium heavy GET requests
                'post'   => 15,    // Premium heavy POST requests
                'put'    => 10,     // Premium heavy PUT requests
                'patch'  => 10,   // Premium heavy PATCH requests
                'delete' => 5,   // Premium heavy DELETE requests
            ],
            'admin' => [
                'get'    => 100,    // Admin heavy GET requests
                'post'   => 30,    // Admin heavy POST requests
                'put'    => 20,     // Admin heavy PUT requests
                'patch'  => 20,   // Admin heavy PATCH requests
                'delete' => 10,  // Admin heavy DELETE requests
            ],
        ],
    ],

    'admin' => [
        'general' => [
            'get'    => 300,    // Admin general GET requests
            'post'   => 50,    // Admin general POST requests
            'put'    => 30,     // Admin general PUT requests
            'patch'  => 30,   // Admin general PATCH requests
            'delete' => 20,  // Admin general DELETE requests
        ],
        'users' => [
            'get'    => 100,    // Admin user management GET requests
            'post'   => 20,    // Admin user management POST requests
            'put'    => 15,     // Admin user management PUT requests
            'patch'  => 15,   // Admin user management PATCH requests
            'delete' => 10,  // Admin user management DELETE requests
        ],
        'settings' => [
            'get'    => 50,     // Admin settings GET requests
            'post'   => 10,    // Admin settings POST requests
            'put'    => 5,      // Admin settings PUT requests
            'patch'  => 5,    // Admin settings PATCH requests
            'delete' => 3,   // Admin settings DELETE requests
        ],
        'logs' => [
            'get'    => 200,    // Admin logs GET requests
            'post'   => 20,    // Admin logs POST requests
            'put'    => 10,     // Admin logs PUT requests
            'patch'  => 10,   // Admin logs PATCH requests
            'delete' => 5,   // Admin logs DELETE requests
        ],
        'reports' => [
            'get'    => 50,     // Admin reports GET requests
            'post'   => 10,    // Admin reports POST requests
            'put'    => 5,      // Admin reports PUT requests
            'patch'  => 5,    // Admin reports PATCH requests
            'delete' => 3,   // Admin reports DELETE requests
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Options
    |--------------------------------------------------------------------------
    */

    'decay_minutes'  => 1, // Time window in minutes
    'log_violations' => true, // Whether to log rate limit violations
    'add_headers'    => true, // Whether to add rate limit headers to responses
];
