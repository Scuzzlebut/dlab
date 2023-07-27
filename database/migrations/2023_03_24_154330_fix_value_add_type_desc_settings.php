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
        Schema::table('settings', function (Blueprint $table) {
            $table->text('field_value')->change();
            $table->string('type', 20)->after('field_value')->nullable();
            $table->string('description', 255)->after('field_value')->nullable();
        });

        DB::table('settings')->insert([
            [
                'field_name' => 'days_holidays',
                'field_value' => json_encode([
                    'Capodanno'                 => '01-01',
                    'Epifania'                  => '01-06',
                    'Liberazione'               => '04-25',
                    'Festa Lavoratori'          => '05-01',
                    'Festa della Repubblica'    => '06-02',
                    'Ferragosto'                => '08-15',
                    'Tutti Santi'               => '11-01',
                    'Immacolata'                => '12-08',
                    'Natale'                    => '12-25',
                    'Santo Stefano'             => '12-26',
                ]),
                'type' => 'text',
                'description' => 'Festività utili per il calcolo delle ore (Pasqua e Pasquetta sono calcolate e aggiunte in automatico)',
                'created_at' => Carbon\Carbon::now(),
            ],
        ]);

        DB::table('settings')->where('field_name', 'morning_starttime')->update(['type' => 'time', 'description' => 'Orario aziendale di inizio fascia oraria mattiniera']);
        DB::table('settings')->where('field_name', 'morning_endtime')->update(['type' => 'time', 'description' => 'Orario aziendale di fine fascia oraria mattiniera']);
        DB::table('settings')->where('field_name', 'afternoon_starttime')->update(['type' => 'time', 'description' => 'Orario aziendale di inizio fascia oraria pomeridiana']);
        DB::table('settings')->where('field_name', 'afternoon_endtime')->update(['type' => 'time', 'description' => 'Orario aziendale di fine fascia oraria pomeridiana']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::table('settings')->where('field_name', 'days_holidays')->delete();

        Schema::table('settings', function (Blueprint $table) {
            $table->string('field_value', 255)->change();
            $table->dropColumn('type');
            $table->dropColumn('description');
        });
    }
};
