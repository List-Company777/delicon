<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cast_tel_clicks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cast_id');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('cast_id')->references('id')->on('casts')->onDelete('cascade');
            $table->index(['cast_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cast_tel_clicks');
    }
};
