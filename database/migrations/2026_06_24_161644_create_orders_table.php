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
        Schema::create('orders', function (Blueprint $table) {
            $table->string('order_id')->primary();
            $table->integer('total_qty');
            $table->integer('total_weight_gr');
            $table->integer('total_returned_qty')->default(0);
            $table->integer('total_diskon')->default(0);
            $table->string('product_categories');
            $table->integer('num_product_categories');
            $table->string('status_pesanan');
            $table->string('alasan_pembatalan')->nullable();
            $table->string('opsi_pengiriman')->nullable();
            $table->string('metode_pembayaran')->nullable();
            $table->string('kota_kabupaten')->nullable();
            $table->string('provinsi')->nullable();
            $table->integer('ongkos_kirim_dibayar_oleh_pembeli')->default(0);
            $table->integer('estimasi_potongan_biaya_pengiriman')->default(0);
            $table->integer('total_pembayaran');
            $table->integer('perkiraan_ongkos_kirim');
            $table->dateTime('waktu_pesanan_dibuat')->nullable();
            $table->string('source_file')->nullable();
            $table->integer('cluster')->nullable();
            $table->string('cluster_label')->nullable();
            $table->integer('is_batal')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
