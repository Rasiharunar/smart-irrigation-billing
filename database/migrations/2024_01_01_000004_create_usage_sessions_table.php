<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('pump_id')->constrained();
            $table->decimal('quota_kwh', 8, 4);
            $table->decimal('actual_kwh', 8, 4)->default(0);
            $table->enum('status', ['active', 'completed', 'stopped', 'exceeded'])->default('active');
            $table->datetime('started_at');
            $table->datetime('ended_at')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->decimal('tariff_rate', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_sessions');
    }
};