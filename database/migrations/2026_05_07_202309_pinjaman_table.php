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
        Schema::create('pinjamans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_anggota')->constrained('anggotas');
            $table->string('no_kontrak')->unique();
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_disetujui')->nullable();
            $table->decimal('jumlah_pinjaman', 10, 2);
            $table->integer('tenor');
            $table->decimal('bunga', 5, 2); // Persentase bunga, misalnya 12.50 untuk 12.5%
            $table->enum('jenis_bunga', ['flat', 'anuitas']);
            $table->decimal('total_kewajiban', 10, 2); // (Pokok + Total Bunga)
            $table->enum('status_pinjaman', ["pending", "approved", "rejected", "ongoing", "settled"])->default("pending");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pinjamans');
    }
};
