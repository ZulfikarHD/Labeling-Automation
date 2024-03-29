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
        Schema::create('generated_products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('no_po')->unique();
            $table->string('no_obc')->nullable();
            $table->string('type');
            $table->integer('sum_rim');
            $table->integer('start_rim');
            $table->integer('end_rim');
            $table->integer('assigned_team')->nullable();
            $table->integer('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_products');
    }
};
