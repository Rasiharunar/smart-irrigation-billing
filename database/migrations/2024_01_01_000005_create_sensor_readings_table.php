<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pump_id')->constrained();
            $table->foreignId('usage_session_id')->nullable()->constrained();
            $table->decimal('voltage', 8, 2);
            $table->decimal('current', 8, 3);
            $table->decimal('power', 8, 2);
            $table->decimal('energy', 10, 4);
            $table->decimal('frequency', 8, 2);
            $table->decimal('power_factor', 5, 3);
            $table->datetime('recorded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};