<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_alert_tokens', function (Blueprint $table) {
            $table->boolean('daily_pay_ok')->default(false)->after('job_type_id');
            $table->boolean('inexperienced_ok')->default(false)->after('daily_pay_ok');
            $table->boolean('arubaito')->default(false)->after('inexperienced_ok');
        });
    }

    public function down(): void
    {
        Schema::table('web_alert_tokens', function (Blueprint $table) {
            $table->dropColumn(['daily_pay_ok', 'inexperienced_ok', 'arubaito']);
        });
    }
};
