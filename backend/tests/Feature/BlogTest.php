<?php

use App\Models\Blog;
use App\Models\BlogImage;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Test blog has author relationship.
 */
test('blog has author relationship', function () {
    $author = User::factory()->create();
    $blog   = Blog::factory()->create(['author_id' => $author->id]);

    expect($blog->author)->toBeInstanceOf(User::class);
    expect($blog->author->id)->toBe($author->id);
});

/**
 * Test blog has category relationship.
 */
test('blog has category relationship', function () {
    $category = Category::factory()->create();
    $blog     = Blog::factory()->create(['category_id' => $category->id]);

    expect($blog->category)->toBeInstanceOf(Category::class);
    expect($blog->category->id)->toBe($category->id);
});

/**
 * Test blog has images relationship.
 */
test('blog has images relationship', function () {
    $blog = Blog::factory()->create();

    $blog->images()->create([
        'image_path' => 'blogs/image1.jpg',
        'sort_order' => 0,
    ]);

    $blog->images()->create([
        'image_path' => 'blogs/image2.jpg',
        'sort_order' => 1,
    ]);

    expect($blog->images)->toHaveCount(2);
    expect($blog->images->first())->toBeInstanceOf(BlogImage::class);
});

/**
 * Test blog has products relationship.
 */
test('blog has products relationship', function () {
    $blog     = Blog::factory()->create();
    $products = Product::factory()->count(3)->create();

    $blog->products()->attach($products->pluck('id'));

    expect($blog->products)->toHaveCount(3);
    expect($blog->products->first())->toBeInstanceOf(Product::class);
});

/**
 * Test blog slug is generated from title.
 */
test('blog slug is generated from title', function () {
    $blog = Blog::factory()->create(['title' => 'How to Install Solar Panels']);

    expect($blog->slug)->toBe('how-to-install-solar-panels');
});

/**
 * Test blog increment views works.
 */
test('blog increment views works', function () {
    $blog = Blog::factory()->create(['views' => 10]);

    $blog->incrementViews();

    expect($blog->fresh()->views)->toBe(11);
});

/**
 * Test blog published scope.
 */
test('blog published scope filters correctly', function () {
    Blog::factory()->count(3)->create([
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    Blog::factory()->count(2)->create([
        'is_published' => false,
        'published_at' => null,
    ]);

    Blog::factory()->create([
        'is_published' => true,
        'published_at' => now()->addDay(),
    ]);

    $publishedBlogs = Blog::published()->get();

    expect($publishedBlogs)->toHaveCount(3);
});

/**
 * Test blog images are ordered by sort_order.
 */
test('blog images are ordered by sort_order', function () {
    $blog = Blog::factory()->create();

    $blog->images()->create(['image_path' => 'blogs/img3.jpg', 'sort_order' => 2]);
    $blog->images()->create(['image_path' => 'blogs/img1.jpg', 'sort_order' => 0]);
    $blog->images()->create(['image_path' => 'blogs/img2.jpg', 'sort_order' => 1]);

    $images = $blog->fresh()->images;

    expect($images[0]->sort_order)->toBe(0);
    expect($images[1]->sort_order)->toBe(1);
    expect($images[2]->sort_order)->toBe(2);
});

/**
 * Test blog products are ordered by sort_order.
 */
test('blog products are ordered by sort_order', function () {
    $blog     = Blog::factory()->create();
    $products = Product::factory()->count(3)->create();

    $blog->products()->attach([
        $products[0]->id => ['sort_order' => 2],
        $products[1]->id => ['sort_order' => 0],
        $products[2]->id => ['sort_order' => 1],
    ]);

    $orderedProducts = $blog->fresh()->products;

    expect($orderedProducts[0]->pivot->sort_order)->toBe(0);
    expect($orderedProducts[1]->pivot->sort_order)->toBe(1);
    expect($orderedProducts[2]->pivot->sort_order)->toBe(2);
});

/**
 * Test blog soft deletes.
 */
test('blog soft deletes', function () {
    $blog = Blog::factory()->create();
    $id   = $blog->id;

    $blog->delete();

    $this->assertSoftDeleted('blogs', ['id' => $id]);

    expect(Blog::find($id))->toBeNull();
    expect(Blog::withTrashed()->find($id))->not->toBeNull();
});

/**
 * Test blog factory creates valid blog.
 */
test('blog factory creates valid blog', function () {
    $blog = Blog::factory()->create();

    expect($blog)->toBeInstanceOf(Blog::class);
    expect($blog->title)->not->toBeEmpty();
    expect($blog->slug)->not->toBeEmpty();
    expect($blog->content)->not->toBeEmpty();
    expect($blog->author_id)->not->toBeNull();
});

/**
 * Test blog with null category.
 */
test('blog can have null category', function () {
    $blog = Blog::factory()->create(['category_id' => null]);

    expect($blog->category)->toBeNull();
    expect($blog)->toBeInstanceOf(Blog::class);
});

/**
 * Test blog casts.
 */
test('blog casts work correctly', function () {
    $blog = Blog::factory()->create([
        'is_published' => true,
        'published_at' => now(),
        'views'        => 100,
    ]);

    expect($blog->is_published)->toBeTrue();
    expect($blog->published_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    expect($blog->views)->toBeInt();
});

/**
 * Test deleting blog deletes related images.
 */
test('deleting blog deletes related images', function () {
    $blog = Blog::factory()->create();

    $image1 = $blog->images()->create(['image_path' => 'blogs/img1.jpg', 'sort_order' => 0]);
    $image2 = $blog->images()->create(['image_path' => 'blogs/img2.jpg', 'sort_order' => 1]);

    $blog->delete();

    $this->assertDatabaseMissing('blog_images', ['id' => $image1->id]);
    $this->assertDatabaseMissing('blog_images', ['id' => $image2->id]);
});

/**
 * Test deleting blog detaches products.
 */
test('deleting blog detaches products', function () {
    $blog     = Blog::factory()->create();
    $products = Product::factory()->count(3)->create();

    $blog->products()->attach($products->pluck('id'));

    $blog->delete();

    $this->assertDatabaseMissing('blog_product', ['blog_id' => $blog->id]);
});
