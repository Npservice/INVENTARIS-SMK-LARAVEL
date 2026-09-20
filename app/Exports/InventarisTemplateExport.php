<?php

namespace App\Exports;

use App\Models\Instansi;
use App\Models\Jenis;
use App\Models\Lokasi;
use App\Models\Pendanaan;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventarisTemplateExport implements FromArray, WithEvents, WithHeadings
{
    private const LAST_ROW = 200;

    public function array(): array
    {
        return [
            [
                'Contoh Proyektor', 'Elektronik', 'Gudang', 'Putra',
                '15/01/2026', 'Baik', 'Mandiri', 1, 3500000, 'Contoh baris, boleh dihapus',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Nama', 'Jenis', 'Lokasi', 'Instansi', 'Tanggal Masuk',
            'Kondisi', 'Pendanaan', 'Jumlah', 'Harga Beli', 'Keterangan',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $spreadsheet = $event->sheet->getDelegate()->getParent();
                $mainSheet = $event->sheet->getDelegate();

                $ranges = $this->buildListSheet($spreadsheet);

                $this->applyRangeDropdown($mainSheet, 'B', $ranges['Jenis']);
                $this->applyRangeDropdown($mainSheet, 'C', $ranges['Lokasi']);
                $this->applyRangeDropdown($mainSheet, 'D', $ranges['Instansi']);
                $this->applyRangeDropdown($mainSheet, 'G', $ranges['Pendanaan']);
                $this->applyStaticDropdown($mainSheet, 'F', ['Baik', 'Rusak']);

                $spreadsheet->setActiveSheetIndex(0);
            },
        ];
    }

    private function buildListSheet($spreadsheet): array
    {
        $lists = [
            'Jenis' => Jenis::orderBy('nama')->pluck('nama')->all(),
            'Lokasi' => Lokasi::orderBy('nama')->pluck('nama')->all(),
            'Instansi' => Instansi::orderBy('nama')->pluck('nama')->all(),
            'Pendanaan' => Pendanaan::orderBy('nama')->pluck('nama')->all(),
        ];

        $listSheet = $spreadsheet->createSheet();
        $listSheet->setTitle('Daftar');

        $columns = ['A' => 'Jenis', 'B' => 'Lokasi', 'C' => 'Instansi', 'D' => 'Pendanaan'];
        $ranges = [];

        foreach ($columns as $column => $key) {
            $values = $lists[$key];
            $listSheet->setCellValue($column.'1', $key);

            foreach ($values as $i => $value) {
                $listSheet->setCellValue($column.($i + 2), $value);
            }

            $lastRow = max(count($values), 1) + 1;
            $ranges[$key] = "Daftar!\${$column}\$2:\${$column}\${$lastRow}";
        }

        $listSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        return $ranges;
    }

    private function applyRangeDropdown($sheet, string $column, string $rangeFormula): void
    {
        for ($row = 2; $row <= self::LAST_ROW; $row++) {
            $this->configureValidation($sheet->getCell($column.$row)->getDataValidation())
                ->setFormula1($rangeFormula);
        }
    }

    private function applyStaticDropdown($sheet, string $column, array $values): void
    {
        for ($row = 2; $row <= self::LAST_ROW; $row++) {
            $this->configureValidation($sheet->getCell($column.$row)->getDataValidation())
                ->setFormula1('"'.implode(',', $values).'"');
        }
    }

    private function configureValidation(DataValidation $validation): DataValidation
    {
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Input salah');
        $validation->setError('Pilih dari daftar yang tersedia.');
        $validation->setPromptTitle('Pilih nilai');
        $validation->setPrompt('Pilih dari daftar dropdown.');

        return $validation;
    }
}
