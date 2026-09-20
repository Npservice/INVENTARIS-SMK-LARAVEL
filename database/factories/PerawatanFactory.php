<?php

namespace Database\Factories;

use App\Models\Inventaris;
use App\Models\Perawatan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Perawatan>
 */
class PerawatanFactory extends Factory
{
    protected $model = Perawatan::class;

    public function definition(): array
    {
        return [
            'inventaris_id' => Inventaris::factory(),
            'user_id' => User::factory(),
            'tanggal_perawatan' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'status_perawatan' => fake()->randomElement(['Proses', 'Selesai']),
            'biaya' => fake()->randomFloat(2, 10000, 2000000),
            'keterangan' => fake()->optional()->sentence(6),
        ];
    }
}
