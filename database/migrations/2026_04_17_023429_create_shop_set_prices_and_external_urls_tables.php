<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_set_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('time_from', 10)->nullable();
            $table->string('time_to', 10)->nullable();
            $table->string('price', 100);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('shop_external_urls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('url_type', 20)->default('website');
            $table->string('url', 500);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 既存の website_url データを移行
        DB::table('shop_details')
            ->whereNotNull('website_url')
            ->where('website_url', '!=', '')
            ->get(['shop_id', 'website_url'])
            ->each(function ($row) {
                DB::table('shop_external_urls')->insert([
                    'shop_id'    => $row->shop_id,
                    'url_type'   => 'website',
                    'url'        => $row->website_url,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_external_urls');
        Schema::dropIfExists('shop_set_prices');
    }
};
