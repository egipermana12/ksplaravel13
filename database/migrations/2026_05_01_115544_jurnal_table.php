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
        Schema::create('jurnals', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->index();
            $table->unsignedBigInteger('refid_transaksi')->nullable()->index()->comment('ID dari table transaksi terkait, bisa dari pembelian, penjualan, dll');
            $table->string('ref_type_transaksi')->nullable()->comment('Tipe transaksi terkait, misal: pembelian, penjualan, dll');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnals');
    }
};
