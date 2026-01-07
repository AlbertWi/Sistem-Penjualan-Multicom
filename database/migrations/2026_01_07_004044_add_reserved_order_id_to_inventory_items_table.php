<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            DB::statement("
            ALTER TABLE inventory_items 
            MODIFY status ENUM(
                'in_stock',
                'reserved',
                'sold',
                'transfered'
            ) NOT NULL DEFAULT 'in_stock'
        ");
        });
        Schema::table('inventory_items', function (Blueprint $table) {
        $table->unsignedBigInteger('reserved_order_id')
              ->nullable()
              ->after('branch_id');

        $table->foreign('reserved_order_id')
              ->references('id')
              ->on('orders')
              ->nullOnDelete();
    });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    Schema::table('inventory_items', function (Blueprint $table) {
        $table->dropForeign(['reserved_order_id']);
        $table->dropColumn('reserved_order_id');
    });
    }
};
