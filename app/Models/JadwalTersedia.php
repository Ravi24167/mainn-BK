<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalTersedia extends Model
{
    protected $fillable = ['guru_id', 'tanggal', 'jam_buka', 'jam_tutup'];

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }
}
