<?php

namespace Database\Factories;

use App\Models\Staff;
use App\Models\Communication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Communication>
 */
class CommunicationFactory extends Factory {
    protected $model = Communication::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition() {
        return [
            'title' => $this->faker->text(30),
            'body' => $this->faker->sentence(100),
            'created_by' => Staff::whereIn('role_id', [2, 3])->inRandomOrder()->first()->id
        ];
    }
}
