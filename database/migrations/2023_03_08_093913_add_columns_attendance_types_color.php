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
        Schema::table('attendance_types', function (Blueprint $table) {
            $table->string('color', 20)->after('type_name')->nullable();
        });

        DB::table('attendance_types')->where('id', 1)->update(['color' => '#dbadff']);
        DB::table('attendance_types')->where('id', 2)->update(['color' => '#fbd75b']);
        DB::table('attendance_types')->where('id', 3)->update(['color' => '#5484ed']);
        DB::table('attendance_types')->where('id', 4)->update(['color' => '#dc2127']);
        DB::table('attendance_types')->where('id', 5)->update(['color' => '#51b749']);
        DB::table('attendance_types')->where('id', 6)->update(['color' => '#7C4DFF']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('attendance_types', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
