<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEcomFieldsToInventoryItems extends Migration
{
    public function up()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->decimal('ecom_price', 15, 2)->nullable()->after('purchase_price');
            $table->boolean('is_listed')->default(false)->after('ecom_price');
            $table->timestamp('listed_at')->nullable()->after('is_listed');
        });
    }

    public function down()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['ecom_price', 'is_listed', 'listed_at']);
        });
    }
}