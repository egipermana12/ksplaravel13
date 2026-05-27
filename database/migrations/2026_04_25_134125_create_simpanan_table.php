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
        Schema::create('simpanans', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_anggota');
            $table->enum('jenis_simpanan', ["wajib", "pokok", "sukarela"])->default("wajib");
            $table->decimal('nominal', 10, 2)->default(0);
            $table->string('bukti_setor')->nullable();
            $table->date('tanggal_setor')->useCurrent();
            $table->string('ket')->nullable();
            $table->index('id_anggota');
            $table->index('jenis_simpanan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simpanans');
    }
};
