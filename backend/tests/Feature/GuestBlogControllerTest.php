<?php

use App\Models\Blog;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = Category::factory()->create();
});

/**
 * Test guest can view published blogs.
 */
test('guest can view published blogs', function () {
    Blog::factory()->count(3)->create(['is_published' => true, 'published_at' => now()->subDay()]);
    Blog::factory()->count(2)->create(['is_published' => false]);

    $response = $this->getJson('/api/blogs');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

/**
 * Test guest cannot view draft blogs.
 */
test('guest cannot view draft blogs', function () {
    Blog::factory()->count(5)->create(['is_published' => false]);

    $response = $this->getJson('/api/blogs');

    $response->assertOk()
        ->assertJsonCount(0, 'data');
});

/**
 * Test guest cannot view future scheduled blogs.
 */
test('guest cannot view future scheduled blogs', function () {
    Blog::factory()->create([
        'is_published' => true,
        'published_at' => now()->addDay(),
    ]);

    $response = $this->getJson('/api/blogs');

    $response->assertOk()
        ->assertJsonCount(0, 'data');
});

/**
 * Test guest can view single published blog by slug.
 */
test('guest can view single published blog by slug', function () {
    $blog = Blog::factory()->create([
        'title'        => 'Solar Energy Guide',
        'slug'         => 'solar-energy-guide',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    $response = $this->getJson("/api/blogs/{$blog->slug}");

    $response->assertOk()
        ->assertJsonFragment(['title' => 'Solar Energy Guide']);
});

/**
 * Test guest cannot view draft blog by slug.
 */
test('guest cannot view draft blog by slug', function () {
    $blog = Blog::factory()->create([
        'is_published' => false,
        'published_at' => null,
    ]);

    $response = $this->getJson("/api/blogs/{$blog->slug}");

    $response->assertStatus(Response::HTTP_NOT_FOUND);
});

/**
 * Test viewing blog increments view count.
 */
test('viewing blog increments view count', function () {
    $blog = Blog::factory()->create([
        'is_published' => true,
        'published_at' => now()->subDay(),
        'views'        => 10,
    ]);

    $response = $this->getJson("/api/blogs/{$blog->slug}");

    $response->assertOk();

    expect($blog->fresh()->views)->toBe(11);
});

/**
 * Test guest can filter blogs by category.
 */
test('guest can filter blogs by category', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    Blog::factory()->count(2)->create([
        'category_id'  => $category1->id,
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);
    Blog::factory()->count(3)->create([
        'category_id'  => $category2->id,
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    $response = $this->getJson("/api/blogs?category_id={$category1->id}");

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

/**
 * Test guest can search blogs.
 */
test('guest can search blogs', function () {
    Blog::factory()->create([
        'title'        => 'Solar Panel Installation Guide',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);
    Blog::factory()->create([
        'title'        => 'Battery Storage Systems',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    $response = $this->getJson('/api/blogs?search=solar');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['title' => 'Solar Panel Installation Guide']);
});

/**
 * Test blog includes related products.
 */
test('blog includes related products', function () {
    $blog = Blog::factory()->create([
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    $products = Product::factory()->count(3)->create();
    $blog->products()->attach($products->pluck('id'));

    $response = $this->getJson("/api/blogs/{$blog->slug}");

    $response->assertOk()
        ->assertJsonCount(3, 'data.products');
});

/**
 * Test blog includes author information.
 */
test('blog includes author information', function () {
    $author = User::factory()->create(['name' => 'John Doe']);

    $blog = Blog::factory()->create([
        'author_id'    => $author->id,
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    $response = $this->getJson("/api/blogs/{$blog->slug}");

    $response->assertOk()
        ->assertJsonPath('data.author.name', 'John Doe');
});

/**
 * Test blog includes images.
 */
test('blog includes images', function () {
    $blog = Blog::factory()->create([
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    $blog->images()->create([
        'image_path' => 'blogs/image1.jpg',
        'alt_text'   => 'Test Image',
        'sort_order' => 0,
    ]);

    $response = $this->getJson("/api/blogs/{$blog->slug}");

    $response->assertOk()
        ->assertJsonCount(1, 'data.images');
});

/**
 * Test blog pagination works.
 */
test('blog pagination works', function () {
    Blog::factory()->count(15)->create([
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    $response = $this->getJson('/api/blogs?per_page=10');

    $response->assertOk()
        ->assertJsonCount(10, 'data')
        ->assertJsonStructure([
            'data',
            'meta' => ['current_page', 'last_page', 'total'],
        ]);

    expect($response->json('meta.total'))->toBe(15);
});

/**
 * Test blogs are sorted by published_at by default.
 */
test('blogs are sorted by published_at by default', function () {
    $blog1 = Blog::factory()->create([
        'is_published' => true,
        'published_at' => now()->subDays(3),
        'title'        => 'Old Blog',
    ]);

    $blog2 = Blog::factory()->create([
        'is_published' => true,
        'published_at' => now()->subDay(),
        'title'        => 'New Blog',
    ]);

    $response = $this->getJson('/api/blogs');

    $response->assertOk();

    $firstBlog = $response->json('data.0');
    expect($firstBlog['title'])->toBe('New Blog');
});

/**
 * Test invalid blog slug returns 404.
 */
test('invalid blog slug returns 404', function () {
    $response = $this->getJson('/api/blogs/non-existent-slug');

    $response->assertStatus(Response::HTTP_NOT_FOUND);
});

/**
 * Test blog search is case insensitive.
 */
test('blog search is case insensitive', function () {
    Blog::factory()->create([
        'title'        => 'Solar Panel Guide',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    $response = $this->getJson('/api/blogs?search=SOLAR');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});
