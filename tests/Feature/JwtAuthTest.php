<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JwtAuthTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
    }

    public function test_cannot_login_and_without_jwt_token()
    {
        $response = $this->getJson('/api/user', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)->assertJsonStructure(['token']);
    }

    public function test_fails_to_access_protected_route_without_token()
    {
        $this->getJson('/api/protected')
            ->assertStatus(401);
    }

    public function test_can_access_protected_route_with_valid_token()
    {
        $token = auth()->guard()->attempt(['email' => $this->user->email, 'password' => 'password']);

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/protected')
            ->assertOk()
            ->assertJson(['message' => 'Access granted']);
    }
}
