<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('projects')->insert([
            [
                'title' => 'TermoGest',
            ],
            [
                'title' => 'OsteoEasy',
            ],
            [
                'title' => 'Associami',
            ],
            [
                'title' => 'D.Lab',
            ],
            [
                'title' => 'Altro',
            ]
        ]);
    }
}
