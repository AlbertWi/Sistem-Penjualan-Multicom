<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('receiver_name')->after('order_number');
            $table->string('receiver_phone')->after('receiver_name');

            $table->text('shipping_address')->after('receiver_phone');
            $table->string('province')->after('shipping_address');
            $table->string('city')->after('province');
            $table->string('district')->after('city');
            $table->string('postal_code')->after('district');

            $table->decimal('shipping_cost', 12, 0)->default(0)->after('total_amount');
            $table->string('shipping_service')->nullable()->after('shipping_cost');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'receiver_name',
                'receiver_phone',
                'shipping_address',
                'province',
                'city',
                'district',
                'postal_code',
                'shipping_cost',
                'shipping_service'
            ]);
        });
    }
};
