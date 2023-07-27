<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

use App\Models\{Staff, User};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Staff>
 */
class StaffFactory extends Factory {
    use \App\Traits\GlobalSettings;
    protected $model = Staff::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition() {
        return [
            'code' => function () {
                return Staff::formatCode($this->faker->unique()->numberBetween(1, 999));
            },
            'name' => $this->faker->firstName,
            'surname' => $this->faker->lastName,
            'taxcode' => $this->faker->taxId(),
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'postcode' => $this->faker->postcode,
            'state' => $this->faker->stateAbbr,
            'phone_number' => $this->faker->phoneNumber,
            'private_email' => $this->faker->unique()->safeEmail,
            'iban' => $this->faker->iban('IT'),
            'date_start' => $this->faker->date,
            //'date_end' => $this->faker->date,
            'morning_starttime' => $this->get_global_setting('morning_starttime'),
            'morning_endtime' => $this->get_global_setting('morning_endtime'),
            'afternoon_starttime' => $this->get_global_setting('afternoon_starttime'),
            'afternoon_endtime' => $this->get_global_setting('afternoon_endtime'),
            'notifications_on' => $this->faker->numberBetween(0, 1),
            'user_id' => $this->faker->boolean($this->faker->numberBetween(1, 100)) ? User::factory() : null,
            'created_by' => User::inRandomOrder()->first()->id,
            'role_id' => $this->faker->numberBetween(1, 3),
            'type_id' => $this->faker->numberBetween(1, 3),
            'note' => $this->faker->sentence(20)
        ];
    }
}
