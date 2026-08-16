<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_sanctum_cookie_session(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@apotek.test',
            'password' => 'password',
        ]);

        $response = $this
            ->withHeader('Origin', 'http://localhost:5173')
            ->postJson('/api/v1/auth/login', [
                'email' => 'admin@apotek.test',
                'password' => 'password',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.user.email', 'admin@apotek.test')
            ->assertJsonMissing(['token_type' => 'Bearer']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_public_registration_endpoint_is_not_available(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Unauthorized Admin',
            'email' => 'attacker@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertNotFound();
    }

    public function test_login_is_rate_limited(): void
    {
        $email = 'limited'.Str::random(10).'@apotek.test';

        RateLimiter::clear($email.'|127.0.0.1');

        User::factory()->create([
            'email' => $email,
            'password' => 'correct-password',
        ]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withHeader('Origin', 'http://localhost:5173')
                ->postJson('/api/v1/auth/login', [
                    'email' => $email,
                    'password' => 'wrong-password',
                ])
                ->assertUnprocessable();
        }

        $this->withHeader('Origin', 'http://localhost:5173')
            ->postJson('/api/v1/auth/login', [
                'email' => $email,
                'password' => 'wrong-password',
            ])
            ->assertTooManyRequests();
    }

    public function test_authenticated_user_can_update_own_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Admin Lama',
            'email' => 'profile@apotek.test',
            'password' => 'old-password',
            'role' => 'admin',
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/auth/profile', [
                'name' => 'Admin Baru',
                'email' => 'updated@apotek.test',
                'password' => 'new-password123',
                'password_confirmation' => 'new-password123',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Admin Baru')
            ->assertJsonPath('data.email', 'updated@apotek.test')
            ->assertJsonPath('data.role', 'admin');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Admin Baru',
            'email' => 'updated@apotek.test',
            'role' => 'admin',
        ]);
    }
}
