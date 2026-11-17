<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // hanya admin & guru yang punya akun login. Siswa tidak perlu akun.
            $table->enum('role', ['admin', 'guru'])->default('guru');

            // foto profil guru (Bu Maya / Bu Noya) yang tampil di dashboard siswa
            $table->string('foto_profil')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
