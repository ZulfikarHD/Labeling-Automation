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
        Schema::create('specification_mmea', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('no_po');
            $table->string('no_obc');
            $table->string('produk')->default('MMEA');
            $table->integer('desain');
            $table->integer('jml_lbr');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specification_mmea');
    }
};
