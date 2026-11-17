<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FotoKonseling extends Model
{
    protected $fillable = ['catatan_konseling_id', 'path', 'tahun'];

    public function catatan()
    {
        return $this->belongsTo(CatatanKonseling::class, 'catatan_konseling_id');
    }
}
