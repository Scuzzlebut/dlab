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
        Schema::create('attachment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attachment_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('staff_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->text('user_agent')->nullable();
            $table->string('ip', 64);
            $table->timestamp('downloaded_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('attachment_logs', function (Blueprint $table) {
            $table->dropForeign(['attachment_id']);
            $table->dropForeign(['staff_id']);
        });

        Schema::dropIfExists('attachment_logs');
    }
};
