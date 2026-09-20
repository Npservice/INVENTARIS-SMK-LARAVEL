<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventaris extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'inventaris';

    protected $fillable = [
        'nama',
        'jenis_id',
        'kode_invt',
        'lokasi_id',
        'masuk',
        'kondisi',
        'pendanaan_id',
        'jumlah',
        'harga_beli',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'masuk' => 'date',
            'jumlah' => 'integer',
            'harga_beli' => 'decimal:2',
        ];
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(Jenis::class);
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class);
    }

    public function pendanaan(): BelongsTo
    {
        return $this->belongsTo(Pendanaan::class);
    }

    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'id_inventaris');
    }

    public function perawatan(): HasMany
    {
        return $this->hasMany(Perawatan::class);
    }
}
