<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 300)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->boolean('is_fraud')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['shop_id', 'created_at']);
            $table->index(['ip', 'shop_id', 'created_at']); // 重複IP検出用
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_access_logs');
    }
};
