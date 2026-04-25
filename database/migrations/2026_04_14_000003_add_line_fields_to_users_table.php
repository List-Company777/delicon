<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('line_user_id', 100)->nullable()->unique()->after('email');
            $table->string('line_name', 100)->nullable()->after('line_user_id');
            // メール・パスワードはLINE登録時も後から設定するためnullable化
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['line_user_id']);
            $table->dropColumn(['line_user_id', 'line_name']);
            $table->string('password')->nullable(false)->change();
        });
    }
};
