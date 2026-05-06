<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cast_deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cast_id')->constrained()->cascadeOnDelete();
            $table->string('requester_name', 50);
            $table->string('requester_email', 100);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'processed'])->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cast_deletion_requests');
    }
};
