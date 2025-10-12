<?php

use App\Models\Blog;
use App\Models\Category;
use App\Models\Product;
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
 * Test guest cannot access admin blog endpoints.
 */
test('guest cannot access admin blog endpoints', function () {
    $response = $this->getJson('/api/admin/blogs');

    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});

/**
 * Test regular user cannot access admin blog endpoints.
 */
test('regular user cannot access admin blog endpoints', function () {
    $response = $this->actingAs($this->user)->getJson('/api/admin/blogs');

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

/**
 * Test admin can view all blogs.
 */
test('admin can view all blogs including drafts', function () {
    Blog::factory()->count(3)->create(['is_published' => true, 'published_at' => now()]);
    Blog::factory()->count(2)->create(['is_published' => false]);

    $response = $this->actingAs($this->admin)->getJson('/api/admin/blogs');

    $response->assertOk()
        ->assertJsonCount(5, 'data');
});

/**
 * Test admin can filter blogs by published status.
 */
test('admin can filter blogs by published status', function () {
    Blog::factory()->count(3)->create(['is_published' => true, 'published_at' => now()]);
    Blog::factory()->count(2)->create(['is_published' => false]);

    $response = $this->actingAs($this->admin)->getJson('/api/admin/blogs?is_published=1');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

/**
 * Test admin can filter blogs by category.
 */
test('admin can filter blogs by category', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    Blog::factory()->count(2)->create(['category_id' => $category1->id, 'is_published' => true, 'published_at' => now()]);
    Blog::factory()->count(3)->create(['category_id' => $category2->id, 'is_published' => true, 'published_at' => now()]);

    $response = $this->actingAs($this->admin)->getJson("/api/admin/blogs?category_id={$category1->id}");

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

/**
 * Test admin can search blogs.
 */
test('admin can search blogs', function () {
    Blog::factory()->create(['title' => 'Solar Panel Guide', 'is_published' => true, 'published_at' => now()]);
    Blog::factory()->create(['title' => 'Battery Storage Tips', 'is_published' => true, 'published_at' => now()]);
    Blog::factory()->create(['title' => 'Wind Energy Basics', 'is_published' => true, 'published_at' => now()]);

    $response = $this->actingAs($this->admin)->getJson('/api/admin/blogs?search=solar');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['title' => 'Solar Panel Guide']);
});

/**
 * Test admin can view single blog.
 */
test('admin can view single blog', function () {
    $blog = Blog::factory()->create(['is_published' => true, 'published_at' => now()]);

    $response = $this->actingAs($this->admin)->getJson("/api/admin/blogs/{$blog->id}");

    $response->assertOk()
        ->assertJsonFragment(['title' => $blog->title]);
});

/**
 * Test admin can create blog.
 */
test('admin can create blog', function () {
    $blogData = [
        'title'            => 'New Solar Blog',
        'content'          => '<p>This is test content about solar energy.</p>',
        'excerpt'          => 'Test excerpt',
        'category_id'      => $this->category->id,
        'is_published'     => true,
        'meta_title'       => 'Solar Blog Meta',
        'meta_description' => 'Meta description',
        'meta_keywords'    => 'solar, energy',
    ];

    $response = $this->actingAs($this->admin)->postJson('/api/admin/blogs', $blogData);

    $response->assertStatus(Response::HTTP_CREATED)
        ->assertJsonFragment(['title' => 'New Solar Blog']);

    $this->assertDatabaseHas('blogs', [
        'title'     => 'New Solar Blog',
        'slug'      => 'new-solar-blog',
        'author_id' => $this->admin->id,
    ]);
});

/**
 * Test blog creation validates required fields.
 */
test('blog creation validates required fields', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/admin/blogs', []);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['title', 'content']);
});

/**
 * Test admin can update blog.
 */
test('admin can update blog', function () {
    $blog = Blog::factory()->create(['author_id' => $this->admin->id]);

    $updateData = [
        'title'   => 'Updated Blog Title',
        'content' => '<p>Updated content.</p>',
    ];

    $response = $this->actingAs($this->admin)->putJson("/api/admin/blogs/{$blog->id}", $updateData);

    $response->assertOk()
        ->assertJsonFragment(['title' => 'Updated Blog Title']);

    $this->assertDatabaseHas('blogs', [
        'id'    => $blog->id,
        'title' => 'Updated Blog Title',
        'slug'  => 'updated-blog-title',
    ]);
});

/**
 * Test admin can delete blog.
 */
test('admin can delete blog', function () {
    $blog = Blog::factory()->create();

    $response = $this->actingAs($this->admin)->deleteJson("/api/admin/blogs/{$blog->id}");

    $response->assertOk();

    $this->assertSoftDeleted('blogs', ['id' => $blog->id]);
});

/**
 * Test admin can attach products to blog.
 */
test('admin can attach products to blog', function () {
    $blog     = Blog::factory()->create(['author_id' => $this->admin->id]);
    $products = Product::factory()->count(3)->create();

    $blogData = [
        'title'       => $blog->title,
        'content'     => $blog->content,
        'product_ids' => $products->pluck('id')->toArray(),
    ];

    $response = $this->actingAs($this->admin)->putJson("/api/admin/blogs/{$blog->id}", $blogData);

    $response->assertOk();

    $this->assertDatabaseHas('blog_product', [
        'blog_id'    => $blog->id,
        'product_id' => $products[0]->id,
    ]);

    expect($blog->fresh()->products)->toHaveCount(3);
});

/**
 * Test admin can get blog statistics.
 */
test('admin can get blog statistics', function () {
    Blog::factory()->count(5)->create(['is_published' => true, 'published_at' => now(), 'views' => 100]);
    Blog::factory()->count(3)->create(['is_published' => false]);

    $response = $this->actingAs($this->admin)->getJson('/api/admin/blogs/statistics');

    $response->assertOk()
        ->assertJsonStructure([
            'total_blogs',
            'published_blogs',
            'draft_blogs',
            'total_views',
            'top_blogs',
            'recent_blogs',
        ]);

    expect($response->json('total_blogs'))->toBe(8);
    expect($response->json('published_blogs'))->toBe(5);
    expect($response->json('draft_blogs'))->toBe(3);
});

/**
 * Test published_at is set when publishing blog.
 */
test('published_at is set when publishing blog', function () {
    $blogData = [
        'title'        => 'New Blog',
        'content'      => '<p>Content</p>',
        'is_published' => true,
    ];

    $response = $this->actingAs($this->admin)->postJson('/api/admin/blogs', $blogData);

    $response->assertStatus(Response::HTTP_CREATED);

    $blog = Blog::where('title', 'New Blog')->first();
    expect($blog->published_at)->not->toBeNull();
});

/**
 * Test slug is automatically generated from title.
 */
test('slug is automatically generated from title', function () {
    $blogData = [
        'title'   => 'How to Install Solar Panels',
        'content' => '<p>Content</p>',
    ];

    $response = $this->actingAs($this->admin)->postJson('/api/admin/blogs', $blogData);

    $response->assertStatus(Response::HTTP_CREATED);

    $this->assertDatabaseHas('blogs', [
        'title' => 'How to Install Solar Panels',
        'slug'  => 'how-to-install-solar-panels',
    ]);
});

/**
 * Test blog pagination works correctly.
 */
test('blog pagination works correctly', function () {
    Blog::factory()->count(20)->create(['is_published' => true, 'published_at' => now()]);

    $response = $this->actingAs($this->admin)->getJson('/api/admin/blogs?per_page=10');

    $response->assertOk()
        ->assertJsonCount(10, 'data')
        ->assertJsonStructure([
            'data',
            'meta' => ['current_page', 'last_page', 'total'],
        ]);
});
