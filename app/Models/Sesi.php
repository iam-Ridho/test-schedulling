<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sesi extends Model
{
    protected $guarded = [];

    public function dosens()
    {
        return $this->belongsToMany(Dosen::class, 'dosen_has_sesi');
    }

    public function getFormatApiAttribute()
    {
        //Convert value jam
        $jam = substr($this->jam_sesi, 0, 5);
        $jam = str_replace(':', '.', $jam);

        //Jam Selesai
        $jamMulai = strtotime($this->jam_sesi);
        $jamSelesai = date('H.i', strtotime('+2 hours', $jamMulai));

        return $this->hari . ' '. $jam . '-' . $jamSelesai;
    }
}
