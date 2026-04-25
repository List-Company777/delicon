<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_type_id')->constrained();
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('prefecture_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('station_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->unsignedInteger('hourly_wage_min')->nullable(); // 最低時給
            $table->unsignedInteger('hourly_wage_max')->nullable(); // 最高時給
            $table->string('working_hours', 100)->nullable();       // 勤務時間帯
            // job_typeのtarget_genderを上書きする場合のみセット（nullはマスタ通り）
            $table->enum('gender_override', ['male', 'female'])->nullable();
            // male/female/both を検索時に使うため保存（job_type + gender_override から導出）
            $table->enum('search_group', ['male', 'female', 'both'])->default('female');
            $table->string('image_path', 255)->nullable(); // 有料掲載・XML経由のみ
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active');
            // ホットリンクオプション
            $table->boolean('is_hotlink')->default(false);
            $table->string('hotlink_url', 500)->nullable();
            $table->unsignedInteger('bid_price')->default(10); // 入札単価（円）
            // XML連携
            $table->enum('xml_source', ['upstage', 'cabareuclub', 'manual'])->default('manual');
            $table->string('xml_id', 100)->nullable();
            $table->boolean('xml_enabled')->default(false);
            $table->unsignedInteger('click_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['search_group', 'status', 'bid_price']);
            $table->index(['area_id', 'search_group', 'status']);
            $table->index(['shop_id', 'status']);
            $table->unique(['xml_source', 'xml_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
