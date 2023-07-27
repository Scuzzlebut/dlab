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
            $table->foreignId('accepted_by')->after('note')->nullable()->constrained('staff')->onUpdate('cascade')->onDelete('cascade'); //chi ha validato nel caso di più responsabili
            $table->timestamp('accepted_datetime')->after('note')->nullable(); //data della validazione
            $table->boolean('accepted')->after('note')->default(0); //esito validazione - 0 rifiutata, 1 approvata
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['accepted_by']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('accepted');
            $table->dropColumn('accepted_datetime');
            $table->dropColumn('accepted_by');
        });
    }
};
