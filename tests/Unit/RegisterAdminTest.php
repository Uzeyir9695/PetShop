<?php

namespace Tests\Unit;

use App\Models\User;
use App\Traits\JwtTokenTrait;
use \Tests\TestCase;

class RegisterAdminTest extends TestCase
{
    use JwtTokenTrait;
    /**
     * A basic unit test example.
     */

    public function testCanRegisterAdmin()
    {
        $payload = [
            'uuid' => \Illuminate\Support\Str::uuid(),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => '123 Main Street',
            'phone_number' => '1234567890',
            'email' => 'admin2@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678'
        ];

        $response = $this->json('POST', '/api/v1/admin/create', $payload);
        $response->assertStatus(201)
            ->assertJson([
                'message' => $response->json()['message'],
                'admin' => $response->json()['admin'],
                'token' => $response->json()['token']
            ]);;
         // extract token from response
        $responseData = $response->json();
        $token = $responseData['token'];

        /*
         *
         *  Admin Login Test
         *
         */
        // Make a separate request to get the token
        // use the token for subsequent requests
        $loginResponse =  $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('POST', '/api/v1/admin/login', [
            'email' => $payload['email'],
            'password' => $payload['password'],
        ]);

        $admin = User::where('uuid', $loginResponse->json()['admin']['uuid'])->first();
        $this->actingAs($admin, 'jwt');

        $loginResponse->assertStatus(200)
            ->assertJson([
                'message' => $loginResponse->json()['message'],
                'admin' => $loginResponse->json()['admin'],
                'token' => $loginResponse->json()['token']
            ]);
    }
}
