<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cast_shift_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cast_id');
            $table->date('work_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('note', 100)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->foreign('cast_id')->references('id')->on('casts')->onDelete('cascade');
            $table->unique(['cast_id', 'work_date']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('cast_shift_requests');
    }
};
