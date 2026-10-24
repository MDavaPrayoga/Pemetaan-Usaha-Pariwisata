<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kecamatan extends Model
{
    protected $table = 'kecamatan';
    protected $primaryKey = 'id_kecamatan';

    protected $fillable = [
        'nama_kecamatan',
        'geojson_polygon',
    ];

    public function usahaPariwisata(): HasMany
    {
        return $this->hasMany(UsahaPariwisata::class, 'id_kecamatan', 'id_kecamatan');
    }
}
