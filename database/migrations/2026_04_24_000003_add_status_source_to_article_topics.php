<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('article_topics', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved'])->default('pending')->after('gender');
            $table->enum('source', ['ai', 'admin'])->default('admin')->after('status');
            $table->string('ai_reason', 100)->nullable()->after('source');
        });

        // 既存テーマはすでに手動承認済み扱いにする
        DB::table('article_topics')->update(['status' => 'approved', 'source' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('article_topics', function (Blueprint $table) {
            $table->dropColumn(['status', 'source', 'ai_reason']);
        });
    }
};
