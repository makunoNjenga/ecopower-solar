<?php
namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds the categories table with sample product categories
     *
     * @return void
     */
    public function run(): void
    {
        $now = Carbon::now();

        $categories = [
            [
                'name'        => 'Solar Panels',
                'slug'        => 'solar-panels',
                'description' => 'High-efficiency solar panels for residential and commercial use',
                'image'       => null,
                'parent_id'   => null,
                'sort_order'  => 1,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Inverters',
                'slug'        => 'inverters',
                'description' => 'Power inverters for solar energy systems',
                'image'       => null,
                'parent_id'   => null,
                'sort_order'  => 2,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Batteries',
                'slug'        => 'batteries',
                'description' => 'Energy storage solutions and battery systems',
                'image'       => null,
                'parent_id'   => null,
                'sort_order'  => 3,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Charge Controllers',
                'slug'        => 'charge-controllers',
                'description' => 'Solar charge controllers and regulators',
                'image'       => null,
                'parent_id'   => null,
                'sort_order'  => 4,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Mounting Systems',
                'slug'        => 'mounting-systems',
                'description' => 'Racking and mounting hardware for solar installations',
                'image'       => null,
                'parent_id'   => null,
                'sort_order'  => 5,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Cables & Connectors',
                'slug'        => 'cables-connectors',
                'description' => 'Solar cables, connectors, and wiring accessories',
                'image'       => null,
                'parent_id'   => null,
                'sort_order'  => 6,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Monitoring Systems',
                'slug'        => 'monitoring-systems',
                'description' => 'Energy monitoring and management systems',
                'image'       => null,
                'parent_id'   => null,
                'sort_order'  => 7,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Complete Kits',
                'slug'        => 'complete-kits',
                'description' => 'All-in-one solar power system kits',
                'image'       => null,
                'parent_id'   => null,
                'sort_order'  => 8,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        DB::table('categories')->insert($categories);

        $this->command->info('Categories seeded successfully!');
    }
}
