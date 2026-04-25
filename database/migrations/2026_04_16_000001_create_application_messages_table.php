<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // reply_token を applications に追加
        Schema::table('applications', function (Blueprint $table) {
            $table->uuid('reply_token')->unique()->after('status');
        });

        // メッセージスレッド
        Schema::create('application_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->enum('sender', ['shop', 'applicant']);
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_messages');
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('reply_token');
        });
    }
};
