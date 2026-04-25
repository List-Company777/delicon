<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notices', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->text('body');
            // 送信対象：all=全オーナー / active=掲載中店舗 / inactive=非公開店舗
            $table->enum('target', ['all', 'active', 'inactive'])->default('all');
            $table->enum('status', ['draft', 'sent'])->default('draft');
            $table->unsignedInteger('sent_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notices');
    }
};
