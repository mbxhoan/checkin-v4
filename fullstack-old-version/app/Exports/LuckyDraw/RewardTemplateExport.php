<?php

namespace App\Exports\LuckyDraw;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class RewardTemplateExport implements FromArray, WithHeadings, WithColumnFormatting
{
    use Exportable;

    public function array(): array
    {
        return [
            ['RE0001', 'Giải đặc biệt', 1, 1, 'Giải đặc biệt', 5, 'https://example.com/giai-dac-biet.png'],
            ['RE0002', 'Giải nhất', 1, 2, 'Giải nhất', 5, 'https://example.com/giai-nhat.png'],
            ['RE0003', 'Giải nhì', 1, 3, 'Giải nhì', 5, 'https://example.com/giai-nhi.png'],
            ['RE0004', 'Giải ba', 1, 4, 'Giải ba', 5, 'https://example.com/giai-ba.png'],
        ];
    }

    /**
     * Headers: value = số lượng người trúng giải đó trong một lần quay
     */
    public function headings(): array
    {
        return [
            'code',
            'name',
            'value',      // Số lượng người trúng một lần
            'order',
            'order_name',
            'time',
            'img_link',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
