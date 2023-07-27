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
        Schema::create('communication_roles', function (Blueprint $table) {
            $table->foreignId('communication_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('staff_roles_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->primary(array('communication_id', 'staff_roles_id'));
        });

        Schema::create('communication_staff', function (Blueprint $table) {
            $table->foreignId('communication_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('staff_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->timestamp('date_read');
            $table->primary(array('communication_id', 'staff_id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('communication_roles');
        Schema::dropIfExists('communication_staff');
    }
};
