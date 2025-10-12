<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin    = User::factory()->create(['role' => 'admin']);
    $this->user     = User::factory()->create();
    $this->category = Category::factory()->create();
});

/**
 * Test guest can view active products.
 */
test('guest can view active products', function () {
    Product::factory()->count(3)->create(['is_active' => true]);
    Product::factory()->count(2)->create(['is_active' => false]);

    $response = $this->getJson('/api/products');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

/**
 * Test guest can view single active product.
 */
test('guest can view single active product', function () {
    $product = Product::factory()->create(['is_active' => true]);

    $response = $this->getJson("/api/products/{$product->id}");

    $response->assertOk()
        ->assertJsonFragment(['name' => $product->name]);
});

/**
 * Test guest cannot view inactive product.
 */
test('guest cannot view inactive product', function () {
    $product = Product::factory()->create(['is_active' => false]);

    $response = $this->getJson("/api/products/{$product->id}");

    $response->assertStatus(Response::HTTP_NOT_FOUND);
});

/**
 * Test admin can view all products including inactive.
 */
test('admin can view all products including inactive', function () {
    Product::factory()->count(3)->create(['is_active' => true]);
    Product::factory()->count(2)->create(['is_active' => false]);

    $response = $this->actingAs($this->admin)->getJson('/api/admin/products');

    $response->assertOk()
        ->assertJsonCount(5, 'data');
});

/**
 * Test can filter products by category.
 */
test('can filter products by category', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    Product::factory()->count(2)->create(['category_id' => $category1->id, 'is_active' => true]);
    Product::factory()->count(3)->create(['category_id' => $category2->id, 'is_active' => true]);

    $response = $this->getJson("/api/products?category_id={$category1->id}");

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

/**
 * Test can filter featured products.
 */
test('can filter featured products', function () {
    Product::factory()->count(2)->create(['is_featured' => true, 'is_active' => true]);
    Product::factory()->count(3)->create(['is_featured' => false, 'is_active' => true]);

    $response = $this->getJson('/api/products?featured=true');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

/**
 * Test can search products.
 */
test('can search products', function () {
    Product::factory()->create(['name' => 'Solar Panel Kit', 'is_active' => true]);
    Product::factory()->create(['name' => 'Battery Pack', 'is_active' => true]);

    $response = $this->getJson('/api/products?search=Solar');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['name' => 'Solar Panel Kit']);
});

/**
 * Test admin can create product.
 */
test('admin can create product', function () {
    $productData = [
        'name'              => 'New Solar Panel',
        'description'       => 'High-efficiency solar panel',
        'short_description' => 'Great for homes',
        'sku'               => 'SP-001',
        'price'             => 299.99,
        'sale_price'        => null,
        'stock_quantity'    => 50,
        'min_stock_level'   => 10,
        'category_id'       => $this->category->id,
        'brand'             => 'EcoPower',
        'is_featured'       => true,
        'is_active'         => true,
    ];

    $response = $this->actingAs($this->admin)->postJson('/api/admin/products', $productData);

    $response->assertStatus(Response::HTTP_CREATED)
        ->assertJsonFragment(['name' => 'New Solar Panel']);

    $this->assertDatabaseHas('products', [
        'name' => 'New Solar Panel',
        'sku'  => 'SP-001',
    ]);
});

/**
 * Test admin can update product.
 */
test('admin can update product', function () {
    $product = Product::factory()->create();

    $updateData = [
        'name'  => 'Updated Product Name',
        'price' => 399.99,
    ];

    $response = $this->actingAs($this->admin)->putJson("/api/admin/products/{$product->id}", $updateData);

    $response->assertOk()
        ->assertJsonFragment(['name' => 'Updated Product Name']);

    $this->assertDatabaseHas('products', [
        'id'    => $product->id,
        'name'  => 'Updated Product Name',
        'price' => 399.99,
    ]);
});

/**
 * Test admin can delete product.
 */
test('admin can delete product', function () {
    $product = Product::factory()->create();

    $response = $this->actingAs($this->admin)->deleteJson("/api/admin/products/{$product->id}");

    $response->assertOk();

    $this->assertSoftDeleted('products', [
        'id' => $product->id,
    ]);
});

/**
 * Test non-admin cannot create product.
 */
test('non admin cannot create product', function () {
    $productData = [
        'name'           => 'New Solar Panel',
        'description'    => 'Test',
        'sku'            => 'SP-001',
        'price'          => 299.99,
        'stock_quantity' => 50,
        'category_id'    => $this->category->id,
    ];

    $response = $this->actingAs($this->user)->postJson('/api/admin/products', $productData);

    $response->assertForbidden();
});

/**
 * Test create product validation.
 */
test('create product validates required fields', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/admin/products', []);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['name', 'description', 'sku', 'price', 'stock_quantity', 'category_id']);
});

/**
 * Test update product slug when name changes.
 */
test('update product slug when name changes', function () {
    $product = Product::factory()->create(['name' => 'Original Name']);

    $this->actingAs($this->admin)->putJson("/api/admin/products/{$product->id}", [
        'name' => 'New Product Name',
    ]);

    $product->refresh();

    expect($product->slug)->toBe('new-product-name');
});

/**
 * Test product resource includes relationships.
 */
test('product resource includes relationships', function () {
    $product = Product::factory()->create();
    ProductImage::factory()->for($product)->primary()->create();
    ProductImage::factory()->for($product)->count(2)->create();

    $response = $this->getJson("/api/products/{$product->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'slug',
                'description',
                'price',
                'category',
                'images',
                'primary_image',
            ],
        ]);
});

