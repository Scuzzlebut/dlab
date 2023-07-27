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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('filename', 100);
            $table->string('filetype', 50);
            $table->string('filepath', 200);
            $table->integer('filesize')->default(0);
            $table->foreignId('model_id')->nullable()->index();
            $table->string('model_type', 50)->nullable();

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
        Schema::dropIfExists('attachments');
    }
};
