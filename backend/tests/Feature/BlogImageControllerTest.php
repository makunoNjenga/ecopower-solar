<?php

use App\Models\Blog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->user  = User::factory()->create();
    Storage::fake('public');
});

/**
 * Test guest cannot access blog image endpoints.
 */
test('guest cannot access blog image endpoints', function () {
    $blog = Blog::factory()->create();

    $response = $this->getJson("/api/admin/blogs/{$blog->id}/images");

    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});

/**
 * Test regular user cannot access blog image endpoints.
 */
test('regular user cannot access blog image endpoints', function () {
    $blog = Blog::factory()->create();

    $response = $this->actingAs($this->user)->getJson("/api/admin/blogs/{$blog->id}/images");

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

/**
 * Test admin can list blog images.
 */
test('admin can list blog images', function () {
    $blog = Blog::factory()->create();

    $blog->images()->create([
        'image_path' => 'blogs/image1.jpg',
        'alt_text'   => 'Image 1',
        'sort_order' => 0,
    ]);

    $blog->images()->create([
        'image_path' => 'blogs/image2.jpg',
        'alt_text'   => 'Image 2',
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($this->admin)->getJson("/api/admin/blogs/{$blog->id}/images");

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

/**
 * Test admin can upload blog image.
 */
test('admin can upload blog image', function () {
    $blog  = Blog::factory()->create();
    $image = UploadedFile::fake()->image('blog.jpg', 1200, 800);

    $response = $this->actingAs($this->admin)->postJson("/api/admin/blogs/{$blog->id}/images", [
        'image'    => $image,
        'alt_text' => 'Test Image',
        'caption'  => 'Test Caption',
    ]);

    $response->assertStatus(Response::HTTP_CREATED)
        ->assertJsonFragment(['alt_text' => 'Test Image']);

    $this->assertDatabaseHas('blog_images', [
        'blog_id'  => $blog->id,
        'alt_text' => 'Test Image',
    ]);

    $blogImage = $blog->fresh()->images->first();
    expect($blogImage->image_path)->toContain('blogs/');
});

/**
 * Test image upload validates file type.
 */
test('image upload validates file type', function () {
    $blog = Blog::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf', 100);

    $response = $this->actingAs($this->admin)->postJson("/api/admin/blogs/{$blog->id}/images", [
        'image' => $file,
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['image']);
});

/**
 * Test image upload validates file size.
 */
test('image upload validates file size', function () {
    $blog  = Blog::factory()->create();
    $image = UploadedFile::fake()->image('blog.jpg')->size(6000); // 6MB

    $response = $this->actingAs($this->admin)->postJson("/api/admin/blogs/{$blog->id}/images", [
        'image' => $image,
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['image']);
});

/**
 * Test admin can upload multiple images.
 */
test('admin can upload multiple images', function () {
    $blog   = Blog::factory()->create();
    $image1 = UploadedFile::fake()->image('image1.jpg');
    $image2 = UploadedFile::fake()->image('image2.jpg');
    $image3 = UploadedFile::fake()->image('image3.jpg');

    $response = $this->actingAs($this->admin)->postJson("/api/admin/blogs/{$blog->id}/images/multiple", [
        'images'    => [$image1, $image2, $image3],
        'alt_texts' => ['Alt 1', 'Alt 2', 'Alt 3'],
        'captions'  => ['Caption 1', 'Caption 2', 'Caption 3'],
    ]);

    $response->assertStatus(Response::HTTP_CREATED);

    expect($blog->fresh()->images)->toHaveCount(3);
});

/**
 * Test admin can update blog image.
 */
test('admin can update blog image', function () {
    $blog = Blog::factory()->create();

    $image = $blog->images()->create([
        'image_path' => 'blogs/image.jpg',
        'alt_text'   => 'Original Alt',
        'sort_order' => 0,
    ]);

    $response = $this->actingAs($this->admin)->putJson("/api/admin/blogs/{$blog->id}/images/{$image->id}", [
        'alt_text'   => 'Updated Alt',
        'caption'    => 'New Caption',
        'sort_order' => 5,
    ]);

    $response->assertOk()
        ->assertJsonFragment(['alt_text' => 'Updated Alt']);

    $this->assertDatabaseHas('blog_images', [
        'id'       => $image->id,
        'alt_text' => 'Updated Alt',
        'caption'  => 'New Caption',
    ]);
});

/**
 * Test admin can delete blog image.
 */
test('admin can delete blog image', function () {
    Storage::fake('public');

    $blog = Blog::factory()->create();

    $image = $blog->images()->create([
        'image_path' => 'blogs/test-image.jpg',
        'sort_order' => 0,
    ]);

    Storage::disk('public')->put('blogs/test-image.jpg', 'fake content');

    $response = $this->actingAs($this->admin)->deleteJson("/api/admin/blogs/{$blog->id}/images/{$image->id}");

    $response->assertOk();

    $this->assertDatabaseMissing('blog_images', ['id' => $image->id]);
});

/**
 * Test cannot update image of different blog.
 */
test('cannot update image of different blog', function () {
    $blog1 = Blog::factory()->create();
    $blog2 = Blog::factory()->create();

    $image = $blog1->images()->create([
        'image_path' => 'blogs/image.jpg',
        'sort_order' => 0,
    ]);

    $response = $this->actingAs($this->admin)->putJson("/api/admin/blogs/{$blog2->id}/images/{$image->id}", [
        'alt_text' => 'Updated',
    ]);

    $response->assertStatus(Response::HTTP_NOT_FOUND);
});

/**
 * Test images are ordered by sort_order.
 */
test('images are ordered by sort_order', function () {
    $blog = Blog::factory()->create();

    $blog->images()->create(['image_path' => 'blogs/img3.jpg', 'sort_order' => 2]);
    $blog->images()->create(['image_path' => 'blogs/img1.jpg', 'sort_order' => 0]);
    $blog->images()->create(['image_path' => 'blogs/img2.jpg', 'sort_order' => 1]);

    $response = $this->actingAs($this->admin)->getJson("/api/admin/blogs/{$blog->id}/images");

    $response->assertOk();

    $images = $response->json('data');
    expect($images[0]['sort_order'])->toBe(0);
    expect($images[1]['sort_order'])->toBe(1);
    expect($images[2]['sort_order'])->toBe(2);
});

/**
 * Test multiple images get sequential sort orders.
 */
test('multiple images get sequential sort orders', function () {
    $blog = Blog::factory()->create();

    $blog->images()->create(['image_path' => 'blogs/existing.jpg', 'sort_order' => 0]);

    $image1 = UploadedFile::fake()->image('image1.jpg');
    $image2 = UploadedFile::fake()->image('image2.jpg');

    $response = $this->actingAs($this->admin)->postJson("/api/admin/blogs/{$blog->id}/images/multiple", [
        'images' => [$image1, $image2],
    ]);

    $response->assertStatus(Response::HTTP_CREATED);

    $blog->refresh();
    expect($blog->images()->orderBy('sort_order')->pluck('sort_order')->toArray())->toBe([0, 1, 2]);
});
