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
        Schema::create('generated_labels_mmea', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('nomor_po');
            $table->string('nomor_obc');
            $table->string('gol');
            $table->string('produk');
            $table->string('waktu_kerja');
            $table->string('periksa1');
            $table->string('periksa2');
            $table->integer('kemasan')->nullable();
            $table->integer('lbr_kemas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_labels_mmea');
    }
};
