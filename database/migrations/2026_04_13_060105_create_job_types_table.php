<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);             // キャスト、黒服、ホールなど
            $table->string('slug', 50)->unique();    // cast, kurofuku, hall
            // male=男性向け, female=女性向け, both=両方の求人一覧に表示
            $table->enum('target_gender', ['male', 'female', 'both'])->default('female');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_types');
    }
};
