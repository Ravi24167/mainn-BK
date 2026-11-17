<?php

namespace Database\Seeders;

use App\Models\KategoriMasalah;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RuangBkSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(['email' => 'admin@sekolah.sch.id'], ['name' => 'Admin BK', 'password' => Hash::make('password'), 'role' => 'admin']);
        User::firstOrCreate(['email' => 'maya@sekolah.sch.id'], ['name' => 'Bu Maya', 'password' => Hash::make('password'), 'role' => 'guru']);
        User::firstOrCreate(['email' => 'noya@sekolah.sch.id'], ['name' => 'Bu Noya', 'password' => Hash::make('password'), 'role' => 'guru']);
        foreach (['Keluarga', 'Sosial', 'Pembelajaran', 'Akademik', 'Kesehatan Mental', 'Lainnya'] as $nama) {
            KategoriMasalah::firstOrCreate(['nama' => $nama]);
        }
    }
}
