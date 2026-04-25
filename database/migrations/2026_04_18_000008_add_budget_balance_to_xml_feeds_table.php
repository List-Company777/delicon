<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('xml_feeds', function (Blueprint $table) {
            $table->unsignedBigInteger('budget_balance')->nullable()->after('last_imported_at')
                  ->comment('null=無制限、0以上=残高あり（残高0で無料扱い）');
        });
    }

    public function down(): void
    {
        Schema::table('xml_feeds', function (Blueprint $table) {
            $table->dropColumn('budget_balance');
        });
    }
};
