<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kbli extends Model
{
    protected $table = 'kbli';
    protected $primaryKey = 'id_kbli';

    protected $fillable = [
        'kode_kbli',
        'judul_kbli',
        'deskripsi',
    ];

    public function usahaPariwisata(): HasMany
    {
        return $this->hasMany(UsahaPariwisata::class, 'id_kbli', 'id_kbli');
    }
}
