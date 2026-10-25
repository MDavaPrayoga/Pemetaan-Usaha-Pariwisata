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
        'nib',
        'nama_perusahaan',
        'nama_proyek',
        'skala_usaha',
        'uraian_risiko',
        'alamat',
        'kelurahan',
        'tki',
        'tanggal_pengajuan',
        'latitude',
        'longitude',
        'status_spasial',
        'status_operasional',
        'sumber_data',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'tki' => 'integer',
        ];
    }

    /**
     * Accessor untuk nama usaha (kompatibilitas dengan view lama).
     */
    public function getNamaUsahaAttribute(): string
    {
        return $this->nama_proyek ?: ($this->nama_perusahaan ?: 'Usaha Tanpa Nama');
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

    /**
     * Scope data yang koordinatnya valid (1.421 titik)
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status_spasial', 'verified');
    }

    /**
     * Scope data antrean tanpa koordinat (1.388 data)
     */
    public function scopeUnverified(Builder $query): Builder
    {
        return $query->where('status_spasial', 'unverified');
    }

    /**
     * Scope hanya usaha yang berstatus aktif/buka di Google Maps (1.281 titik)
     */
    public function scopeBuka(Builder $query): Builder
    {
        return $query->where('status_operasional', 'Buka');
    }

    /**
     * Scope usaha yang berstatus tutup permanen (109 titik)
     */
    public function scopeTutupPermanen(Builder $query): Builder
    {
        return $query->where('status_operasional', 'Tutup Permanen');
    }

    /**
     * Scope usaha yang berstatus tutup sementara (31 titik)
     */
    public function scopeTutupSementara(Builder $query): Builder
    {
        return $query->where('status_operasional', 'Tutup Sementara');
    }
}
