<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {

            // TAMBAH KOLOM SPEC HP
            $table->integer('ram')->after('name');
            $table->integer('rom')->after('ram');
            $table->integer('baterai')->after('rom');
            $table->decimal('ukuran_layar', 4, 2)->after('baterai');
            $table->integer('masa_garansi')->after('ukuran_layar');
            $table->string('resolusi_kamera')->after('masa_garansi');
            $table->tinyInteger('jumlah_slot_sim')->after('resolusi_kamera');

            // HAPUS KOLOM SPECIFICATION
            if (Schema::hasColumn('products', 'specification')) {
                $table->dropColumn('specification');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            // BALIKKAN KOLOM SPECIFICATION
            $table->text('specification')->nullable();

            // HAPUS KOLOM SPEC HP
            $table->dropColumn([
                'ram',
                'rom',
                'baterai',
                'ukuran_layar',
                'masa_garansi',
                'resolusi_kamera',
                'jumlah_slot_sim',
            ]);
        });
    }
};
