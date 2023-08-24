<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MerchSale>
 */
class MerchSaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'      => fake()->name(),
            'item_name' => fake()->word(),
            'amount'    => rand(1,10),
            'price'     => rand(100,500) / 100,
            'created_at'=> strtotime('-'.rand(0,90).' days')
        ];
    }
}
