<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_alert_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 32)->unique();
            $table->enum('gender', ['female', 'male', 'both']);
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_type_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_alert_tokens');
    }
};
