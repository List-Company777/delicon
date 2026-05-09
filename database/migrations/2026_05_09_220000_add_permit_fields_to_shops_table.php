<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('shops', function (Blueprint $table) {
            $table->enum('permit_type', ['uploaded', 'not_required'])->nullable()->after('status');
            $table->string('permit_document_path', 500)->nullable()->after('permit_type');
        });
    }
    public function down(): void {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['permit_type', 'permit_document_path']);
        });
    }
};
