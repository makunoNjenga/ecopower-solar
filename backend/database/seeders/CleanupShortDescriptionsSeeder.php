<?php
namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Clean up existing short descriptions that contain HTML tags
 */
class CleanupShortDescriptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::whereNotNull('short_description')->get();

        foreach ($products as $product) {
            $originalShortDescription = $product->short_description;
            $cleanShortDescription    = strip_tags($originalShortDescription);

            // Only update if the content changed (had HTML tags)
            if ($originalShortDescription !== $cleanShortDescription) {
                $product->update(['short_description' => $cleanShortDescription]);
                $this->command->info("Cleaned short description for product: {$product->name}");
            }
        }

        $this->command->info('Short descriptions cleanup completed!');
    }
}
