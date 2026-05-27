<?php

namespace Database\Factories;

use App\Models\Anggota;
use App\Models\Simpanan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Simpanan>
 */
class SimpananFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_anggota' => Anggota::factory(),
            'jenis_simpanan' => $this->faker->randomElement(['wajib', 'sukarela', 'pokok']),
            'nominal' => $this->faker->numberBetween(100000, 5000000),
            'tanggal_setor' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'bukti_setor' => $this->faker->optional()->sentence(10),
            'ket' => $this->faker->optional()->sentence(),
        ];
    }
}
