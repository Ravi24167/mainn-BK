<?php

namespace App\Http\Controllers;

use App\Models\JadwalKonseling;
use App\Models\JadwalTersedia;
use App\Models\KategoriMasalah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class JadwalKonselingController extends Controller
{
    // Form booking terbuka untuk siapa saja, tidak perlu login
    public function create(Request $request)
    {
        $guru = User::where('role', 'guru')->get();
        $guru->each(function (User $pengguna): void {
            if ($pengguna->foto_profil && ! Storage::disk('public')->exists($pengguna->foto_profil)) {
                $pengguna->setAttribute('foto_profil', null);
            }
        });
        $kategori = KategoriMasalah::all();

        $tanggal = $request->query('tanggal', now()->toDateString());
        $guruId = $request->query('guru_id');

        return view('siswa.booking', compact('guru', 'kategori', 'tanggal', 'guruId'));
    }

    // Dipanggil via AJAX pas siswa pilih guru/tanggal, biar gak perlu reload halaman
    public function cekSlot(Request $request)
    {
        $guruId = $request->query('guru_id');
        $tanggal = $request->query('tanggal');

        if (! $guruId || ! $tanggal) {
            return response()->json(['buka' => false, 'slot' => [], 'terisi' => []]);
        }

        $jadwalTerisi = JadwalKonseling::where('guru_id', $guruId)
            ->where('tanggal', $tanggal)
            ->whereIn('status', ['menunggu', 'disetujui'])
            ->get(['jam_mulai', 'jam_selesai']);

        $rentangBuka = JadwalTersedia::where('guru_id', $guruId)->where('tanggal', $tanggal)->get();

        $slot = [];
        $jamTerisi = [];
        foreach ($rentangBuka as $rentang) {
            $jamMulai = Carbon::parse($rentang->jam_buka);
            $jamTutup = Carbon::parse($rentang->jam_tutup);
            while ($jamMulai->copy()->addMinutes(40)->lte($jamTutup)) {
                $jamSelesai = $jamMulai->copy()->addMinutes(40);
                $label = $jamMulai->format('H:i');
                $slot[] = $label;

                $bentrok = $jadwalTerisi->contains(function (JadwalKonseling $jadwal) use ($jamMulai, $jamSelesai) {
                    return $jamMulai->lt(Carbon::parse($jadwal->jam_selesai))
                        && $jamSelesai->gt(Carbon::parse($jadwal->jam_mulai));
                });

                if ($bentrok) {
                    $jamTerisi[] = $label;
                }

                $jamMulai->addMinutes(40);
            }
        }
        sort($slot);
        $slot = array_values(array_unique($slot));

        return response()->json([
            'buka' => $rentangBuka->isNotEmpty(),
            'slot' => $slot,
            'terisi' => $jamTerisi,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_siswa' => 'required|string|max:255',
            'kelas' => 'required|in:10,11,12',
            'jurusan' => 'required|in:KKR,RPL,DKV,TKJ,PSPT',
            'guru_id' => ['required', Rule::exists('users', 'id')->where('role', 'guru')],
            'kategori_masalah_id' => 'required|exists:kategori_masalah,id',
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jenis_kedatangan' => 'required|in:individu,kelompok',
            'anggota_kelompok' => 'nullable|string',
        ]);

        $jamMulai = Carbon::createFromFormat('H:i', $data['jam_mulai']);
        $jamSelesai = $jamMulai->copy()->addMinutes(40);

        $guruBuka = JadwalTersedia::where('guru_id', $data['guru_id'])
            ->where('tanggal', $data['tanggal'])
            ->get()
            ->first(function ($rentang) use ($jamMulai, $jamSelesai) {
                return $jamMulai->gte(Carbon::parse($rentang->jam_buka))
                    && $jamSelesai->lte(Carbon::parse($rentang->jam_tutup));
            });

        if (! $guruBuka) {
            return back()->withInput()->withErrors(['jam_mulai' => 'Guru tidak buka jadwal di jam itu, silakan pilih ulang.']);
        }

        $bentrok = JadwalKonseling::where('guru_id', $data['guru_id'])
            ->where('tanggal', $data['tanggal'])
            ->where('jam_mulai', '<', $jamSelesai->format('H:i:s'))
            ->where('jam_selesai', '>', $jamMulai->format('H:i:s'))
            ->whereIn('status', ['menunggu', 'disetujui'])
            ->exists();

        if ($bentrok) {
            return back()->withInput()->withErrors(['jam_mulai' => 'Waktu ini bertumpang tindih dengan sesi konseling lain. Silakan pilih jam lain.']);
        }

        JadwalKonseling::create([
            'nama_siswa' => $data['nama_siswa'],
            'kelas' => $data['kelas'],
            'guru_id' => $data['guru_id'],
            'kategori_masalah_id' => $data['kategori_masalah_id'],
            'jurusan' => $data['jurusan'],
            'tanggal' => $data['tanggal'],
            'jam_mulai' => $jamMulai->format('H:i:s'),
            'jam_selesai' => $jamSelesai->format('H:i:s'),
            'jenis_kedatangan' => $data['jenis_kedatangan'],
            'anggota_kelompok' => $data['anggota_kelompok'] ?? null,
            'status' => 'menunggu',
        ]);

        return redirect()->route('siswa.jadwal.cek', ['nama' => $data['nama_siswa']])
            ->with('sukses', 'Jadwal berhasil diajukan, menunggu persetujuan guru.');
    }

    // Karena siswa tidak login, cek status dilakukan dengan mencari nama sendiri
    public function cekStatus(Request $request)
    {
        $nama = $request->query('nama');

        $jadwal = collect();
        if ($nama) {
            $jadwal = JadwalKonseling::where('nama_siswa', $nama)
                ->with(['guru', 'kategoriMasalah'])
                ->orderByDesc('tanggal')
                ->get();
        }

        return view('siswa.cek-status', compact('jadwal', 'nama'));
    }
}
