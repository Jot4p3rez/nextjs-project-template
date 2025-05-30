<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = [
        'login',
        'logout',
        'admin/*', // Allow admin routes during maintenance
    ];

    /**
     * The responses that should be returned when maintenance mode is enabled.
     *
     * @var array<string, mixed>
     */
    protected $responses = [
        'default' => [
            'title' => 'Mantenimiento del Sistema',
            'message' => 'El sistema está en mantenimiento. Por favor, intente más tarde.',
            'retry' => 60, // Retry after 60 seconds
        ],
        'json' => [
            'error' => 'Sistema en mantenimiento',
            'message' => 'El sistema está temporalmente fuera de servicio por mantenimiento.',
            'retry_after' => 60,
        ],
    ];
}
