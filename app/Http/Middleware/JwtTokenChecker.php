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
            return response()->json(['message' => 'Invalid Public or Secret key'], Response::HTTP_UNAUTHORIZED);
        } catch (SignatureInvalidException $e) {
            return response()->json(['message' => 'provided JWT signature verification failed'], Response::HTTP_UNAUTHORIZED);
        } catch (BeforeValidException $e) {
            return response()->json(['message' => 'invalid nbf or iat'], Response::HTTP_UNAUTHORIZED);
        } catch (ExpiredException $e) {
            return response()->json(['message' => 'Token expired'], Response::HTTP_UNAUTHORIZED);
        } catch (UnexpectedValueException $e) {
            return response()->json(['message' => 'Token invalid'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
