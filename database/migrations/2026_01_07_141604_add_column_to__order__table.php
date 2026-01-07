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
        Schema::table('orders', function (Blueprint $table) {
            // Stock pickup tracking
            if (!Schema::hasColumn('orders', 'stock_picked_at')) {
                $table->timestamp('stock_picked_at')->nullable()->after('paid_at');
            }
            
            if (!Schema::hasColumn('orders', 'stock_picked_by')) {
                $table->foreignId('stock_picked_by')
                    ->nullable()
                    ->after('stock_picked_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
            
            // Completion tracking
            if (!Schema::hasColumn('orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('stock_picked_by');
            }
            
            if (!Schema::hasColumn('orders', 'completed_by')) {
                $table->foreignId('completed_by')
                    ->nullable()
                    ->after('completed_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
            
            // Cancellation tracking
            if (!Schema::hasColumn('orders', 'cancelled_by')) {
                $table->foreignId('cancelled_by')
                    ->nullable()
                    ->after('cancellation_reason')
                    ->constrained('users')
                    ->nullOnDelete();
            }
            
            // Branch ID (if single-branch order)
            if (!Schema::hasColumn('orders', 'branch_id')) {
                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('customer_id')
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
        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'stock_picked_at',
                'stock_picked_by',
                'completed_at',
                'completed_by',
                'cancelled_by',
                'branch_id'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    // Drop foreign key jika ada
                    if (in_array($column, ['stock_picked_by', 'completed_by', 'cancelled_by', 'branch_id'])) {
                        $table->dropForeign([$column]);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};