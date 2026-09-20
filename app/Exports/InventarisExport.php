<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventarisExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private readonly Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Kode', 'Nama', 'Jenis', 'Lokasi', 'Instansi', 'Tanggal Masuk',
            'Kondisi', 'Pendanaan', 'Jumlah', 'Harga Beli', 'Keterangan',
        ];
    }

    public function map($inventaris): array
    {
        return [
            $inventaris->kode_invt,
            $inventaris->nama,
            $inventaris->jenis->nama,
            $inventaris->lokasi->nama,
            $inventaris->lokasi->instansi->nama,
            optional($inventaris->masuk)->format('d/m/Y'),
            $inventaris->kondisi,
            $inventaris->pendanaan->nama,
            $inventaris->jumlah,
            $inventaris->harga_beli,
            $inventaris->keterangan,
        ];
    }
}
