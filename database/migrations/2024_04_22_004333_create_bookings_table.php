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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('kode_booking')->unique();
            $table->string('nama');
            $table->date('tanggal');
            $table->enum('waktu', ['siang','malam']);
            $table->enum('keterangan', ['halfday','fullday']);
            // $table->string('keterangan');
            $table->foreignId('unit_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->integer('harga');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
