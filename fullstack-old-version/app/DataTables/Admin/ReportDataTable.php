<?php

namespace App\DataTables\Admin;

use App\DataTables\BaseDataTable;
use App\Models\Client;
use App\Models\Event;
use App\Services\Admin\EventService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class ReportDataTable extends BaseDataTable
{
    public $service;

    public function __construct()
    {
        $this->service = app(EventService::class);
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $query = $this->getFilter($query);

        return datatables()
            ->eloquent($query)
            ->escapeColumns(['created_at'])
            ->editColumn('logo', function(Event $model) {
                $route = route('admin.reports.report', [
                    'event' => $model
                ]);
                if ($model->logoUrl) {
                    $logoUrl = $model->logoUrl->getUrl();
                } else {
                    $logoUrl = asset(config("info.placeholders.image"));
                }
                return '<a href="'.$route .'"><img src="'.$logoUrl.'" alt="'.$model->name.'" width="50px"></a>';
            })
            ->editColumn('status', function(Event $model) {
                return '<label class="btn btn-xs '.$model->getStatusClass().'">'.$model->getStatusText().'</label>';
            })
            ->addColumn('name', function(Event $model) {
                $route = route('admin.reports.report', [
                    'event' => $model
                ]);
                return '<a href="'.$route .'"><b>'.$model->name.'</b></b></a>';
            })
            ->editColumn('updated_by', function(Event $model) {
                return $model->user->name;
            })
            ->editColumn('updated_at', function(Event $model) {
                return $model->updated_at ? humanize_date($model->updated_at, 'd/m/Y H:i') : null;
            })
            ->addColumn('run_dates', function(Event $model) {
                if ($model->from_date == $model->to_date) {
                    return humanize_date($model->from_date, 'd/m/Y');
                }

                return humanize_date($model->from_date, 'd/m/Y').'<br>'.humanize_date($model->to_date, 'd/m/Y');
            })
            ->addColumn('province', function(Event $model) {
                return $model->province ? $model->province->name : null;
            })
            ->addColumn('count', function(Event $model) {
                $route = route('admin.clients.index', [
                    'event' => $model
                ]);
                $btns = '<a href="'.$route.'"><i class="fa-solid fa-users"></i> <b> Khách mời: '.$model->clients_count.'</b></a><br>';
                $route = route('admin.checkins.index', [
                    'event' => $model
                ]);
                $totalCheckedIn = $this->service->middleware_client()->getClientCheckedIn($model->code);
                $btns .= '<a href="'.$route.'"><i class="fa-solid fa-sm fa-check me-1"></i> <b> Đã tham dự: '.$totalCheckedIn->count().'</b></a>';
                return $btns;
            })
            ->addColumn('actions', function(Event $model) {
                return $this->generateActionBtns($model);
            })
            ->setRowClass(function (Event $model) {
                if ($model->status == Event::STATUS_DONE) {
                    return "table-success";
                }

                return '';
            });
    }

    public function getFilter($query = null)
    {
        if (empty($query)) {
            $query = $this->query(new Event()); // Get the base query
        }

        if (!auth()->user()->isSysAdmin()) {
            $query->where('company_id', auth()->user()->company->id);
        }

        if (!auth()->user()->isAdmin()) {
            $query->where('id', auth()->user()->event_id);
        }

        $query->where('status', '!=', Event::STATUS_DELETED);
        $query->orderBy('updated_at', 'DESC');
        $query->orderBy('status', 'DESC');

        /* lọc */
        if (request()->filled('status')) {
            $attributes['status'] = request()->input('status');
        }

        if (request()->filled('company_id')) {
            $attributes['company_id'] = request()->input('company_id');
        }

        $dateField = request()->input('field_date');

        if ($dateField) {
            if (in_array($dateField, [
                'run_date'
            ])) {
                if (request()->filled('from_date')) {
                    $query->whereDate('from_date', '>=', request()->from_date);
                }

                if (request()->filled('to_date')) {
                    $query->whereDate('to_date', '<=', request()->to_date);
                }
            } else {
                if (request()->filled('from_date')) {
                    $query->whereDate(request()->input('field_date'), '>=', request()->input('from_date'));
                }

                if (request()->filled('to_date')) {
                    $query->whereDate(request()->input('field_date'), '<=', request()->input('to_date'));
                }
            }
        }

        if (isset($attributes) && count($attributes)) {
            foreach ($attributes as $key => $value) {
                $query->where($key, $value);
            }
        }

        return $query;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Event $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Event $model)
    {
        return $model->newQuery()
            ->withCount([
                'clients as clients_count' => function ($query) {
                    $query->where('status', '!=', Client::STATUS_DELETED);
                }
            ]);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('report-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtlip')
                    ->orderBy(1)
                    ->buttons(
                        Button::make('create'),
                        Button::make('export'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
                    )
                    ->parameters([
                        'pageLength'    => 50,
                        'lengthMenu'    => [5, 10, 20, 30, 50, 100, 200, 500],
                        'processing'    => true,
                        'stateSave'     => true,
                        'language'      => $this->getL(),
                        'buttons'       => [
                            [
                                'text'          => '<i class="bx bxs-printer"></i>',
                                'className'     => 'btn buttons-print btn-default',
                            ],
                        ],

                    ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('logo')
                ->title(""),
            Column::make('name')
                ->width(120)
                ->title("Sự kiện")
                ->addClass('text-table truncate-table-hover'),
            Column::make('count')
                ->title("Người tham dự")
                ->width(200)
                ->addClass('text-table'),
            Column::make('run_dates')
                ->title("Thời gian"),
            Column::make('province')
                ->title("Tỉnh/Thành"),
            Column::make('status')
                ->title("Trạng thái"),
            Column::make('actions')
                ->title(""),
        ];
    }

    protected function generateActionBtns($model)
    {
        $route = route('admin.reports.report', [
            'event' => $model
        ]);

        return '<a href="'.$route.'" class="btn btn-sm btn-primary"><i class="fa-solid fa-chart-simple"></i> Xem báo cáo</a>';
    }
}
