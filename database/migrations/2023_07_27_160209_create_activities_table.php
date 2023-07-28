<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('activity_type_id')->constrained('activity_types')->onUpdate('cascade')->onDelete('cascade'); //chi esegue la richiesta
            $table->date('day');
            $table->integer('hours')->default(0);
            $table->text('note')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->dropForeign(['project_id']);
            $table->dropForeign(['activity_type_id']);
        });
        Schema::dropIfExists('activities');
    }
};
