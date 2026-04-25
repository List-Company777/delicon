<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Botの会話ステートを一時保存（TTL付き）
        Schema::create('job_alert_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('line_user_id', 50)->unique();
            $table->string('step', 20); // gender | area | job_type
            $table->string('gender', 10)->nullable();
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_alert_sessions');
    }
};
