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
        Schema::create('job_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            // view=詳細ページPV / click=ホットリンククリック
            $table->enum('type', ['view', 'click']);
            $table->string('ip', 45)->nullable();         // IPv6対応
            $table->string('user_agent', 300)->nullable();
            $table->string('referrer', 500)->nullable();
            // 不正判定フラグ（後から更新可）
            $table->boolean('is_fraud')->default(false);
            $table->timestamp('created_at')->useCurrent(); // updated_at 不要

            $table->index(['job_id', 'type', 'created_at']); // 集計・請求クエリ用
            $table->index(['ip', 'job_id', 'created_at']);    // 重複IP検出用
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_access_logs');
    }
};
