<?php

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin   = User::factory()->create(['role' => 'admin']);
    $this->user    = User::factory()->create();
    $this->product = Product::factory()->create();

    Storage::fake('public');
});

/**
 * Test admin can get product images.
 */
test('admin can get product images', function () {
    ProductImage::factory()->for($this->product)->count(3)->create();

    $response = $this->actingAs($this->admin)->getJson("/api/admin/products/{$this->product->id}/images");

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

/**
 * Test admin can upload product image.
 */
test('admin can upload product image', function () {
    $file = UploadedFile::fake()->image('product.jpg', 800, 600);

    $response = $this->actingAs($this->admin)->postJson("/api/admin/products/{$this->product->id}/images", [
        'image'      => $file,
        'alt_text'   => 'Product Image',
        'is_primary' => false,
    ]);

    $response->assertStatus(Response::HTTP_CREATED)
        ->assertJsonStructure([
            'data' => [
                'id',
                'path',
                'filename',
                'alt_text',
                'is_primary',
            ],
        ]);

    $this->assertDatabaseHas('product_images', [
        'product_id' => $this->product->id,
        'alt_text'   => 'Product Image',
        'is_primary' => false,
    ]);

    Storage::disk('public')->assertExists('products/' . $response->json('data.filename'));
});

/**
 * Test upload image validates file type.
 */
test('upload image validates file type', function () {
    $file = UploadedFile::fake()->create('document.pdf', 1000);

    $response = $this->actingAs($this->admin)->postJson("/api/admin/products/{$this->product->id}/images", [
        'image' => $file,
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['image']);
});

/**
 * Test upload image validates file size.
 */
test('upload image validates file size', function () {
    $file = UploadedFile::fake()->image('large.jpg')->size(6000);

    $response = $this->actingAs($this->admin)->postJson("/api/admin/products/{$this->product->id}/images", [
        'image' => $file,
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['image']);
});

/**
 * Test setting image as primary removes other primary flags.
 */
test('setting image as primary removes other primary flags', function () {
    $image1 = ProductImage::factory()->for($this->product)->primary()->create();
    $file   = UploadedFile::fake()->image('product.jpg');

    $response = $this->actingAs($this->admin)->postJson("/api/admin/products/{$this->product->id}/images", [
        'image'      => $file,
        'is_primary' => true,
    ]);

    $response->assertStatus(Response::HTTP_CREATED);

    $image1->refresh();
    expect($image1->is_primary)->toBeFalse();

    $newImage = ProductImage::find($response->json('data.id'));
    expect($newImage->is_primary)->toBeTrue();
});

/**
 * Test admin can update image details.
 */
test('admin can update image details', function () {
    $image = ProductImage::factory()->for($this->product)->create([
        'alt_text'   => 'Old Alt Text',
        'is_primary' => false,
    ]);

    $response = $this->actingAs($this->admin)->putJson("/api/admin/products/{$this->product->id}/images/{$image->id}", [
        'alt_text'   => 'New Alt Text',
        'sort_order' => 5,
        'is_primary' => true,
    ]);

    $response->assertOk()
        ->assertJsonFragment(['alt_text' => 'New Alt Text']);

    $this->assertDatabaseHas('product_images', [
        'id'         => $image->id,
        'alt_text'   => 'New Alt Text',
        'sort_order' => 5,
        'is_primary' => true,
    ]);
});

/**
 * Test updating image to primary removes other primary flags.
 */
test('updating image to primary removes other primary flags', function () {
    $image1 = ProductImage::factory()->for($this->product)->primary()->create();
    $image2 = ProductImage::factory()->for($this->product)->create(['is_primary' => false]);

    $response = $this->actingAs($this->admin)->putJson("/api/admin/products/{$this->product->id}/images/{$image2->id}", [
        'is_primary' => true,
    ]);

    $response->assertOk();

    $image1->refresh();
    $image2->refresh();

    expect($image1->is_primary)->toBeFalse();
    expect($image2->is_primary)->toBeTrue();
});

/**
 * Test admin can delete image.
 */
test('admin can delete image', function () {
    $image = ProductImage::factory()->for($this->product)->create([
        'path' => 'products/test.jpg',
    ]);

    // Create fake file
    Storage::disk('public')->put('products/test.jpg', 'fake content');

    $response = $this->actingAs($this->admin)->deleteJson("/api/admin/products/{$this->product->id}/images/{$image->id}");

    $response->assertOk();

    $this->assertDatabaseMissing('product_images', [
        'id' => $image->id,
    ]);

    Storage::disk('public')->assertMissing('products/test.jpg');
});

/**
 * Test cannot update image from different product.
 */
test('cannot update image from different product', function () {
    $otherProduct = Product::factory()->create();
    $image        = ProductImage::factory()->for($otherProduct)->create();

    $response = $this->actingAs($this->admin)->putJson("/api/admin/products/{$this->product->id}/images/{$image->id}", [
        'alt_text' => 'Test',
    ]);

    $response->assertStatus(Response::HTTP_NOT_FOUND);
});

/**
 * Test cannot delete image from different product.
 */
test('cannot delete image from different product', function () {
    $otherProduct = Product::factory()->create();
    $image        = ProductImage::factory()->for($otherProduct)->create();

    $response = $this->actingAs($this->admin)->deleteJson("/api/admin/products/{$this->product->id}/images/{$image->id}");

    $response->assertStatus(Response::HTTP_NOT_FOUND);
});

/**
 * Test non-admin cannot upload images.
 */
test('non admin cannot upload images', function () {
    $file = UploadedFile::fake()->image('product.jpg');

    $response = $this->actingAs($this->user)->postJson("/api/admin/products/{$this->product->id}/images", [
        'image' => $file,
    ]);

    $response->assertForbidden();
});

/**
 * Test images are sorted by sort_order.
 */
test('images are sorted by sort_order', function () {
    ProductImage::factory()->for($this->product)->create(['sort_order' => 3]);
    ProductImage::factory()->for($this->product)->create(['sort_order' => 1]);
    ProductImage::factory()->for($this->product)->create(['sort_order' => 2]);

    $response = $this->actingAs($this->admin)->getJson("/api/admin/products/{$this->product->id}/images");

    $response->assertOk();

    $images = $response->json('data');
    expect($images[0]['sort_order'])->toBe(1);
    expect($images[1]['sort_order'])->toBe(2);
    expect($images[2]['sort_order'])->toBe(3);
});

/**
 * Test auto-increment sort order when uploading.
 */
test('auto increment sort order when uploading', function () {
    ProductImage::factory()->for($this->product)->create(['sort_order' => 2]);

    $file = UploadedFile::fake()->image('product.jpg');

    $response = $this->actingAs($this->admin)->postJson("/api/admin/products/{$this->product->id}/images", [
        'image' => $file,
    ]);

    $response->assertStatus(Response::HTTP_CREATED);

    $newImage = ProductImage::find($response->json('data.id'));
    expect($newImage->sort_order)->toBe(3);
});

