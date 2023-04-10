<?php

namespace Tests\Unit;

use App\Models\User;
use App\Traits\JwtTokenTrait;
use \Tests\TestCase;

class RegisterUserTest extends TestCase
{
    use JwtTokenTrait;
    /**
     * A basic unit test example.
     */

    public function testCanRegisterUser()
    {
        $payload = [
            'uuid' => \Illuminate\Support\Str::uuid(),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => '123 Main Street',
            'phone_number' => '1234567890',
            'email' => 'user@exam.com',
            'password' => '12345678',
            'password_confirmation' => '12345678'
        ];

        $response = $this->json('POST', '/api/v1/user/create', $payload);
        $response->assertStatus(201)
            ->assertJson([
                'message' => $response->json()['message'],
                'user' => $response->json()['user'],
                'token' => $response->json()['token']
            ]);;
        // extract token from response
        $responseData = $response->json();
        $token = $responseData['token'];

        // Make a separate request to get the token
        // use the token for subsequent requests
        $loginResponse =  $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('POST', '/api/v1/user/login', [
                'email' => $payload['email'],
                'password' => $payload['password'],
            ]);

        $user = User::where('uuid', $loginResponse->json()['user']['uuid'])->first();
        $this->actingAs($user, 'jwt');

        $loginResponse->assertStatus(200)
            ->assertJson([
                'message' => $loginResponse->json()['message'],
                'user' => $loginResponse->json()['user'],
                'token' => $loginResponse->json()['token']
            ]);
    }
}
