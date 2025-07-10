<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function authenticate(Request $request)
    {
        return $this->authService->authenticate($request);
    }

    /**
     * logout
     */
    public function logout(Request $request)
    {
        return $this->authService->logout($request);
    }

    /**
     * refresh
     */
    public function refreshToken(Request $request)
    {
        return $this->authService->refreshToken($request);
    }

    /**
     * get user profile
     */
    public function getUserProfile(Request $request)
    {
        return $this->authService->getUserProfile($request);
    }

    /**
     * Note to do: for admin only or high privilege
     */
    public function blacklistTokenForUser(Request $request)
    {
        return $this->authService->blacklistTokenByUserId($request);
    }
}
