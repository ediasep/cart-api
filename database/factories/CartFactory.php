<?php

namespace Database\Factories;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Cart::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $product = \App\Models\Product::factory()->create();

        return [
            'user_id' => \App\Models\User::factory()->create()->id,
            'product_id' => $product->id,
            'quantity' => $this->faker->numberBetween($min = 1, $max = 10),
            'product_version' => 1,
            'product_stock' => $product->stock,
        ];
    }
}
