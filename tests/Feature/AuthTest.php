<?php

namespace Tests\Feature;

use App\Models\TokenCreationLog;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Str;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
    }

    private function doLogin($email, $password)
    {
        $response = $this->withBasicAuth($email, $password)->postJson('api/auth/login');

        return $response;
    }

    private function doLoginDefault()
    {
        return $this->doLogin($this->user->email, 'password');
    }

    public function test_random_route_should_return_not_found()
    {
        $response = $this->getJson('/random_route', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertNotFound();
    }

    public function test_login_should_success()
    {
        $response = $this->doLoginDefault();
        $response->assertSee(['token', 'refresh_token']);
        $response->assertOk();
    }

    public function test_login_should_fail()
    {
        $response = $this->doLogin($this->user->email, 'password_random');
        $response->assertBadRequest();
    }

    public function test_logout_should_success()
    {
        $response = $this->doLoginDefault();
        $logout = $this->withToken($response['token'])
            ->postJson('/api/auth/logout', [
                'token' => $response['token'],
                'refresh_token' => $response['refresh_token'],
            ]);
        $logout->assertOk();
        $profileWithToken = $this->withToken($response['token'])->getJson('/api/auth/me');
        $profileWithToken->assertUnauthorized();
        $profileWithRefresh = $this->withToken($response['refresh_token'])->getJson('/api/auth/me');
        $profileWithRefresh->assertUnauthorized();
    }

    public function test_authenticated_user_can_access_profile()
    {
        $response = $this->doLoginDefault();
        $token = $response['token'];
        $profile = $this->withToken($token)->getJson('/api/auth/me');
        $profile->assertOk();
    }

    public function test_get_profile_user_fail_with_random_token()
    {
        $token = 'random';
        $profile = $this->withToken($token)->getJson('/api/auth/me');
        $profile->assertUnauthorized();
    }

    public function test_authenticated_user_fail_with_expired_token()
    {
        $this->travelTo(now()->subHours(3));
        $response = $this->doLoginDefault();
        $token = $response['token'];
        $this->travelBack();
        $profile = $this->withToken($token)->getJson('/api/auth/me');
        $profile->assertUnauthorized();
        $profile->assertSee('Token expired');
    }

    public function test_authenticated_user_success_refresh_token()
    {
        $this->travelTo(now()->subHours(3));
        $response = $this->doLoginDefault();
        $token = $response['token'];
        $refresh = $response['refresh_token'];
        $this->travelBack();
        $profile = $this->withToken($token)->getJson('/api/auth/me');
        $profile->assertUnauthorized();
        $profile->assertSee('Token expired');
        $refresh = $this->postJson('/api/auth/token/refresh', [
            'refresh_token' => $refresh,
        ]);
        $refresh->assertOk();
        $token = $refresh['token'];
        $profile = $this->withToken($token)->getJson('/api/auth/me');
        $profile->assertOk();
    }

    public function test_authenticated_user_failed_with_expired_refresh_token()
    {
        $this->travelTo(now()->subYears(3));
        $response = $this->doLoginDefault();
        $token = $response['token'];
        $refresh = $response['refresh_token'];
        $this->travelBack();
        $profile = $this->withToken($token)->getJson('/api/auth/me');
        $profile->assertUnauthorized();
        $profile->assertSee('Token expired');
        $refresh = $this->postJson('/api/auth/token/refresh', [
            'refresh_token' => $refresh,
        ]);
        $refresh->assertUnauthorized();
    }

    public function test_should_success_login_with_algorithm_hs256_token()
    {
        config(['app.jwt.algorithm' => AuthService::HS256]);
        $response = $this->doLoginDefault();
        $profile = $this->withToken($response['token'])->getJson('/api/auth/me');
        $profile->assertOk();
    }

    public function test_should_raise_error_invalid_nbf_or_iat()
    {
        $this->travel(30)->minutes();
        $response = $this->doLoginDefault();
        $this->travelBack();
        $profile = $this->withToken($response['token'])->getJson('/api/auth/me');
        $profile->assertUnauthorized();
        $profile->assertSee('invalid nbf or iat');
    }

    public function test_should_raise_error_signature_invalid()
    {
        config([
            'app.jwt.algorithm' => AuthService::HS256,
            'app.jwt.secret' => Str::random(11),
        ]);
        $response = $this->doLoginDefault();
        config([
            'app.jwt.secret' => Str::random(11),
        ]);
        $profile = $this->withToken($response['token'])->getJson('/api/auth/me');
        $profile->assertUnauthorized();
        $profile->assertSee('provided JWT signature verification failed');
        config([
            'app.jwt.secret' => '',
        ]);
        $profile = $this->withToken($response['token'])->getJson('/api/auth/me');
        $profile->assertUnauthorized();
        $profile->assertSee('Invalid Public or Secret key');
    }

    public function test_backlist_by_user_token_should_success()
    {
        $response = $this->doLoginDefault();
        $blacklist = $this->withToken($response['token'])->postJson('/api/auth/token/blacklist');
        $blacklist->assertOk();
        $log = new TokenCreationLog;
        $this->assertDatabaseMissing($log->getTable(), ['sub' => 1]);
    }
}
