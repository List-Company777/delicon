<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cast_diary_tokens', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cast_id')->constrained()->cascadeOnDelete();
            $t->string('token', 64)->unique();
            $t->timestamp('expires_at');
            $t->timestamp('created_at')->nullable();
        });
    }
    public function down(): void {
        Schema::dropIfExists('cast_diary_tokens');
    }
};
