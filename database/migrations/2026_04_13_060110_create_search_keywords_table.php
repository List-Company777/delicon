<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keyword', 200);
            $table->enum('gender', ['male', 'female', 'business'])->nullable();
            $table->unsignedInteger('search_count')->default(1);
            // new=未判定, mapped=正規化済み, excluded=除外
            $table->enum('normalization_status', ['new', 'mapped', 'excluded'])->default('new');
            $table->timestamps();

            $table->unique(['keyword', 'gender']);
            $table->index(['normalization_status', 'search_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_keywords');
    }
};
