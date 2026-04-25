<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prefecture_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);           // 新宿
            $table->string('slug', 50)->unique();  // shinjuku（URL用）
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('prefecture_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
