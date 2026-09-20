<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peminjaman extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'peminjaman';

    protected $primaryKey = 'id_pjm';

    protected $fillable = [
        'id_user',
        'id_inventaris',
        'nama_pjm',
        'status_pjm',
        'waktu_pinjam',
        'waktu_kembali',
        'tanggal_pinjam',
        'tanggal_kembali',
        'status_pinjam',
        'keterangan_pjm',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'date',
            'tanggal_kembali' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function inventaris(): BelongsTo
    {
        return $this->belongsTo(Inventaris::class, 'id_inventaris');
    }
}
