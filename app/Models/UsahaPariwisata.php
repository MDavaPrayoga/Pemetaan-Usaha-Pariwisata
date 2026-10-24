<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class UsahaPariwisata extends Model
{
    protected $table = 'usaha_pariwisata';
    protected $primaryKey = 'id_usaha';

    protected $fillable = [
        'id_kbli',
        'id_kecamatan',
        'nama_usaha',
        'alamat',
        'latitude',
        'longitude',
        'status_spasial',
        'sumber_data',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function kbli(): BelongsTo
    {
        return $this->belongsTo(Kbli::class, 'id_kbli', 'id_kbli');
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'id_kecamatan', 'id_kecamatan');
    }

    public function anggotaKlaster(): HasMany
    {
        return $this->hasMany(AnggotaKlaster::class, 'id_usaha', 'id_usaha');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status_spasial', 'verified');
    }

    public function scopeUnverified(Builder $query): Builder
    {
        return $query->where('status_spasial', 'unverified');
    }
}
