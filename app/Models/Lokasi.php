<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lokasi extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'lokasi';

    protected $fillable = ['instansi_id', 'nama', 'is_gudang'];

    protected function casts(): array
    {
        return [
            'is_gudang' => 'boolean',
        ];
    }

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class);
    }

    public function inventaris(): HasMany
    {
        return $this->hasMany(Inventaris::class);
    }
}
