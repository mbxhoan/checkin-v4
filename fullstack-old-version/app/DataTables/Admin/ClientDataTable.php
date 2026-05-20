<?php

namespace App\DataTables\Admin;

use App\DataTables\BaseDataTable;
use App\Models\Checkin;
use App\Models\Client;
use App\Models\Event;
use App\Models\Label;
use App\Models\LabelDetail;
use App\Services\Admin\ClientService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class ClientDataTable extends BaseDataTable
{
    public $service;
    private $event;
    private $customFieldTemplates = [];

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->service = app(ClientService::class);
        $this->customFieldTemplates = $this->event->getCustomFieldTemplates();
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
        $limitedClients = $this->event->company->limited_clients;

        $dataTable = datatables()
            ->eloquent($query)
            ->escapeColumns(['created_at'])
            ->addIndexColumn()
            ->editColumn('status', function(Client $model) {
                return '<label class="btn btn-sm '.$model->getStatusClass().'">'.$model->getStatusText().'</label>';
            })
            ->editColumn('qrcode', function(Client $model) use ($limitedClients) {
                static $rowIndex = 0;
                $rowIndex++;
                if (is_numeric($limitedClients) && $rowIndex > $limitedClients) return null;

                $qrCodeId = 'qrcode-' . $model->id;
                $viewRoute = route('clients.view-qrcode-by-id', [
                    'id' => $model->id
                ]);
                $generateRoute = route('clients.generate-qrcode-by-id', [
                    'id' => $model->id
                ]);

                $imgHtml = '';
                if ($model->img_qrcode) {
                    $imgHtml = '<a target="_blank" href="'.($viewRoute).'" class="d-inline-block mb-1">'
                        .'<img src="'.($viewRoute).'" alt="qrcode" style="width: 50px; height: 50px; object-fit: contain; border: 1px solid #e5e7eb; border-radius: 6px; padding: 2px; background: #fff;" />'
                        .'</a>';
                }

                $buttons = '<div class="d-flex align-items-center gap-2">'
                    .'<a class="" data-clipboard-target="#'.$qrCodeId.'"><i class="fa-regular fa-clipboard"></i></a>'
                    .($model->img_qrcode
                        ? '<a target="_blank" href="'.($viewRoute).'"><i class="fa-solid fa-qrcode"></i></a>'
                        : '<a target="_blank" href="'.($generateRoute).'"><i class="fa-solid fa-plus"></i></a>')
                    .'</div>';

                // Clipboard.js requires the target element to exist in the DOM.
                $codeText = '<div class="text-xs mt-1"><span id="'.$qrCodeId.'" class="fw-bold">'.$model->qrcode.'</span></div>';

                return '<div class="d-flex flex-column align-items-start">'.$imgHtml.$codeText.$buttons.'</div>';
            })
            ->editColumn('name', function(Client $model) use ($limitedClients) {
                $route = route('admin.clients.edit', [
                    'client'    => $model,
                ]);

                static $rowIndex = 0;
                $rowIndex++;
                if (is_numeric($limitedClients) && $rowIndex > $limitedClients) return null;

                /* checkin */
                $checkins = new Checkin();
                $checkins->where('checkins.qrcode', $model->qrcode);
                $checkins->where('checkins.status', '!=', Checkin::STATUS_DELETED);
                $checkins->where('checkins.event_id', '=', $model->event->id);
                $checkins->orderBy('checkins.updated_at', 'DESC');
                $checkins = $checkins->get();

                /*  */
                $canvas = view('admin.clients.detail.canvas', [
                    'canvasId'              => $model->id,
                    'client'                => $model,
                    'event'                 => $model->event,
                    'cfTemplate'            => $this->service->custom_field_template()->init(),
                    'customFieldTemplates'  => $model->event->getCustomFieldTemplates(),
                    'checkins'              => $checkins ?? null,
                ])->render();

                return $canvas.'<a href="'.$route.'" data-bs-toggle="offcanvas" data-bs-target="#'.$model->id.'" aria-controls="'.$model->id.'"><b>'.$model->name.'</b></a>';

                // return $canvas.'<a href="'.$route.'" data-bs-toggle="offcanvas" data-bs-target="#'.$model->id.'" aria-controls="'.$model->id.'"><b>'.$model->name.'</b>
                // <i class="fa-solid fa-edit"></i>
                // </a>';
            })
            // ->editColumn('updated_at', function(Client $model) {
            //     return $model->updated_at ? humanize_date($model->updated_at, 'd/m/Y H:i:s') : null;
            // })
            ->editColumn('type', function(Client $model) {
                return $model->type ?? "-";
            })
            ->editColumn('register_source', function(Client $model) {
                return $model->register_source ?? "-";
            })
            ->editColumn('updated_by', function(Client $model) {
                return ($model->updated_by ? $model->user->name.'<br>' : null).($model->updated_at ? humanize_date($model->updated_at, 'd/m/Y H:i:s') : null);
            })
            ->addColumn('actions', function(Client $model) use ($limitedClients) {
                static $rowIndex = 0;
                $rowIndex++;
                if (is_numeric($limitedClients) && $rowIndex > $limitedClients) return "hidden";
                return $this->generateActionBtns($model);
            })
            ->setRowClass(function (Client $model) use ($limitedClients) {
                // Use the DT_RowIndex column to determine the row index
                static $rowIndex = 0;
                $rowIndex++;

                if ($model->findCheckin()) {
                    return "table-secondary text-dark";
                }

                if (is_numeric($limitedClients) && $rowIndex > $limitedClients) {
                    // return "table-dark"; // Add the `table-dark` class for rows beyond the 20th
                }

                return "table-hover";
            });

        // $dataTable = $dataTable->filterColumn('name', function($query, $keyword) {
        //     $query->where('name', 'like', "%{$keyword}%");
        // });

        foreach ($this->customFieldTemplates as $templateName => $templateDesc) {
            $fieldName = strtolower($templateName);

            // Add the custom column
            $dataTable = $dataTable->addColumn($fieldName, function(Client $model) use ($templateDesc, $limitedClients) {
                static $rowIndex = 0;
                $rowIndex++;
                if (is_numeric($limitedClients) && $rowIndex > $limitedClients) return null;
                return $model->getCustomFieldValue($templateDesc, false) ?? "-";
            });

            // Make the column searchableimport
            $dataTable = $dataTable->filterColumn($fieldName, function($query, $keyword) use ($fieldName) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(custom_fields, '$.\"{$fieldName}\"'))) LIKE ?", ["%".strtolower($keyword)."%"]);
            });
        }

        return $dataTable;
    }

    public function getFilter($query = null)
    {
        if (empty($query)) {
            $query = $this->query(new Client()); // Get the base query
        }

        $query->where('status', '!=', Client::STATUS_DELETED);
        $query->where('event_id', '=', $this->event->id);
        $query->orderBy('updated_at', 'DESC');
        $query = $this->service->applyFilters($query);
        $query = $this->service->applyCustomFieldFilters($query, $this->event->id);
        // $query->take(50);
        return $query;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Client $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Client $model)
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('client-table')
                    ->columns($this->getColumns())
                    // Preserve current query params (filters) when DataTables fetches via AJAX.
                    ->minifiedAjax(url()->full())
                    ->dom('Bfrtlip')
                    ->orderBy(1)
                    ->buttons(
                        Button::make('create'),
                        Button::make('export'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
                    )
                    ->addTableClass('table-striped')
                    ->parameters([
                        'responsive'    => true,
                        'autoWidth'     => false,
                        'pageLength'    => 20,
                        'lengthMenu'    => [5, 10, 20, 30, 50, 100, 200, 500],
                        'processing'    => true,
                        'stateSave'     => true,
                        'language'      => $this->getL(),
                        'initComplete' => "function () {
                            this.api().columns().every(function () {
                                var column = this;
                                var input = document.createElement('input');
                                input.placeholder = '🔍';
                                input.style.width = '100%';
                                input.className = 'form-control form-control-sm';
                                $(input).appendTo($(column.footer()).empty())
                                    .on('keyup change clear', function () {
                                        if (column.search() !== this.value) {
                                            column.search(this.value).draw();
                                        }
                                    });
                            });
                        }",
                    ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $fullCustomFieldTemplates = $this->event->getCustomFieldTemplates(true);

        $columns = [
            Column::make('actions')
                ->title(""),
            Column::make('status')
                ->title("Trạng thái")
                ->sortable(true),
            Column::make('type')
                ->title("Nhóm")
                ->addClass('text-table')
                ->sortable(true),
            Column::make('register_source')
                ->title("Nguồn đăng ký")
                ->addClass('text-table')
                ->sortable(true),
            Column::make('updated_by')
                ->addClass('text-table')
                ->title("Cập nhật")
                ->sortable(true),
            Column::make('qrcode')
                ->addClass('text-xs')
                ->width(50)
                ->title($fullCustomFieldTemplates['qrcode']['desc'] ?? "QR"),
            Column::make('name')
                ->addClass('text-table')
                ->title($fullCustomFieldTemplates['name']['desc'] ?? "Tên"),
            Column::make('email')
                ->addClass('text-table')
                ->title($fullCustomFieldTemplates['email']['desc'] ?? "Email"),
            // Column::make('ref_id')
            //     ->addClass('text-table')
            //     ->title("REF ID"),
            // Column::make('updated_at')
            //     ->addClass('text-table')
            //     ->title("Cập nhật lúc")
            //     ->sortable(true),
        ];

        foreach ($this->customFieldTemplates as $templateName => $templateAttr) {
            $customColumns[] = Column::make(strtolower($templateName))
                ->addClass("{$templateName} text-table truncate-table-hover all")
                ->title($templateAttr['desc'] ?? strtoupper($templateName))
                ->searchable(true)
                ->orderable(true);
        }

        return  array_merge($columns, $customColumns ?? []);
    }

    protected function generateActionBtns($model)
    {
        /* button edit */
        $buttons = view('components.btn-edit', [
            'route' => route('admin.clients.edit', [
                'client'    => $model,
            ]),
            'class' => 'btn btn-xs btn-primary mb-1',
        ]);
        /* button delete */
        $buttons .= view('components.btn-del-alert', [
            'route'     => route('admin.clients.destroy', $model),
            'class'     => 'btn btn-xs btn-danger mb-1',
            'confirm'   => 'Bạn có chắc chắn muốn xoá khách hàng này?',
            'modalId'   => "client-{$model->id}",
            'pushToStack' => false,
        ]);
        /* button print */
        $labels = $model->event->labels;
        $label = $labels->first();
        if ($label && in_array($label->status, [
            Label::STATUS_NEW,
            Label::STATUS_ACTIVE
        ])) {
            $buttons .= '
                <a class="btn btn-xs btn-warning mb-1 btn-toggle-modal"
                    data-modal_id="modalLabelPrint-'.$model->id.'"
                    data-bs-toggle="modal"
                    data-bs-target="#modalLabelPrint-'.$model->id.'"
                >
                    <i class="fa-solid fa-print"></i>
                </a>';
            $buttons .= view('admin.clients._modal-print', [
                'modalId'           => "modalLabelPrint-{$model->id}",
                'title'             => "In tem",
                'modalClass'        => 'modal-dialog-scrollable modal-dialog-centered',
                'modalBodyClass'    => 'text-sm',
                'labels'            => $labels,
                'label'             => $label,
                'labelDetails'      => $label->label_details->where('status', '!=', LabelDetail::STATUS_DELETED) ?? null,
                'event'             => $model->event,
                'client'            => $model,
            ]);
        }
        /* button checkin */
        $buttons .= view('components.btn-checkin', [
            'route' => route('admin.checkins.checkin'),
            'class' => 'btn btn-xs btn-primary mb-1',
            'model' => $model,
            'text'  => 'Checkin',
        ]);

        /* button send mail */
        $campaigns = $model->event->campaigns ?? collect();
        if ($campaigns->count()) {
            $buttons .= '
                <a class="btn btn-xs btn-info mb-1"
                    data-bs-toggle="modal"
                    data-bs-target="#modalLabelSendMail-'.$model->id.'">
                    <i class="fa-solid fa-paper-plane"></i>
                </a>';

            $buttons .= view('admin.clients._modal-send-mail', [
                'modalId'           => "modalLabelSendMail-{$model->id}",
                'title'             => "Gửi mail",
                'modalClass'        => 'modal-dialog-scrollable modal-dialog-centered',
                'modalBodyClass'    => 'text-sm',
                'campaigns'         => $campaigns,
                'event'             => $model->event,
                'client'            => $model,
                'display'           => true,
            ]);
        }

        return $buttons;
    }
}
