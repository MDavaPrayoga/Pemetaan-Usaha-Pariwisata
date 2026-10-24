<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class KonfigurasiKlaster extends Model
{
    protected $table = 'konfigurasi_klaster';
    protected $primaryKey = 'id_konfigurasi';

    protected $fillable = [
        'id_user',
        'nama_konfigurasi',
        'parameter_eps',
        'parameter_minpts',
        'total_cluster',
        'total_noise',
        'execution_time_ms',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'parameter_eps' => 'float',
            'parameter_minpts' => 'integer',
            'total_cluster' => 'integer',
            'total_noise' => 'integer',
            'execution_time_ms' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function anggotaKlaster(): HasMany
    {
        return $this->hasMany(AnggotaKlaster::class, 'id_konfigurasi', 'id_konfigurasi');
    }

    public function usahaPariwisata(): BelongsToMany
    {
        return $this->belongsToMany(
            UsahaPariwisata::class,
            'anggota_klaster',
            'id_konfigurasi',
            'id_usaha'
        )->withPivot('cluster_label', 'tipe_titik');
    }
}
