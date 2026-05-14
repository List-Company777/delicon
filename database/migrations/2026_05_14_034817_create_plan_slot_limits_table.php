<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_slot_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prefecture_id')->constrained('prefectures')->cascadeOnDelete();
            $table->unsignedTinyInteger('max_slots')->default(5);
            $table->timestamps();

            $table->unique('prefecture_id');
        });

        // 東京(13)・大阪(66)は10枠、それ以外は5枠（デフォルト）
        // 東京・大阪のみ明示的に INSERT（他はコードでデフォルト5を返す）
        DB::table('plan_slot_limits')->insert([
            ['prefecture_id' => 13, 'max_slots' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['prefecture_id' => 66, 'max_slots' => 10, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_slot_limits');
    }
};
