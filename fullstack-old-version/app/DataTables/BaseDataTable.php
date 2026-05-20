<?php

namespace App\DataTables;

use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class BaseDataTable extends DataTable
{
    public function __construct()
    {

    }

    public function getL()
    {
        return [
            'searchPlaceholder'     => __('datatable.search_placeholder'),
            'search'                => '',
            "decimal"               => "",
            "emptyTable"            => __('datatable.empty_table'),
            "info"                  => __('datatable.info'),
            "infoEmpty"             => __('datatable.info_empty'),
            "infoFiltered"          => __('datatable.info_filtered'),
            "infoPostFix"           => "",
            "thousands"             => ",",
            "lengthMenu"            => __('datatable.length_menu'),
            "loadingRecords"        => __('datatable.loading_records'),
            "processing"            => __('datatable.processing'),
            "zeroRecords"           => __('datatable.zero_records'),
            // "paginate"              => [
            //     "first"             => "Đầu tiên",
            //     "last"              => "Cuối cùng",
            //     "next"              => "Tiếp",
            //     "previous"          => "Trước"
            // ],
            "aria"                  => [
                "orderable"         => __('datatable.aria.orderable'),
                "orderableReverse"  => __('datatable.aria.orderable_reverse')
            ]
        ];
    }
}
