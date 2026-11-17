<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class SiswaDashboardController extends Controller
{
    public function index()
    {
        $guru = User::where('role', 'guru')->get();
        $guru->each(function (User $pengguna): void {
            if ($pengguna->foto_profil && ! Storage::disk('public')->exists($pengguna->foto_profil)) {
                $pengguna->setAttribute('foto_profil', null);
            }
        });

        return view('siswa.dashboard', compact('guru'));
    }
}
