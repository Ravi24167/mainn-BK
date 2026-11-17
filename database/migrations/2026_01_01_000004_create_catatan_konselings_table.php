<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catatan_konselings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_konseling_id')->constrained('jadwal_konselings')->cascadeOnDelete();

            $table->longText('catatan')->nullable();      // isi cerita/poin penting
            $table->longText('kesimpulan')->nullable();    // kesimpulan akhir sesi
            $table->unsignedSmallInteger('tahun');          // dipakai buat galeri per tahun, contoh 2026

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catatan_konselings');
    }
};
