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
        Schema::table('staff', function (Blueprint $table) {
            $table->string('code', 50)->unique()->change();
            $table->string('iban', 30)->after('private_email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropUnique('code');
            $table->dropColumn('iban');
        });
    }
};
