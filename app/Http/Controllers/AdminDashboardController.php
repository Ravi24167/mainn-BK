<?php

namespace App\Http\Controllers;

use App\Models\JadwalKonseling;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $jadwal = JadwalKonseling::with(['guru', 'kategoriMasalah', 'catatan'])
            ->orderByDesc('tanggal')
            ->paginate(20);

        return view('admin.dashboard', compact('jadwal'));
    }

    // PDF rekap: nama siswa, jam booking awal, dan ulasan/catatan guru
    public function exportPdf(Request $request)
    {
        $query = JadwalKonseling::with(['guru', 'catatan'])->orderBy('tanggal');

        if ($request->filled('dari') && $request->filled('sampai')) {
            $query->whereBetween('tanggal', [$request->dari, $request->sampai]);
        }

        $jadwal = $query->get();

        $pdf = Pdf::loadView('admin.rekap-pdf', compact('jadwal'))->setPaper('a4');
        return $pdf->stream('rekap-konseling.pdf');
    }
}
