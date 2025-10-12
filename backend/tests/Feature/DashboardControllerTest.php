<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin    = User::factory()->create(['role' => 'admin']);
    $this->user     = User::factory()->create();
    $this->category = Category::factory()->create();
});

/**
 * Test admin can access dashboard stats.
 */
test('admin can access dashboard stats', function () {
    $response = $this->actingAs($this->admin)->getJson('/api/admin/dashboard/stats');

    $response->assertOk()
        ->assertJsonStructure([
            'products' => [
                'total',
                'active',
                'inactive',
                'featured',
                'low_stock',
                'out_of_stock',
            ],
            'users'    => [
                'total',
                'new_this_month',
            ],
            'recent_products',
            'low_stock_alerts',
        ]);
});

/**
 * Test non-admin cannot access dashboard stats.
 */
test('non admin cannot access dashboard stats', function () {
    $response = $this->actingAs($this->user)->getJson('/api/admin/dashboard/stats');

    $response->assertForbidden();
});

/**
 * Test guest cannot access dashboard stats.
 */
test('guest cannot access dashboard stats', function () {
    $response = $this->getJson('/api/admin/dashboard/stats');

    $response->assertUnauthorized();
});

/**
 * Test product statistics are correct.
 */
test('product statistics are correct', function () {
    Product::factory()->count(5)->create(['is_active' => true, 'is_featured' => true]);
    Product::factory()->count(3)->create(['is_active' => true, 'is_featured' => false]);
    Product::factory()->count(2)->create(['is_active' => false]);
    Product::factory()->count(2)->create([
        'is_active'       => true,
        'stock_quantity'  => 5,
        'min_stock_level' => 10,
    ]);
    Product::factory()->count(1)->create([
        'is_active'      => true,
        'stock_quantity' => 0,
    ]);

    $response = $this->actingAs($this->admin)->getJson('/api/admin/dashboard/stats');

    $response->assertOk();

    $data = $response->json();

    expect($data['products']['total'])->toBe(13);
    expect($data['products']['active'])->toBe(11);
    expect($data['products']['inactive'])->toBe(2);
    expect($data['products']['featured'])->toBe(5);
    expect($data['products']['low_stock'])->toBe(2);
    expect($data['products']['out_of_stock'])->toBe(1);
});

/**
 * Test user statistics count correctly.
 */
test('user statistics count correctly', function () {
    User::factory()->count(5)->create(['created_at' => now()]);
    User::factory()->count(3)->create(['created_at' => now()->subMonth()]);

    $response = $this->actingAs($this->admin)->getJson('/api/admin/dashboard/stats');

    $response->assertOk();

    $data = $response->json();

    // 5 new users + 3 old users + 1 admin + 1 regular user from beforeEach
    expect($data['users']['total'])->toBe(10);
    expect($data['users']['new_this_month'])->toBe(7); // 5 + admin + regular user from beforeEach
});

/**
 * Test recent products are returned.
 */
test('recent products are returned', function () {
    Product::factory()->count(15)->create();

    $response = $this->actingAs($this->admin)->getJson('/api/admin/dashboard/stats');

    $response->assertOk();

    $data = $response->json();

    expect($data['recent_products'])->toHaveCount(10);
    expect($data['recent_products'][0])->toHaveKeys(['id', 'name', 'sku', 'price', 'category']);
});

/**
 * Test recent products include images.
 */
test('recent products include images', function () {
    $product = Product::factory()->create();
    ProductImage::factory()->for($product)->primary()->create();

    $response = $this->actingAs($this->admin)->getJson('/api/admin/dashboard/stats');

    $response->assertOk();

    $data = $response->json();

    expect($data['recent_products'][0])->toHaveKey('primary_image');
});

/**
 * Test low stock alerts only show active products.
 */
test('low stock alerts only show active products', function () {
    // Active low stock product
    Product::factory()->create([
        'is_active'       => true,
        'stock_quantity'  => 3,
        'min_stock_level' => 10,
    ]);

    // Inactive low stock product
    Product::factory()->create([
        'is_active'       => false,
        'stock_quantity'  => 2,
        'min_stock_level' => 10,
    ]);

    // Active out of stock product (should not appear)
    Product::factory()->create([
        'is_active'       => true,
        'stock_quantity'  => 0,
        'min_stock_level' => 10,
    ]);

    $response = $this->actingAs($this->admin)->getJson('/api/admin/dashboard/stats');

    $response->assertOk();

    $data = $response->json();

    expect($data['low_stock_alerts'])->toHaveCount(1);
    expect($data['low_stock_alerts'][0]['stock_quantity'])->toBe(3);
});

/**
 * Test low stock alerts are sorted by stock quantity.
 */
test('low stock alerts are sorted by stock quantity', function () {
    Product::factory()->create([
        'is_active'       => true,
        'stock_quantity'  => 5,
        'min_stock_level' => 10,
    ]);
    Product::factory()->create([
        'is_active'       => true,
        'stock_quantity'  => 2,
        'min_stock_level' => 10,
    ]);
    Product::factory()->create([
        'is_active'       => true,
        'stock_quantity'  => 8,
        'min_stock_level' => 10,
    ]);

    $response = $this->actingAs($this->admin)->getJson('/api/admin/dashboard/stats');

    $response->assertOk();

    $data = $response->json();

    expect($data['low_stock_alerts'][0]['stock_quantity'])->toBe(2);
    expect($data['low_stock_alerts'][1]['stock_quantity'])->toBe(5);
    expect($data['low_stock_alerts'][2]['stock_quantity'])->toBe(8);
});

/**
 * Test dashboard returns empty arrays when no data.
 */
test('dashboard returns empty arrays when no data', function () {
    // Only create admin and user, no other data
    $response = $this->actingAs($this->admin)->getJson('/api/admin/dashboard/stats');

    $response->assertOk();

    $data = $response->json();

    expect($data['products']['total'])->toBe(0);
    expect($data['recent_products'])->toBe([]);
    expect($data['low_stock_alerts'])->toBe([]);
});
