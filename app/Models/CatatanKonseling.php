<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatatanKonseling extends Model
{
    protected $fillable = ['jadwal_konseling_id', 'catatan', 'kesimpulan', 'tahun'];

    public function jadwal()
    {
        return $this->belongsTo(JadwalKonseling::class, 'jadwal_konseling_id');
    }

    public function foto()
    {
        return $this->hasMany(FotoKonseling::class);
    }
}
