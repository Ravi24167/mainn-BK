<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_konselings', function (Blueprint $table) {
            $table->id();

            $table->string('nama_siswa');
            $table->enum('kelas', ['10', '11', '12']);
            $table->enum('jurusan', ['KKR', 'RPL', 'DKV', 'TKJ', 'PSPT']);

            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kategori_masalah_id')->constrained('kategori_masalah');

            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');

            $table->enum('jenis_kedatangan', ['individu', 'kelompok']);
            $table->text('anggota_kelompok')->nullable();

            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');

            $table->timestamps();

            $table->unique(['guru_id', 'tanggal', 'jam_mulai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_konselings');
    }
};
