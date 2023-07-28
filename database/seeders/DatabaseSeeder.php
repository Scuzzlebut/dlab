<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Staff;
use App\Models\Attendance;
use App\Models\Communication;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run() {
        Staff::factory(50)->create();
        Communication::factory(25)->create();
        Attendance::factory(30)->create();
        $this->call(ActivityTypeSeeder::class);
        $this->call(ProjectSeeder::class);
    }
}
