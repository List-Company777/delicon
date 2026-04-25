<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('applicant_name', 50);
            $table->unsignedTinyInteger('applicant_age')->nullable();
            $table->string('applicant_email', 255);
            $table->string('applicant_tel', 20)->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['new', 'contacted', 'rejected', 'hired'])->default('new');
            // 外部APIへの送信管理
            $table->string('xml_source', 50)->nullable();
            $table->timestamp('api_sent_at')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'status']);
            $table->index('job_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
