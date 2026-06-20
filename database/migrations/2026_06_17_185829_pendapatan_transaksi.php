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
        Schema::create('pendapatan_transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kategori')->constrained('kategori_pendapatan');
            $table->date('tanggal_transaksi');
            $table->decimal('nominal', 15, 2);
            $table->text('keterangan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendapatan_transaksi');
    }
};
