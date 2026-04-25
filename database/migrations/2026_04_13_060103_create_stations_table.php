<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prefecture_id')->constrained()->cascadeOnDelete();
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 50);            // 新宿駅
            $table->string('slug', 50)->unique();   // shinjuku-station
            $table->string('line', 50)->nullable(); // 路線名
            $table->timestamps();

            $table->index(['prefecture_id', 'area_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
