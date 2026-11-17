<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foto_konselings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catatan_konseling_id')->constrained('catatan_konselings')->cascadeOnDelete();
            $table->string('path'); // path file foto di storage
            $table->unsignedSmallInteger('tahun'); // duplikat dari catatan biar query galeri per tahun cepat
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_konselings');
    }
};
