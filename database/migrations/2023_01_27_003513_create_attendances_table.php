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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->references('id')->on('staff')->onUpdate('cascade')->onDelete('cascade'); //chi esegue la richiesta
            $table->foreignId('staff_id')->constrained('staff')->onUpdate('cascade')->onDelete('cascade'); //il dipendente interessato
            $table->date('date_start');
            $table->date('date_end');
            $table->integer('hours')->default(0);
            $table->string('sick_note', 20)->nullable();
            $table->text('note', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['applicant_id']);
            $table->dropForeign(['staff_id']);
        });

        Schema::dropIfExists('attendances');
    }
};
