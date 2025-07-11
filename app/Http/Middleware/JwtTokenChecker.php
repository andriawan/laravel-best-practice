<?php

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class JwtTokenChecker
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $authService = app(AuthService::class);
            $authService->decodeTokenFromRequest($request);
        } catch (InvalidArgumentException $e) {
            abort(Response::HTTP_UNAUTHORIZED, 'Invalid Public or Secret key');
        } catch (SignatureInvalidException $e) {
            abort(Response::HTTP_UNAUTHORIZED, 'provided JWT signature verification failed');
        } catch (BeforeValidException $e) {
            abort(Response::HTTP_UNAUTHORIZED, 'invalid nbf or iat');
        } catch (ExpiredException $e) {
            abort(Response::HTTP_UNAUTHORIZED, 'Token expired');
        } catch (UnexpectedValueException $e) {
            abort(Response::HTTP_UNAUTHORIZED, 'Token invalid');
        }

        return $next($request);
    }
}
