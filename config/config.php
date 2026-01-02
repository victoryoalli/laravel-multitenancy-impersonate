<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'ttl' => 60, // Token TTL (time to live in seconds).
    'redirect_path' => '/home', // Default redirect path after impersonation.
    'auth_guard' => 'web', // Authentication guard to use.
    'rate_limit' => [
        'max_attempts' => 5, // Maximum token validation attempts.
        'decay_minutes' => 1, // Minutes until attempts reset.
    ],
];
