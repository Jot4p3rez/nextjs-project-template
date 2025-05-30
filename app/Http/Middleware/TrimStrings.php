<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Transform the given value.
     *
     * @param  string  $value
     * @return string
     */
    protected function transform($value)
    {
        if (is_string($value)) {
            $value = trim($value);
            
            // Convert empty strings to null
            return $value === '' ? null : $value;
        }

        return $value;
    }
}
