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
        Schema::create('generated_labels_personal', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('no_po');
            $table->integer('no_rim');
            $table->integer('jml_lembar');
            $table->string('np_users')->nullable();
            $table->string('potongan');
            $table->dateTime('start')->nullable();
            $table->dateTime('finish')->nullable();
            $table->integer('workstation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_labels_personal');
    }
};
