<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Cookies that should not be encrypted
        'remember_web_*', // Remember me token cookies
        'XSRF-TOKEN',    // CSRF token cookie
    ];

    /**
     * Indicates if cookies should be serialized.
     *
     * @var bool
     */
    protected static $serialize = true;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        if ($this->isReading($request)) {
            $this->disableEncryption();
        }

        return parent::handle($request, $next);
    }

    /**
     * Determine whether encryption is disabled for the given cookie.
     *
     * @param  string  $name
     * @return bool
     */
    public function isDisabled($name)
    {
        return in_array($name, $this->except) ||
               $this->isPatternMatched($name);
    }

    /**
     * Determine if the cookie name matches a pattern in the except array.
     *
     * @param  string  $name
     * @return bool
     */
    protected function isPatternMatched($name)
    {
        foreach ($this->except as $pattern) {
            if (str_contains($pattern, '*') && str_is($pattern, $name)) {
                return true;
            }
        }

        return false;
    }
}
