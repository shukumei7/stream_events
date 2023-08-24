<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Donation>
 */
class DonationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $c = ['USD', 'CAD'];
        return [
            'name'      => fake()->name(),
            'amount'    => rand(1,500),
            'currency'  => $c[rand(0,1)],
            'created_at'=> random_date()
        ];
    }
}
