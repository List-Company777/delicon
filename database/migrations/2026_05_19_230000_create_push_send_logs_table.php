<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_send_logs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedInteger('sent');
            $table->unsignedInteger('failed');
            $table->timestamp('sent_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_send_logs');
    }
};
