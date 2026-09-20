<?php

namespace App\Support;

use App\Models\Inventaris;

class InventarisKode
{
    public static function next(): string
    {
        $max = Inventaris::query()->max('kode_invt');

        $next = $max ? ((int) str_replace('INV/SMKUN/', '', $max)) + 1 : 1;

        return 'INV/SMKUN/'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
