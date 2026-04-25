<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shop_extension_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('shop_price_plans')->cascadeOnDelete();
            $table->string('label', 50);
            $table->string('price', 50);
            $table->tinyInteger('sort_order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_extension_prices');
    }
};
