<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Perawatan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'perawatan';

    protected $fillable = [
        'inventaris_id',
        'user_id',
        'tanggal_perawatan',
        'status_perawatan',
        'biaya',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_perawatan' => 'date',
            'biaya' => 'decimal:2',
        ];
    }

    public function inventaris(): BelongsTo
    {
        return $this->belongsTo(Inventaris::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
