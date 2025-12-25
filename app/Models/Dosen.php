<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dosen extends Model
{
    protected $guarded = [];

    protected $table = 'dosens';
    protected $fillable = ['nama', 'nip', 'telepon']; 

    public function keahlians(): BelongsToMany {
        return $this->belongsToMany(Keahlian::class, 'dosen_has_keahlian', 'dosen_id', 'keahlian_id');
    }

    public function mahasiswas()
    {
        return $this->belongsToMany(Mahasiswa::class, 'dosen_has_mahasiswa');
    }

    public function sesis()
    {
        return $this->belongsToMany(Sesi::class, 'dosen_has_sesi', 'dosen_id', 'sesi_id');
    }


}
