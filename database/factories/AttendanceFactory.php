<?php

namespace Database\Factories;

use App\Models\Staff;
use Illuminate\Support\Carbon;
use App\Traits\AttendanceFunctions;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Request>
 */
class AttendanceFactory extends Factory {
    use AttendanceFunctions;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $hours = [9, 10, 11, 12, 13, 14, 15, 16, 17, 18];
    protected $mins = [0, 15, 30, 45];

    public function definition() {
        $hour = $this->hours[random_int(0, count($this->hours) - 1)];
        $min = $this->mins[random_int(0, count($this->mins) - 1)];
        $random_day = random_int(0, 14);
        $staffid = Staff::inRandomOrder()->first()->id;
        $startdate = $this->faker->dateTimeThisMonth->format(sprintf("Y-m-d %02d:%02d:00", $hour, $min));
        $enddate = (clone Carbon::parse($startdate))->addDays($random_day)->format(sprintf("Y-m-d %02d:%02d:00", ($random_day == 0) ? ($hour + 1) : $hour, $min));
        $hours = $this->calcAttendanceHours(Carbon::parse($startdate), Carbon::parse($enddate), $staffid);
        $typeid = $this->faker->numberBetween(1, 5);
        $accepted_by = $this->faker->boolean($this->faker->numberBetween(1, 100)) ? Staff::whereIn('role_id', [3])->inRandomOrder()->first()->id : null;

        return [
            'applicant_id' => $staffid,
            'staff_id' => $staffid,
            'date_start' => $startdate,
            'date_end' => $enddate,
            'hours' => $hours,
            'type_id' => $typeid,
            'sick_note' => ($typeid == 4) ? $this->faker->text(10) : '',
            'note' => $this->faker->boolean($this->faker->numberBetween(1, 100)) ? $this->faker->text(20) : '',
            'accepted_by' => $accepted_by,
            'accepted_datetime' => (!is_null($accepted_by)) ? $this->faker->dateTimeThisMonth : null,
            'accepted' => ($accepted_by) ? 1 : 0,
        ];
    }
}
