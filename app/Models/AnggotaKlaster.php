<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnggotaKlaster extends Model
{
    protected $table = 'anggota_klaster';
    protected $primaryKey = 'id_anggota';
    public $timestamps = false;

    protected $fillable = [
        'id_konfigurasi',
        'id_usaha',
        'cluster_label',
        'tipe_titik',
    ];

    protected function casts(): array
    {
        return [
            'cluster_label' => 'integer',
        ];
    }

    public function konfigurasiKlaster(): BelongsTo
    {
        return $this->belongsTo(KonfigurasiKlaster::class, 'id_konfigurasi', 'id_konfigurasi');
    }

    public function usahaPariwisata(): BelongsTo
    {
        return $this->belongsTo(UsahaPariwisata::class, 'id_usaha', 'id_usaha');
    }
}
