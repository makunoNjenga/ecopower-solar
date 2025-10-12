<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'name'     => 'Test User',
        'email'    => 'test@example.com',
        'phone'    => '1234567890',
        'password' => 'password123',
    ]);
});

test('user can update their profile', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson('/api/user/profile', [
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '0987654321',
        ]);

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email', 'phone'],
        ]);

    $this->user->refresh();
    expect($this->user->name)->toBe('Updated Name');
    expect($this->user->email)->toBe('updated@example.com');
    expect($this->user->phone)->toBe('0987654321');
});

test('user cannot update profile with invalid email', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson('/api/user/profile', [
            'name'  => 'Test User',
            'email' => 'invalid-email',
            'phone' => '1234567890',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user cannot update profile with duplicate email', function () {
    $otherUser = User::factory()->create([
        'email' => 'other@example.com',
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson('/api/user/profile', [
            'name'  => 'Test User',
            'email' => 'other@example.com',
            'phone' => '1234567890',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user can update their password with correct current password', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson('/api/user/password', [
            'current_password'      => 'password123',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

    $response->assertOk()
        ->assertJson([
            'message' => 'Password updated successfully',
        ]);

    $loginResponse = $this->postJson('/api/login', [
        'email'    => $this->user->email,
        'password' => 'newpassword123',
    ]);

    $loginResponse->assertOk();
});

test('user cannot update password with incorrect current password', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson('/api/user/password', [
            'current_password'      => 'wrongpassword',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Current password is incorrect',
        ]);
});

test('user cannot update password without confirmation', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson('/api/user/password', [
            'current_password' => 'password123',
            'password'         => 'newpassword123',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('user cannot update password with mismatched confirmation', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson('/api/user/password', [
            'current_password'      => 'password123',
            'password'              => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('guest cannot update profile', function () {
    $response = $this->putJson('/api/user/profile', [
        'name'  => 'Test User',
        'email' => 'test@example.com',
        'phone' => '1234567890',
    ]);

    $response->assertStatus(401);
});

test('guest cannot update password', function () {
    $response = $this->putJson('/api/user/password', [
        'current_password'      => 'password123',
        'password'              => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(401);
});

test('admin can list users', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    User::factory()->count(5)->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/users');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'email', 'is_admin', 'is_active'],
            ],
        ]);
});

test('regular user cannot list users', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/admin/users');

    $response->assertStatus(403);
});

test('admin can update user status', function () {
    $admin      = User::factory()->create(['is_admin' => true]);
    $targetUser = User::factory()->create(['is_active' => true]);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/admin/users/{$targetUser->id}/status", [
            'is_active' => false,
        ]);

    $response->assertOk()
        ->assertJson([
            'message' => 'User status updated successfully',
        ]);

    $targetUser->refresh();
    expect($targetUser->is_active)->toBeFalse();
});
