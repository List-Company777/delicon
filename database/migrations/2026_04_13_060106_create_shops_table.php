<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('kana', 100)->nullable();       // フリガナ
            $table->foreignId('genre_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('prefecture_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('station_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address', 200)->nullable();
            $table->string('tel', 20)->nullable();
            $table->string('line_id', 50)->nullable();
            $table->string('logo_image', 255)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            // XML連携
            $table->enum('xml_source', ['upstage', 'cabareuclub', 'manual'])->default('manual');
            $table->string('xml_id', 100)->nullable();     // 外部サービスのID
            $table->boolean('xml_enabled')->default(false); // XMLフラグ
            $table->timestamps();

            $table->index(['genre_id', 'area_id', 'status']);
            $table->unique(['xml_source', 'xml_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
