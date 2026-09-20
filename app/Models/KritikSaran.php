<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KritikSaran extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'kritiksarans';

    protected $fillable = ['nama', 'kritik_saran'];
}
