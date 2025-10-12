<?php
namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds the product_images table with placeholder images for products
     *
     * @return void
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Get all product IDs
        $products = DB::table('products')->select('id', 'slug')->get();

        $imageData    = [];
        $imageCounter = 1;

        foreach ($products as $product) {
            // Add 2-3 images per product
            $numImages = rand(2, 3);

            for ($i = 0; $i < $numImages; $i++) {
                $imageData[] = [
                    'product_id' => $product->id,
                    'path'       => 'products/' . $product->slug . '-' . ($i + 1) . '.jpg',
                    'filename'   => $product->slug . '-' . ($i + 1) . '.jpg',
                    'alt_text'   => ucfirst(str_replace('-', ' ', $product->slug)) . ' - Image ' . ($i + 1),
                    'sort_order' => $i,
                    // First image is primary
                    'is_primary' => $i === 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $imageCounter++;
            }
        }

        DB::table('product_images')->insert($imageData);

        $this->command->info('Product images seeded successfully! ' . count($imageData) . ' images added for ' . $products->count() . ' products.');
        $this->command->warn('Note: These are placeholder image paths. Upload actual images to storage/app/public/products/');
    }
}
