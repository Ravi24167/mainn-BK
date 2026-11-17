<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriMasalah extends Model
{
 protected $table = 'kategori_masalah';
 protected $fillable = ['nama'];

 public function jadwal()
 {
 return $this->hasMany(JadwalKonseling::class);
 }
}
