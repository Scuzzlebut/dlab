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
        // ruoli utente : dipendente - responsabile - admin
        Schema::create('staff_roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_name', 30);
            $table->string('role_slug', 30);

            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('staff_roles')->insert([
            [
                'role_name' => 'Dipendente',
                'role_slug' => 'employee',
                'created_at' => Carbon\Carbon::now(),
            ],
            [
                'role_name' => 'Responsabile',
                'role_slug' => 'manager',
                'created_at' => Carbon\Carbon::now(),
            ],
            [
                'role_name' => 'Admin',
                'role_slug' => 'admin',
                'created_at' => Carbon\Carbon::now(),
            ]
        ]);

        // tipo utente (usi futuri) : interno, esterno, titolare
        Schema::create('staff_types', function (Blueprint $table) {
            $table->id();
            $table->string('type_name', 30);

            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('staff_types')->insert([
            [
                'type_name' => 'Personale interno',
                'created_at' => Carbon\Carbon::now(),
            ],
            [
                'type_name' => 'Personale esterno',
                'created_at' => Carbon\Carbon::now(),
            ],
            [
                'type_name' => 'Titolare',
                'created_at' => Carbon\Carbon::now(),
            ]
        ]);

        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->string('surname', 30);
            $table->string('name', 30);
            $table->string('taxcode', 16)->nullable();
            $table->string('address', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postcode', 10)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('private_email', 50)->nullable()->unique();
            $table->string('gender', 10)->nullable();
            $table->string('birthplace', 100)->nullable();
            $table->date('birthday')->nullable();
            $table->date('date_start');
            $table->date('date_end')->nullable();
            $table->time('morning_starttime');
            $table->time('morning_endtime');
            $table->time('afternoon_starttime');
            $table->time('afternoon_endtime');
            $table->boolean('notifications_on')->default(0);
            $table->foreignId('user_id')->nullable(); // ->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('created_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('staff_roles')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('type_id')->constrained('staff_types')->onUpdate('cascade')->onDelete('cascade');
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // tabella relazioni dipendenti-responsabili
        Schema::create('staff_managers', function (Blueprint $table) {
            $table->foreignId('staff_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('manager_id')->constrained('staff')->onUpdate('cascade')->onDelete('cascade');
        });

        DB::table('staff')->insert([
            [
                'code' => '00001',
                'name' => 'Admin',
                'surname' => 'DLab',
                'date_start' => now()->format('Y-m-d'),
                'morning_starttime' => '09:00',
                'morning_endtime' => '13:00',
                'afternoon_starttime' => '14:00',
                'afternoon_endtime' => '18:00',
                'notifications_on' => 1,
                'user_id' => 1,
                'created_by' => 1,
                'role_id' => 3,
                'type_id' => 1,
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('staff_managers');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('staff_roles');
        Schema::dropIfExists('staff_types');
    }
};
