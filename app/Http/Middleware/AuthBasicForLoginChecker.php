<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthBasicForLoginChecker
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $username = $request->headers->get('php-auth-user');
        $password = $request->headers->get('php-auth-pw');
        abort_if(empty($username) && empty($password),
            HttpResponse::HTTP_BAD_REQUEST,
            'Please supply auth basic method. see detail at https://datatracker.ietf.org/doc/html/rfc7617"');

        return $next($request);
    }
}
