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
        // comunicazioni
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->string('title', 50);
            $table->text('body')->nullable();
            $table->foreignId('created_by')->references('id')->on('staff')->onUpdate('cascade')->onDelete('cascade');

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
        Schema::dropIfExists('communications');
    }
};
