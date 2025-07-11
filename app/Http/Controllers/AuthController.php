<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'auth',
    description: 'Operations about user',
)]
class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Auth based on pass and email
     */
    #[OA\Post(
        path: '/api/auth/login',
        summary: 'Logs user into the system',
        tags: ['auth'],
        security: [
            [
                'basicAuth' => [],
            ],
        ],
        responses: [
            new OA\Response(response: 200, description: 'successful operation'),
            new OA\Response(response: 400, description: 'Invalid username/password supplied'),
        ]
    )]
    public function authenticate(Request $request)
    {
        return $this->authService->authenticate($request);
    }

    /**
     * logout
     */
    #[OA\Post(
        path: '/api/auth/logout',
        summary: 'Logs out current logged in user session',
        tags: ['auth'],
        security: [
            [
                'bearerAuth' => [],
            ],
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'token', type: 'string'),
                    new OA\Property(property: 'refresh_token', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'successful operation'),
            new OA\Response(response: 401, description: 'unauthorized'),
        ]
    )]
    public function logout(Request $request)
    {
        return $this->authService->logout($request);
    }

    /**
     * refresh
     */
    #[OA\Post(
        path: '/api/auth/token/refresh',
        summary: 'refresh token',
        tags: ['auth'],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'refresh_token', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'successful operation'),
            new OA\Response(response: 401, description: 'unauthorized'),
        ]
    )]
    public function refreshToken(Request $request)
    {
        return $this->authService->refreshToken($request);
    }

    /**
     * get user profile
     */
    #[OA\Get(
        path: '/api/auth/me',
        summary: 'get user profile',
        tags: ['auth'],
        security: [
            [
                'bearerAuth' => [],
            ],
        ],
        responses: [
            new OA\Response(response: 200, description: 'successful operation'),
            new OA\Response(response: 401, description: 'unauthorized'),
        ]
    )]
    public function getUserProfile(Request $request)
    {
        return $this->authService->getUserProfile($request);
    }

    /**
     * Note to do: for admin only or high privilege
     */
    #[OA\Post(
        path: '/api/auth/blacklist',
        summary: 'blacklist token for user',
        tags: ['auth'],
        security: [
            [
                'bearerAuth' => [],
            ],
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'sub', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'successful operation'),
            new OA\Response(response: 401, description: 'unauthorized'),
        ]
    )]
    public function blacklistTokenForUser(Request $request)
    {
        return $this->authService->blacklistTokenByUserId($request);
    }
}
