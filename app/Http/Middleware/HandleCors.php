<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\HandleCors as Middleware;

class HandleCors extends Middleware
{
    /**
     * The allowed origins.
     *
     * @var array|string|null
     */
    protected $allowedOrigins = ['*'];

    /**
     * The allowed methods.
     *
     * @var array
     */
    protected $allowedMethods = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS'
    ];

    /**
     * The allowed headers.
     *
     * @var array
     */
    protected $allowedHeaders = [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
    ];

    /**
     * The exposed headers.
     *
     * @var array
     */
    protected $exposedHeaders = [
        'Authorization',
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
    ];

    /**
     * Indicates whether credentials are supported.
     *
     * @var bool
     */
    protected $supportsCredentials = true;

    /**
     * The maximum age of the CORS preflight request.
     *
     * @var int
     */
    protected $maxAge = 86400;
}
