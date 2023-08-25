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
        $items = ['Plushie', 'Shirt', 'Mug', 'Key-chain', 'Magnet'];
        return [
            'name'      => fake()->name(),
            'item_name' => $items[rand(0, count($items) - 1)],
            'amount'    => rand(1,10),
            'price'     => rand(100,500) / 100,
            'created_at'=> random_date()
        ];
    }
}
