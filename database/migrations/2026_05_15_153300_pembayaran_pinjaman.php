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
        Schema::create('pembayaran_pinjaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pinjaman')->constrained('pinjamans');
            // Jika pembayaran spesifik untuk angsuran tertentu
            $table->foreignId('id_jadwal')->nullable()->constrained('pinjaman_jadwal');
            $table->date('tanggal_bayar');
            $table->decimal('nominal_bayar', 15, 2);
            $table->decimal('denda', 15, 2)->default(0);
            $table->string('bukti_bayar')->nullable();
            $table->string('metode_bayar'); // transfer, cash, potong gaji
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_pinjaman');
    }
};
