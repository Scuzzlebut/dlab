<?php

namespace Database\Seeders;

use App\Models\StaffRole;
use App\Models\Communication;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use DB;

class CommunicationSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        //$employees = DB::table('system_plans')->where('group', '2022_v1')->where('name', 'pro')->first();
        $roles = StaffRole::all()->pluck('id')->toArray();

        Communication::factory()->count(30)->create()->each(function ($communication) use ($roles) {

            $newroles = $roles;
            shuffle($newroles);
            $newroles = array_slice($newroles, 1, rand(1, 3));

            foreach ($newroles as $role) {
                DB::table('communication_roles')->insert([
                    'communication_id' => $communication->id,
                    'staff_roles_id' => $role,
                ]);
            }
        });
    }
}
