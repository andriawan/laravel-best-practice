<?php

namespace Tests\Unit\Providers;

use App\Models\User;
use App\Providers\SwaggerUiServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class SwaggerUiServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Register the provider manually since it might not be auto-registered in tests
        $this->app->register(SwaggerUiServiceProvider::class);
    }

    public function test_swagger_ui_gate_denies_access_for_unauthorized_user()
    {
        $user = User::factory()->create(['email' => 'other@example.com']);

        $this->assertFalse(Gate::forUser($user)->allows('viewSwaggerUI'));
    }

    public function test_swagger_ui_gate_denies_access_for_guest()
    {
        $this->assertFalse(Gate::allows('viewSwaggerUI'));
    }
}
