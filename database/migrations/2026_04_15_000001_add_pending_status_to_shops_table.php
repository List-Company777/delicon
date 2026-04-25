<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE shops MODIFY COLUMN status ENUM('active','inactive','pending') NOT NULL DEFAULT 'inactive'");
    }

    public function down(): void
    {
        DB::statement("UPDATE shops SET status = 'inactive' WHERE status = 'pending'");
        DB::statement("ALTER TABLE shops MODIFY COLUMN status ENUM('active','inactive') NOT NULL DEFAULT 'inactive'");
    }
};
