<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_clients_can_log_in_through_current_and_legacy_paths(): void
    {
        User::factory()->create([
            'email' => 'mobile@securets.com',
            'password' => bcrypt('secret123'),
        ]);

        foreach (['/api/mobile/login', '/api/login'] as $path) {
            $response = $this->postJson($path, [
                'email' => 'mobile@securets.com',
                'password' => 'secret123',
                'device_name' => 'iphone_15_pro',
            ]);

            $response->assertOk()
                ->assertJsonStructure([
                    'token',
                    'expires_at',
                    'expires_in_minutes',
                    'user' => ['id', 'name', 'email'],
                ]);

            $token = $response->json('token');

            $this->withHeader('Authorization', 'Bearer '.$token)
                ->getJson('/api/mobile/me')
                ->assertOk()
                ->assertJson([
                    'email' => 'mobile@securets.com',
                ]);
        }
    }

    public function test_mobile_login_handles_case_insensitivity_and_formatting(): void
    {
        User::factory()->create([
            'email' => 'mobile@securets.com',
            'password' => bcrypt('secret123'),
        ]);

        // Test with caps and spaces
        $response = $this->postJson('/api/mobile/login', [
            'email' => ' MOBILE@SECURETS.COM ',
            'password' => 'secret123',
        ]);

        $response->assertOk();
    }

    public function test_mobile_login_returns_expected_error_format(): void
    {
        User::factory()->create([
            'email' => 'mobile@securets.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/mobile/login', [
            'email' => 'mobile@securets.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The provided credentials do not match our records.',
                'errors' => [
                    'email' => ['Incorrect email or password.']
                ]
            ]);
    }
}
