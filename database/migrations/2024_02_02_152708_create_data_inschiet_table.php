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
        Schema::create('data_inschiet', function (Blueprint $table) {
            $table->bigInteger('no_po')->unique();
            $table->integer('inschiet')->default(0);
            $table->string('np_kiri')->nullable();
            $table->string('np_kanan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_inschiet');
    }
};
