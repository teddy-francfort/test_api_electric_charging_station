<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('evses', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 36)->unique();
            $table->string('address', 45);
            $table->foreignId('operator_id')->constrained('operators');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('evses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('operator_id');
        });
        Schema::dropIfExists('evses');
    }
};
