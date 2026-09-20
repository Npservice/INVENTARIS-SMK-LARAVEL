<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => Str::substr(fake()->name(), 0, 20),
            'username' => Str::substr(fake()->unique()->userName(), 0, 15),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 1,
            'status' => 'aktif',
            'remember_token' => Str::random(10),
        ];
    }
}
