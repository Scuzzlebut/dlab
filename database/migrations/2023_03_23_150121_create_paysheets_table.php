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
        Schema::create('paysheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->integer('reference_month');
            $table->integer('reference_year');
            $table->foreignId('created_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamp('downloaded_at')->nullable();
            $table->string('ip', 64)->nullable();
            $table->text('user_agent')->nullable();

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
        Schema::table('paysheets', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::dropIfExists('paysheets');
    }
};
