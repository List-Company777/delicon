<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE cast_diaries MODIFY COLUMN status ENUM('draft','published','pending') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE cast_diaries MODIFY COLUMN status ENUM('draft','published') NOT NULL DEFAULT 'published'");
    }
};
