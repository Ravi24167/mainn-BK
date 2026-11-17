<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\GuruDashboardController;
use App\Http\Controllers\JadwalKonselingController;
use App\Http\Controllers\SiswaDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('siswa.dashboard'));

// --- LOGIN (hanya untuk admin & guru, siswa tidak butuh akun) ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// --- SISWA (publik, tanpa login/akun) ---
Route::prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');
    Route::get('/jadwal/booking', [JadwalKonselingController::class, 'create'])->name('jadwal.create');
    Route::get('/jadwal/slot', [JadwalKonselingController::class, 'cekSlot'])->name('jadwal.slot');
    Route::post('/jadwal/booking', [JadwalKonselingController::class, 'store'])->name('jadwal.store');
    Route::get('/jadwal/cek', [JadwalKonselingController::class, 'cekStatus'])->name('jadwal.cek');
});

// --- GURU (Bu Maya / Bu Noya) - wajib login ---
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
    Route::post('/jadwal/{jadwal}/approve', [GuruDashboardController::class, 'approve'])->name('jadwal.approve');
    Route::post('/jadwal/{jadwal}/reject', [GuruDashboardController::class, 'reject'])->name('jadwal.reject');
    Route::get('/konseling/{jadwal}', [GuruDashboardController::class, 'show'])->name('konseling.show');
    Route::post('/konseling/{jadwal}/catatan', [GuruDashboardController::class, 'simpanCatatan'])->name('konseling.catatan');
    Route::post('/konseling/{jadwal}/alasan', [GuruDashboardController::class, 'simpanAlasan'])->name('konseling.alasan');
    Route::post('/konseling/{jadwal}/foto', [GuruDashboardController::class, 'uploadFoto'])->name('konseling.foto');
    Route::get('/konseling/{jadwal}/pdf', [GuruDashboardController::class, 'exportPdf'])->name('konseling.pdf');
    Route::get('/galeri', [GuruDashboardController::class, 'galeri'])->name('galeri');
    Route::get('/galeri/foto/{foto}', [GuruDashboardController::class, 'fotoShow'])->name('galeri.foto');
    Route::delete('/foto/{foto}', [GuruDashboardController::class, 'hapusFoto'])->name('foto.hapus');
    Route::get('/ketersediaan', [GuruDashboardController::class, 'ketersediaan'])->name('ketersediaan.index');
    Route::post('/ketersediaan', [GuruDashboardController::class, 'simpanKetersediaan'])->name('ketersediaan.store');
    Route::delete('/ketersediaan/{ketersediaan}', [GuruDashboardController::class, 'hapusKetersediaan'])->name('ketersediaan.hapus');
    Route::get('/siswa/{nama}', [GuruDashboardController::class, 'riwayatSiswa'])->name('siswa.riwayat');
    Route::get('/profil', [GuruDashboardController::class, 'profil'])->name('profil');
    Route::post('/profil', [GuruDashboardController::class, 'simpanProfil'])->name('profil.simpan');
});

// --- ADMIN - wajib login ---
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/rekap/pdf', [AdminDashboardController::class, 'exportPdf'])->name('rekap.pdf');
});
