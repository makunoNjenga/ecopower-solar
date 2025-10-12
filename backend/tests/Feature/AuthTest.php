<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Registration disabled
// test('users can register', function () {
//     $userData = [
//         'name' => 'John Doe',
//         'email' => 'john@ecopowertechglobal.com',
//         'password' => 'password123',
//         'password_confirmation' => 'password123',
//     ];

//     $response = $this->postJson('/api/register', $userData);

//     $response->assertStatus(201)
//              ->assertJsonStructure([
//                  'message',
//                  'user' => ['id', 'name', 'email'],
//                  'token'
//              ]);

//     $this->assertDatabaseHas('users', [
//         'email' => 'john@ecopowertechglobal.com',
//         'name' => 'John Doe'
//     ]);
// });

test('users can login with valid credentials', function () {
    $user = User::factory()->create([
        'email'    => 'test@ecopowertechglobal.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email'    => 'test@ecopowertechglobal.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'user',
            'token',
        ]);
});

test('users cannot login with invalid credentials', function () {
    $response = $this->postJson('/api/login', [
        'email'    => 'invalid@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Invalid credentials']);
});

test('authenticated users can access their profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/user');

    $response->assertStatus(200)
        ->assertJsonStructure(['user' => ['id', 'name', 'email']]);
});

test('users can logout', function () {
    $user  = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Logged out successfully']);
});
