<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Cek dan tambahkan kolom inventory_item_id jika belum ada
            if (!Schema::hasColumn('order_items', 'inventory_item_id')) {
                $table->foreignId('inventory_item_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('inventory_items')
                    ->nullOnDelete();
            }
            
            // Cek dan tambahkan kolom branch_id jika belum ada
            if (!Schema::hasColumn('order_items', 'branch_id')) {
                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('inventory_item_id')
                    ->constrained('branches')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'inventory_item_id')) {
                $table->dropForeign(['inventory_item_id']);
                $table->dropColumn('inventory_item_id');
            }
            
            if (Schema::hasColumn('order_items', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
        });
    }
};