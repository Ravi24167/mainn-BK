<?php

namespace App\Http\Controllers;

use App\Models\CatatanKonseling;
use App\Models\FotoKonseling;
use App\Models\JadwalKonseling;
use App\Models\JadwalTersedia;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf; // composer require barryvdh/laravel-dompdf

class GuruDashboardController extends Controller
{
    // Halaman utama: browsing per bulan (tab), dikelompokkan per tanggal
    public function index(Request $request)
    {
        $tahun = (int) $request->query('tahun', now()->year);
        $bulan = (int) $request->query('bulan', now()->month);

        $tahunTersedia = JadwalKonseling::where('guru_id', auth()->id())
            ->selectRaw("CAST(strftime('%Y', tanggal) AS INTEGER) as thn")
            ->distinct()->orderByDesc('thn')->pluck('thn');
        if ($tahunTersedia->isEmpty()) {
            $tahunTersedia = collect([now()->year]);
        }

        $namaBulan = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $jadwal = JadwalKonseling::where('guru_id', auth()->id())
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->with('kategoriMasalah')
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        $jadwalPerTanggal = $jadwal->groupBy('tanggal');
        $jumlahMenunggu = $jadwal->where('status', 'menunggu')->count();
        $jumlahDisetujui = $jadwal->where('status', 'disetujui')->count();

        // data buat chart kategori masalah, ikut bulan & tahun yang lagi dibuka
        $rekapKategori = JadwalKonseling::where('guru_id', auth()->id())
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->join('kategori_masalah', 'kategori_masalah.id', '=', 'jadwal_konselings.kategori_masalah_id')
            ->selectRaw('kategori_masalah.nama, count(*) as jumlah')
            ->groupBy('kategori_masalah.nama')
            ->pluck('jumlah', 'nama');

        return view('guru.dashboard', compact(
            'jadwalPerTanggal',
            'tahun',
            'bulan',
            'namaBulan',
            'tahunTersedia',
            'rekapKategori',
            'jumlahMenunggu',
            'jumlahDisetujui'
        ));
    }

    // Guru menyetujui / menolak jadwal yang masuk
    public function approve(JadwalKonseling $jadwal)
    {
        $this->authorizeGuru($jadwal);
        $jadwal->update(['status' => 'disetujui']);
        return back()->with('sukses', 'Jadwal disetujui.');
    }

    public function reject(Request $request, JadwalKonseling $jadwal)
    {
        $this->authorizeGuru($jadwal);
        $jadwal->update([
            'status' => 'ditolak',
            'alasan_ditolak' => $request->input('alasan'),
        ]);
        return back()->with('sukses', 'Jadwal ditolak.');
    }

    // Detail 1 siswa: form catatan + kesimpulan + upload foto
    public function show(JadwalKonseling $jadwal)
    {
        $this->authorizeGuru($jadwal);
        $jadwal->load('kategoriMasalah', 'catatan.foto');
        return view('guru.detail-konseling', compact('jadwal'));
    }

    public function simpanCatatan(Request $request, JadwalKonseling $jadwal)
    {
        $this->authorizeGuru($jadwal);

        $data = $request->validate([
            'catatan' => 'nullable|string',
        ]);

        $tahun = (int) date('Y', strtotime($jadwal->tanggal));

        $catatan = CatatanKonseling::updateOrCreate(
            ['jadwal_konseling_id' => $jadwal->id],
            ['catatan' => $data['catatan'] ?? null, 'tahun' => $tahun]
        );

        return back()->with('sukses', 'Catatan tersimpan.');
    }

    public function uploadFoto(Request $request, JadwalKonseling $jadwal)
    {
        $this->authorizeGuru($jadwal);

        $request->validate(['foto' => 'required|image|max:20480']);

        $tahun = (int) date('Y', strtotime($jadwal->tanggal));

        $catatan = CatatanKonseling::firstOrCreate(
            ['jadwal_konseling_id' => $jadwal->id],
            ['tahun' => $tahun]
        );

        $path = $request->file('foto')->store("galeri-konseling/{$tahun}", 'private');

        FotoKonseling::create([
            'catatan_konseling_id' => $catatan->id,
            'path' => $path,
            'tahun' => $tahun,
        ]);

        return back()->with('sukses', 'Foto ditambahkan.');
    }

    // Galeri privat, browsing per tahun & bulan (tab), hanya guru yang login yang bisa akses
    public function galeri(Request $request)
    {
        $tahun = (int) $request->query('tahun', now()->year);
        $bulan = (int) $request->query('bulan', now()->month);

        $tahunTersedia = CatatanKonseling::selectRaw('distinct tahun')->orderByDesc('tahun')->pluck('tahun');
        if ($tahunTersedia->isEmpty()) {
            $tahunTersedia = collect([now()->year]);
        }

        $namaBulan = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $foto = FotoKonseling::where('tahun', $tahun)
            ->whereHas('catatan.jadwal', fn ($q) => $q->where('guru_id', auth()->id())
                ->whereMonth('tanggal', $bulan))
            ->with('catatan.jadwal')
            ->get();

        return view('guru.galeri', compact('foto', 'tahun', 'bulan', 'namaBulan', 'tahunTersedia'));
    }

    // Serve foto privat, cuma bisa diakses guru pemilik sesi konseling itu
    public function fotoShow(FotoKonseling $foto)
    {
        abort_unless($foto->catatan->jadwal->guru_id === auth()->id(), 403);

        return Storage::disk('private')->response($foto->path);
    }

