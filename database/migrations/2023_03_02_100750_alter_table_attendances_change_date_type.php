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
        Schema::table('attendances', function (Blueprint $table) {
            $table->datetime('date_start')->change();
            $table->datetime('date_end')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('attendances', function (Blueprint $table) {
            $table->date('date_start')->change();
            $table->date('date_end')->change();
        });
    }
};
