<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $guarded = [];

    protected $fillable = [
        'nama',
        'nim',
        'skripsi',
        'keahlian_id'
    ];

    public function dosens()
    {
        return $this->belongsToMany(Dosen::class, 'dosen_has_mahasiswa');
    }

    public function keahlian()
    {
        return $this->belongsTo(Keahlian::class, 'keahlian_id');
    }

    public function pembimbings()
    {
        return $this->dosens();
    }

}
