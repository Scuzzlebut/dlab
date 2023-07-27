<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('staff', function (Blueprint $table) {
            $table->time('morning_starttime')->nullable()->change();
            $table->time('morning_endtime')->nullable()->change();
            $table->time('afternoon_starttime')->nullable()->change();
            $table->time('afternoon_endtime')->nullable()->change();
        });

        DB::table('settings')->insert([
            [
                'field_name' => 'morning_starttime',
                'field_value' => '09:00:00',
                'created_at' => Carbon\Carbon::now(),
            ],
            [
                'field_name' => 'morning_endtime',
                'field_value' => '13:00:00',
                'created_at' => Carbon\Carbon::now(),
            ],
            [
                'field_name' => 'afternoon_starttime',
                'field_value' => '14:00:00',
                'created_at' => Carbon\Carbon::now(),
            ],
            [
                'field_name' => 'afternoon_endtime',
                'field_value' => '18:00:00',
                'created_at' => Carbon\Carbon::now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::table('settings')->where('field_name', 'morning_starttime')->delete();
        DB::table('settings')->where('field_name', 'morning_endtime')->delete();
        DB::table('settings')->where('field_name', 'afternoon_starttime')->delete();
        DB::table('settings')->where('field_name', 'afternoon_endtime')->delete();

        Schema::table('staff', function (Blueprint $table) {
            $table->time('morning_starttime')->nullable(false)->change();
            $table->time('morning_endtime')->nullable(false)->change();
            $table->time('afternoon_starttime')->nullable(false)->change();
            $table->time('afternoon_endtime')->nullable(false)->change();
        });
    }
};
