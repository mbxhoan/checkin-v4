<?php

namespace App\Exports\LuckyDraw\Raffle;

use App\Models\LuckyDrawClient;
use Maatwebsite\Excel\Concerns\FromQuery;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;

class WinnersExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, WithTitle
{
    use Exportable;
    private $rows;
    private $luckyDrawId;
    private $sheetTitle;

    public function __construct(int $luckyDrawId, string $sheetTitle = "Danh sách người trúng thưởng")
    {
        $this->luckyDrawId = $luckyDrawId;
        $this->sheetTitle = $sheetTitle;
    }

    public function query()
    {
        $query = LuckyDrawClient::query()
            ->where('lucky_draw_id', $this->luckyDrawId)
            ->whereNotNull('reward_id')
            ->with('reward');

        return $query;
    }

    public function map($row): array
    {
        ++$this->rows;

        return [
            $this->rows,
            $row->qrcode,
            $row->name,
            $row->email,
            $row->phone,
            $row->reward ? $row->reward->code : '',
            $row->reward ? $row->reward->name : '',
            $row->reward ? $row->reward->order_name : '',
            $row->reward ? $row->reward->value : '',
            $row->updated_at,
        ];
    }

    public function headings(): array
    {
        return [
            'STT',
            'QRCODE',
            'TÊN',
            'EMAIL',
            'SỐ ĐIỆN THOẠI',
            'MÃ GIẢI',
            'TÊN GIẢI',
            'LOẠI GIẢI',
            'GIÁ TRỊ GIẢI',
            'THỜI GIAN CẬP NHẬT',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }
}
