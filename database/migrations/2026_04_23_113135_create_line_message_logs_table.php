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
        Schema::create('line_message_logs', function (Blueprint $table) {
            $table->id();
            // job_alert: 求人アラート / shop_notify: 応募通知 / alive_check: 生存確認 / inactivated: 非公開通知
            $table->string('type', 30);
            $table->string('line_user_id', 50);
            $table->timestamp('sent_at')->useCurrent();

            $table->index('line_user_id');
            $table->index('sent_at'); // 当月集計用
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('line_message_logs');
    }
};
