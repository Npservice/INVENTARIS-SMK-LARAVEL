<?php

namespace App\Imports;

use App\Models\Instansi;
use App\Models\Inventaris;
use App\Models\Jenis;
use App\Models\Lokasi;
use App\Models\Pendanaan;
use App\Support\InventarisKode;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class InventarisImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;

    public int $skipped = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $nama = trim((string) ($row['nama'] ?? ''));

            if ($nama === '') {
                $this->skipped++;

                continue;
            }

            $instansi = Instansi::firstOrCreate(['nama' => trim((string) $row['instansi'])]);

            $lokasiNama = trim((string) $row['lokasi']);
            $lokasi = Lokasi::firstOrCreate(
                ['instansi_id' => $instansi->id, 'nama' => $lokasiNama],
                ['is_gudang' => strtolower($lokasiNama) === 'gudang'],
            );

            $jenis = Jenis::firstOrCreate(['nama' => trim((string) $row['jenis'])]);
            $pendanaan = Pendanaan::firstOrCreate(['nama' => trim((string) $row['pendanaan'])]);

            Inventaris::create([
                'nama' => $nama,
                'jenis_id' => $jenis->id,
                'kode_invt' => InventarisKode::next(),
                'lokasi_id' => $lokasi->id,
                'masuk' => $this->parseDate($row['tanggal_masuk'] ?? null),
                'kondisi' => $row['kondisi'] ?? 'Baik',
                'pendanaan_id' => $pendanaan->id,
                'jumlah' => (int) ($row['jumlah'] ?? 1),
                'harga_beli' => $row['harga_beli'] ?? null,
                'keterangan' => $row['keterangan'] ?? null,
            ]);

            $this->imported++;
        }
    }

    private function parseDate(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
        }

        return Carbon::parse((string) $value)->format('Y-m-d');
    }
}