    // Hapus foto yang salah upload
    public function hapusFoto(FotoKonseling $foto)
    {
        abort_unless($foto->catatan->jadwal->guru_id === auth()->id(), 403);

        Storage::disk('private')->delete($foto->path);
        $foto->delete();

        return back()->with('sukses', 'Foto dihapus.');
    }

    // Guru atur sendiri jam bukanya, manual per tanggal
    public function ketersediaan(Request $request)
    {
        $tahunTersedia = JadwalTersedia::where('guru_id', auth()->id())
            ->selectRaw("CAST(strftime('%Y', tanggal) AS INTEGER) as thn")
            ->distinct()->orderByDesc('thn')->pluck('thn');
        if ($tahunTersedia->isEmpty() || ! $tahunTersedia->contains(now()->year)) {
            $tahunTersedia->push(now()->year);
            $tahunTersedia = $tahunTersedia->unique()->sortDesc()->values();
        }

        $tahun = (int) $request->query('tahun', now()->year);

        $daftar = JadwalTersedia::where('guru_id', auth()->id())
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal')
            ->get();

        return view('guru.ketersediaan', compact('daftar', 'tahun', 'tahunTersedia'));
    }

    public function simpanKetersediaan(Request $request)
    {
        $data = $request->validate([
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_buka' => 'required|date_format:H:i',
            'jam_tutup' => 'required|date_format:H:i|after:jam_buka',
        ]);

        $jamBuka = Carbon::createFromFormat('H:i', $data['jam_buka']);
        $jamTutup = Carbon::createFromFormat('H:i', $data['jam_tutup']);

        if ($jamBuka->copy()->addMinutes(40)->gt($jamTutup)) {
            return back()->withInput()->withErrors([
                'jam_tutup' => 'Rentang jadwal harus cukup untuk satu sesi konseling selama 40 menit.',
            ]);
        }

        $beririsan = JadwalTersedia::where('guru_id', auth()->id())
            ->where('tanggal', $data['tanggal'])
            ->get()
            ->contains(function (JadwalTersedia $jadwal) use ($jamBuka, $jamTutup) {
                return $jamBuka->lt(Carbon::parse($jadwal->jam_tutup))
                    && $jamTutup->gt(Carbon::parse($jadwal->jam_buka));
            });

        if ($beririsan) {
            return back()->withInput()->withErrors([
                'jam_buka' => 'Rentang jam ini bertumpang tindih dengan jadwal buka yang sudah dibuat.',
            ]);
        }

        JadwalTersedia::create([
            'guru_id' => auth()->id(),
            'tanggal' => $data['tanggal'],
            'jam_buka' => $data['jam_buka'],
            'jam_tutup' => $data['jam_tutup'],
        ]);

        return back()->with('sukses', 'Jam buka tersimpan.');
    }

    public function hapusKetersediaan(JadwalTersedia $ketersediaan)
    {
        abort_unless($ketersediaan->guru_id === auth()->id(), 403);
        $ketersediaan->delete();
        return back()->with('sukses', 'Jam buka dihapus.');
    }

    // Riwayat lengkap semua kunjungan 1 siswa (biar gak perlu buka PDF satu-satu)
    public function riwayatSiswa($nama)
    {
        $riwayat = JadwalKonseling::where('guru_id', auth()->id())
            ->where('nama_siswa', $nama)
            ->with('kategoriMasalah', 'catatan')
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_mulai')
            ->get();

        abort_if($riwayat->isEmpty(), 404);

        $riwayatPerTahun = $riwayat
            ->groupBy(fn (JadwalKonseling $jadwal) => Carbon::parse($jadwal->tanggal)->format('Y'))
            ->map(fn ($daftarTahun) => $daftarTahun->groupBy(
                fn (JadwalKonseling $jadwal) => Carbon::parse($jadwal->tanggal)->format('m')
            ));

        return view('guru.riwayat-siswa', compact('riwayat', 'riwayatPerTahun', 'nama'));
    }

    // Halaman profil guru: upload foto sendiri
    public function profil()
    {
        $fotoProfil = auth()->user()->foto_profil;

        if ($fotoProfil && ! Storage::disk('public')->exists($fotoProfil)) {
            $fotoProfil = null;
        }

        return view('guru.profil', compact('fotoProfil'));
    }

    public function simpanProfil(Request $request)
    {
        $request->validate(['foto' => 'required|image|max:5120']);

        $guru = auth()->user();
        $fotoLama = $guru->foto_profil;
        $path = $request->file('foto')->store('foto-guru', 'public');

        $guru->update(['foto_profil' => $path]);

        if ($fotoLama) {
            Storage::disk('public')->delete($fotoLama);
        }

        return back()->with('sukses', 'Foto profil berhasil diganti.');
    }

    // Export catatan 1 sesi ke PDF A4
    public function exportPdf(JadwalKonseling $jadwal)
    {
        $this->authorizeGuru($jadwal);
        $jadwal->load('guru', 'kategoriMasalah', 'catatan');

        $pdf = Pdf::loadView('guru.catatan-pdf', compact('jadwal'))->setPaper('a4');
        return $pdf->stream("catatan-konseling-{$jadwal->nama_siswa}.pdf");
    }

    private function authorizeGuru(JadwalKonseling $jadwal): void
    {
        abort_unless($jadwal->guru_id === auth()->id(), 403);
    }
}
