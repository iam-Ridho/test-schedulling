<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Keahlian extends Model
{
    protected $guarded = [];
    protected $table = 'keahlians';
    protected $fillable = ['nama'];

    public function dosens(): BelongsToMany{
        return $this->belongsToMany(Dosen::class, 'dosen_has_keahlian','keahlian_id' ,'dosen_id' );
    }
    
}
