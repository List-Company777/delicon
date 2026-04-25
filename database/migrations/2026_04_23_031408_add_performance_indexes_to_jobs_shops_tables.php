<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // withinPlanLimit scope: correlated subquery counts jobs by shop_id + search_group + id
        // (already applied in previous partial run, wrapped in existence check)
        Schema::table('jobs', function (Blueprint $table) {
            // Recreate the index that was dropped in the failed previous run
            // (the old index had only search_group+status; jobs.bid_price does not exist)
            $table->index(['search_group', 'status'], 'jobs_search_group_status_index');
        });

        // ORDER BY subquery on shops uses bid_price, budget_balance, main_image for priority scoring
        Schema::table('shops', function (Blueprint $table) {
            $table->index(['bid_price', 'budget_balance'], 'shops_bid_price_budget_balance_index');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex('jobs_shop_id_search_group_id_index');
            $table->dropIndex('jobs_search_group_status_index');
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->dropIndex('shops_bid_price_budget_balance_index');
        });
    }
};
