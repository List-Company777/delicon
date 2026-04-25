<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keyword_normalizations', function (Blueprint $table) {
            $table->id();
            $table->string('keyword', 200);                              // 検索ワード（例: 新宿 キャバ）
            $table->enum('gender', ['male', 'female', 'business'])->nullable();      // 対象性別
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_type_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['keyword', 'gender']);
            $table->index(['is_active', 'area_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keyword_normalizations');
    }
};
