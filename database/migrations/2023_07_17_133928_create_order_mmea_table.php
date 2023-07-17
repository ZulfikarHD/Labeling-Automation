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
        Schema::create('order_mmea', function (Blueprint $table) {
            $table->date('tgl_obc')->nullable();
            $table->string('no_obc')->nullable();
            $table->string('warna')->nullable();
            $table->float('tarif_per_liter')->nullable();
            $table->string('kadar_alkohol')->nullable();
            $table->string('kode_pabrik')->nullable();
            $table->string('nama_pabrik')->nullable();
            $table->string('peruntukan')->nullable();
            $table->string('gol')->nullable();
            $table->string('volume')->nullable();
            $table->date('tgl_jt')->nullable();
            $table->integer('qty_pesan')->nullable();
            $table->integer('rencet')->nullable();
            $table->biginteger('order')->nullable();
            $table->string('kadar_hptl')->nullable();
            $table->integer('gr')->nullable();
            $table->string('perekat')->nullable();
            $table->integer('no_pelat')->nullable();
            $table->integer('tahun')->nullable();
            $table->string('obc_awal')->nullable();
            $table->string('type')->nullable();
            $table->biginteger('sales_doc')->nullable();
            $table->integer('item')->nullable();
            $table->string('material_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_mmea');
    }
};
