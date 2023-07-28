<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivityTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('activity_types')->insert([
            [
                'title' => 'Bug Fix',
            ],
            [
                'title' => 'Nuova Feature',
            ],
            [
                'title' => 'Assistenza',
            ],
            [
                'title' => 'Organizzazione',
            ],
            [
                'title' => 'Progettazione',
            ],
            [
                'title' => 'Refactoring',
            ],
            [
                'title' => 'Altro',
            ],
        ]);
    }
}
