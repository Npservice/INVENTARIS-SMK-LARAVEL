<?php

namespace Database\Factories;

use App\Models\KritikSaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KritikSaran>
 */
class KritikSaranFactory extends Factory
{
    protected $model = KritikSaran::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'kritik_saran' => fake()->paragraph(3),
        ];
    }
}
