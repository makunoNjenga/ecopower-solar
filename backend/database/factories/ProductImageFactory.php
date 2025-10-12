<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductImage>
 */
class ProductImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'path'       => 'products/' . $this->faker->uuid() . '.jpg',
            'filename'   => $this->faker->uuid() . '.jpg',
            'alt_text'   => $this->faker->sentence(),
            'sort_order' => $this->faker->numberBetween(0, 10),
            'is_primary' => false,
        ];
    }

    /**
     * Indicate that the image is primary.
     *
     * @return Factory
     */
    public function primary(): Factory
    {
        return $this->state(fn(array $attributes) => [
            'is_primary' => true,
        ]);
    }
}
