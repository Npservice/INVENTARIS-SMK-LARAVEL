<?php

namespace Database\Factories;

use App\Models\Pendanaan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pendanaan>
 */
class PendanaanFactory extends Factory
{
    protected $model = Pendanaan::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->randomElement(['Bantuan', 'Mandiri', 'BOS', 'Lain-Lain']),
        ];
    }
}
