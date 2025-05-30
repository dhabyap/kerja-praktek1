<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('kode_invoice')->unique();
            $table->date('tanggal');
            $table->string('keterangan')->nullable();
            $table->string('type');
            // $table->foreignId('booking_id')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('unit_id')->nullable();
            $table->integer('harga');
            $table->enum('tipe_pembayaran', ['cash','transfer'])->default('cash');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
