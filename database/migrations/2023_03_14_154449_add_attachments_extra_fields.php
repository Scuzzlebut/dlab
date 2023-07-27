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
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('extra_category', 50)->nullable();
            $table->text('extra_info')->nullable(); // JSON
            $table->string('thumbnail_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropColumn('extra_category');
            $table->dropColumn('extra_info');
            $table->dropColumn('thumbnail_path');
        });
    }
};
