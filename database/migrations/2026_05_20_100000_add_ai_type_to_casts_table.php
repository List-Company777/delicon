<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('casts', function (Blueprint $table) {
            $table->unsignedTinyInteger('ai_type_id')->nullable()->after('type_id');
            $table->timestamp('type_ai_processed_at')->nullable()->after('charm_ai_processed_at');
        });
    }

    public function down(): void
    {
        Schema::table('casts', function (Blueprint $table) {
            $table->dropColumn(['ai_type_id', 'type_ai_processed_at']);
        });
    }
};
