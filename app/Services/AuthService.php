<?php

namespace App\Services;

use App\Models\TokenCreationLog;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Str;
use UnexpectedValueException;

/**
 * Class AuthService.
 */
class AuthService
{
    const HS256 = 'HS256';

    const RS256 = 'RS256';

    const ACCESS_TOKEN_TYPE = 'access_token';

    const REFRESH_TOKEN_TYPE = 'refresh_token';

    public function authenticate(Request $request)
    {
        $user = $this->getUserForAuth($request);
        $token = $this->encodeToken($user->id);

        return response($token);
    }

    private function encodeToken($id)
    {
        $config = $this->buildConfigPayload($id);
        $key = $this->getKey();
        $algorithm = config('app.jwt.algorithm');
        $payloadAccessToken = $this->buildPayloadAccessToken($config);
        $payloadRefreshToken = $this->buildPayloadRefreshToken($config);
        $token = JWT::encode($payloadAccessToken, $key, $algorithm);
        $refresh = JWT::encode($payloadRefreshToken, $key, $algorithm);
        $this->cacheToken($payloadAccessToken);
        $this->trackToken($payloadAccessToken);
        $this->cacheToken($payloadRefreshToken);
        $this->trackToken($payloadRefreshToken);

        return [
            'token' => $token,
            'refresh_token' => $refresh,
        ];
    }

    public function decodeTokenFromRequest(Request $request)
    {
        $jwt = $request->bearerToken();
        $decoded = $this->decodeToken($jwt);
        $request->merge($decoded);

        return $request;
    }

    private function buildConfigPayload($id)
    {
        $config = [
            'user_id' => $id,
            'token_exp' => now()->addMinutes(config('app.jwt.expired.access_token')),
            'refresh_exp' => now()->addMinutes(config('app.jwt.expired.refresh_token')),
        ];

        return $config;
    }

    private function decodeToken($jwt, $type = self::ACCESS_TOKEN_TYPE)
    {
        $key = $this->getKey('public');
        $algorithm = config('app.jwt.algorithm');
        $decoded = JWT::decode($jwt, new Key($key, $algorithm));
        $decodedArray = collect($decoded)->toArray();
        abort_if($decodedArray['token_type'] !== $type,
            Response::HTTP_UNAUTHORIZED, 'Token type not match');
        abort_if($this->isTokenRevokedFromCache($decodedArray),
            Response::HTTP_UNAUTHORIZED, 'Token has been revoked');

        return $decodedArray;
    }

    public function refreshToken(Request $request)
    {
        try {
            $decoded = $this->decodeToken($request->refresh_token, self::REFRESH_TOKEN_TYPE);
            $token = $this->encodeToken($decoded['sub']);
            Cache::delete($decoded['jti']);

            return response($token);
        } catch (UnexpectedValueException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

    }

    public function logout(Request $request)
    {
        $token = $this->decodeToken($request->token);
        $refresh = $this->decodeToken($request->refresh_token, self::REFRESH_TOKEN_TYPE);
        Cache::deleteMultiple([
            $token['jti'], $refresh['jti'],
        ]);

        return response([
            'message' => 'logout success',
        ]);
    }

    public function getUserProfile(Request $request)
    {
        return User::find($request->sub);
    }

    private function getUserForAuth(Request $request)
    {
        $username = urldecode($request->headers->get('php-auth-user'));
        $password = $request->headers->get('php-auth-pw');
        $user = User::where(['email' => $username])->firstOrFail();
        $isValidCredential = Hash::check($password, $user->password);
        abort_if(! $isValidCredential, Response::HTTP_BAD_REQUEST, 'invalid credentials');

        return $user;
    }

    private function buildPayload($config = [])
    {
        throw_if(! isset($config['user_id']), ValidationException::class, 'please provide user_id for jti');
        $now = now();
        $exp = $config['exp'];
        $id = $config['user_id'];
        $payload = [
            'iss' => request()->getSchemeAndHttpHost(),
            'iat' => $now->unix(),
            'sub' => $id,
            'nbf' => $now->unix(),
            'jti' => Str::uuid()->toString(),
            'exp' => $exp,
            'token_type' => $config['token_type'] ?? self::ACCESS_TOKEN_TYPE,
        ];

        return $payload;
    }

    private function buildPayloadAccessToken($config = [])
    {
        $defaultExp = now()->addMinutes(5)->unix();
        $config['exp'] = optional($config['token_exp'])->unix() ?? $defaultExp;

        return $this->buildPayload($config);
    }

    private function buildPayloadRefreshToken($config = [])
    {
        $defaultExp = now()->addMinutes(24 * 60)->unix();
        $config['exp'] = optional($config['refresh_exp'])->unix() ?? $defaultExp;
        $config['token_type'] = self::REFRESH_TOKEN_TYPE;

        return $this->buildPayload($config);
    }

    private function getPrivateKey()
    {
        $path = config('app.jwt.key.private');
        abort_unless(File::exists($path),
            Response::HTTP_NOT_FOUND, 'please provide private key');
        $privateKey = File::get($path);

        return $privateKey;
    }

    private function getPublicKey()
    {
        $path = config('app.jwt.key.public');
        abort_unless(File::exists($path),
            Response::HTTP_NOT_FOUND, 'please provide public key');
        $publicKey = File::get($path);

        return $publicKey;
    }

    private function getKey($type = 'private')
    {
        $algorithm = config('app.jwt.algorithm');
        $key = null;
        if ($algorithm === self::RS256) {
            $key = $type === 'private' ? $this->getPrivateKey() : $this->getPublicKey();
        } else {
            $key = $this->getSecretKey();
        }

        return $key;
    }

    private function getSecretKey()
    {
        $secretKey = config('app.jwt.secret');

        return $secretKey;
    }

    private function cacheToken($config)
    {
        $expired = Carbon::parse($config['exp']);
        Cache::put($config['jti'], $config, $expired);
    }

    private function trackToken($config)
    {
        $log = new TokenCreationLog($config);
        $log->save();
    }

    private function revokeTokenCacheBySub($user_id)
    {
        TokenCreationLog::query()->whereIn('sub', $user_id)->each(function ($data) {
            Cache::delete($data->jti);
            $data->delete();
        }, 50);
    }

    public function blacklistTokenByUserId(Request $request)
    {
        $this->revokeTokenCacheBySub([$request->sub]);

        return response([
            'message' => 'token blacklisted successfully',
        ]);
    }

    private function isTokenRevokedFromCache($config)
    {
        $exist = Cache::get($config['jti']);

        return isset($exist) ? false : true;
    }
}
