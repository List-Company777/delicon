<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 100);              // 会社名
            $table->string('contact_name', 50)->nullable();   // 担当者名
            $table->string('email', 255)->unique();
            $table->string('tel', 20)->nullable();
            $table->string('referral_code', 20)->unique();    // 紹介URL用コード
            $table->decimal('commission_rate', 5, 4)->default(0.1000); // 手数料率（0.1000=10%）
            $table->text('bank_info')->nullable();            // 振込先情報（自由記述）
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();                // admin メモ
            $table->timestamps();
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->foreignId('partner_id')->nullable()->after('status')
                  ->constrained()->nullOnDelete();
        });

        Schema::create('partner_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('base_amount');           // 売上金額（円）
            $table->decimal('rate', 5, 4);                   // 適用レート（スナップショット）
            $table->unsignedInteger('commission_amount');     // 手数料金額（円）
            $table->string('description', 200)->nullable();  // 内容メモ（例：2026年5月分掲載料）
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['partner_id', 'status']);
            $table->index(['shop_id']);
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropForeign(['partner_id']);
            $table->dropColumn('partner_id');
        });
        Schema::dropIfExists('partner_commissions');
        Schema::dropIfExists('partners');
    }
};
