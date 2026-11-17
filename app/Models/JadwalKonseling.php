<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalKonseling extends Model
{
    protected $fillable = [
        'nama_siswa', 'kelas', 'guru_id', 'kategori_masalah_id', 'jurusan',
        'tanggal', 'jam_mulai', 'jam_selesai',
        'jenis_kedatangan', 'anggota_kelompok', 'status', 'alasan_ditolak',
    ];

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function kategoriMasalah()
    {
        return $this->belongsTo(KategoriMasalah::class, 'kategori_masalah_id');
    }

    public function catatan()
    {
        return $this->hasOne(CatatanKonseling::class);
    }

    public function scopeMenunggu($query)
    {
        return $query->where('status', 'menunggu');
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'disetujui');
    }
}
