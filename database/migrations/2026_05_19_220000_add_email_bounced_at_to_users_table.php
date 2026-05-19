<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'email_bounced_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('email_bounced_at')->nullable()->after('email_verified_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_bounced_at');
        });
    }
};
