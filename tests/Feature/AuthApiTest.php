<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

final class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login with valid username and password.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $password = 'password123';
        $user = AdminUser::factory()->create([
            'password' => Hash::make($password),
            'status' => 1,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'usernameOrEmail' => $user->username,
            'password' => $password,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'accessToken',
                'refreshToken',
                'expiresIn',
                'user' => [
                    'id',
                    'username',
                    'email',
                    'full_name',
                ],
                'message'
            ])
            ->assertJsonPath('status', 'success');
        
        $this->assertNotEmpty($response->json('accessToken'));
        $this->assertEquals($user->username, $response->json('user.username'));
    }

    /**
     * Test login with valid email and password.
     */
    public function test_user_can_login_with_email(): void
    {
        $password = 'password123';
        $user = AdminUser::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make($password),
            'status' => 1,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'usernameOrEmail' => 'test@example.com',
            'password' => $password,
        ]);

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('accessToken'));
    }

    /**
     * Test login with invalid password.
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = AdminUser::factory()->create([
            'password' => Hash::make('correct_password'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'usernameOrEmail' => $user->username,
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    /**
     * Test getting current user info.
     */
    public function test_authenticated_user_can_get_me_info(): void
    {
        $user = AdminUser::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('username', $user->username);
    }

    /**
     * Test logout functionality.
     */
    public function test_user_can_logout(): void
    {
        $user = AdminUser::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
        
        // Try to access /me again with the same token - should be unauthorized or bad request (invalid)
        $responseMe = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/v1/auth/me');
        
        $responseMe->assertStatus(400);
    }

    /**
     * Test token refresh functionality.
     */
    public function test_user_can_refresh_token(): void
    {
        $password = 'password123';
        $user = AdminUser::factory()->create([
            'password' => Hash::make($password),
            'status' => 1,
        ]);

        // Login to get refresh token
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'usernameOrEmail' => $user->username,
            'password' => $password,
        ]);

        $refreshToken = $loginResponse->json('refreshToken');

        // Call refresh endpoint
        $refreshResponse = $this->postJson('/api/v1/auth/refresh', [
            'refreshToken' => $refreshToken,
        ]);

        $refreshResponse->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'accessToken',
                'expiresIn',
            ])
            ->assertJsonPath('status', 'success');
        
        $this->assertNotEmpty($refreshResponse->json('accessToken'));
        $this->assertNotEquals($loginResponse->json('accessToken'), $refreshResponse->json('accessToken'));
    }
}
