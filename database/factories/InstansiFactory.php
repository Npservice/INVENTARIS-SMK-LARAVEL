<?php

namespace Database\Factories;

use App\Models\Instansi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Instansi>
 */
class InstansiFactory extends Factory
{
    protected $model = Instansi::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->randomElement(['Putra', 'Putri']),
        ];
    }
}
