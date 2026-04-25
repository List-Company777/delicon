<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('area_address_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('keyword')->unique();          // マッチに使うキーワード
            $table->string('example_address')->nullable(); // 記録時の住所例（文脈用）
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_address_mappings');
    }
};
