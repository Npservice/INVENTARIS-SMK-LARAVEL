<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pendanaan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pendanaan';

    protected $fillable = ['nama'];

    public function inventaris(): HasMany
    {
        return $this->hasMany(Inventaris::class);
    }
}
