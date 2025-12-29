<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sale_accessories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                ->constrained('sales')
                ->cascadeOnDelete();

            $table->foreignId('accessory_id')
                ->constrained('accessories')
                ->cascadeOnDelete();

            $table->foreignId('purchase_accessory_id')
                ->constrained('purchase_accessories')
                ->cascadeOnDelete();

            $table->integer('qty');
            $table->decimal('price', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_accessories');
    }
};
