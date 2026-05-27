<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggotas', function (Blueprint $table): void {
            $table->id();
            $table->string('nik', 16)->unique();
            $table->string('nama_anggota', 50);
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P'])->comment('L: Laki-laki, P: Perempuan');
            $table->string('alamat', 100)->nullable();
            $table->string('nomor_hp', 13)->nullable();
            $table->date('tanggal_gabung')->useCurrent();
            $table->enum('status_anggota', ['aktif', 'nonaktif'])->default('aktif');
            $table->string('path_image', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggotas');
    }
};
