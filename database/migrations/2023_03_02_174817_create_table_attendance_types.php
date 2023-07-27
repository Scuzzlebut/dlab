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
        Schema::create('attendance_types', function (Blueprint $table) {
            $table->id();
            $table->string('type_name', 30);

            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('attendance_types')->insert([
            [
                'type_name' => 'Assenza ingiustificata',
                'created_at' => Carbon\Carbon::now(),
            ],
            [
                'type_name' => 'Chiusura aziendale',
                'created_at' => Carbon\Carbon::now(),
            ],
            [
                'type_name' => 'Ferie',
                'created_at' => Carbon\Carbon::now(),
            ],
            [
                'type_name' => 'Malattia',
                'created_at' => Carbon\Carbon::now(),
            ],
            [
                'type_name' => 'Permesso',
                'created_at' => Carbon\Carbon::now(),
            ],
            [
                'type_name' => 'Straordinario',
                'created_at' => Carbon\Carbon::now(),
            ],
        ]);

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('type_id')->after('hours')->constrained('attendance_types')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('type_id');
        });

        Schema::dropIfExists('attendance_types');
    }
};
