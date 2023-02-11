<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cdrs', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 36)->unique();
            $table->timestamp('start_datetime');
            $table->timestamp('end_datetime');
            $table->integer('total_energy')->nullable();
            $table->unsignedInteger('total_cost')->nullable();
            $table->foreignId('evse_id')->constrained('evses');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cdrs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('evse_id');
        });
        Schema::dropIfExists('cdrs');
    }
};
