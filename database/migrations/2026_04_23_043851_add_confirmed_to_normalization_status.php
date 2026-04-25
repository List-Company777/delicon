<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE search_keywords MODIFY COLUMN normalization_status ENUM('new','mapped','confirmed','excluded') NOT NULL DEFAULT 'new'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE search_keywords MODIFY COLUMN normalization_status ENUM('new','mapped','excluded') NOT NULL DEFAULT 'new'");
    }
};
