<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataTables\Admin\ClientForLabelDataTable;
use App\DataTables\Admin\LabelDataTable;
use App\Http\Requests\Admin\Labels\CloneRequest;
use App\Http\Requests\Admin\Labels\ListRequest;
use App\Http\Requests\Admin\Labels\UpdateLiveRequest;
use App\Http\Requests\Admin\LabelsRequest;
use App\Http\Requests\Admin\SelectEventToCreateRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\Event;
use App\Models\Label;
use App\Models\LabelDetail;
use App\Models\Client;
use App\Services\Admin\LabelService;
use Illuminate\Http\Request;
use stdClass;

class LabelController extends Controller
{
    public function __construct(LabelService $service)
    {
        $this->service = $service;
    }

    public function index(ListRequest $request)
    {
        $dataTable = new LabelDataTable();
        $total = $dataTable->getFilter();
        $events = $this->service->event()->getEventList();

        return $dataTable->render('admin.labels.index', [
            'total'             => $total->count(),
            'eventArray'        => $events->mapWithKeys(function ($event) {
                return [
                    $event->id  => "{$event->code} - {$event->name}"
                ];
            })->toArray(),
        ]);
    }

    public function selectEventToCreate(SelectEventToCreateRequest $request)
    {
        return redirect()->route('admin.labels.create', [
            'event' => $request->event_id
        ]);
    }

    /**
     * Display the specified resource edit form.
     */
    public function edit(Label $label, Request $request)
    {
        $this->authorize('edit', $label);

        $events = $this->service->event()->getEventList();
        $customFieldTemplates = $this->service->custom_field_template()->getListByAttributes([
            'event_id'      => $label->event->id,
        ], [], [], 0, [
            'order'         => 'ASC',
        ]);

        $types = $this->service->client()->getListDistinctField([
            'event_id' => $label->event->id,
        ]);

        $types = $this->service
            ->removeEmptyElementInArray($types->pluck('type', 'type')
            ->toArray());

        foreach ($types as $key => $type) {
            $count = $this->service->client()->getListByAttributes([
                'event_id' => $label->event->id,
                'status'   => Client::STATUS_ACTIVE,
                'type'     => $key,
            ])->count();

            $types[$key] = "{$type} ({$count})";
        }

        $dataTable = new ClientForLabelDataTable($label->event, $label, array_filter([
            "type" => $label->client_type
        ]));

        $totalClients = $dataTable->getFilter();

        // Get the collection of clients
        $clients = $this->service->client()->getListByAttributes([
            'event_id'  => $label->event->id,
            // "type"      => $label->client_type
            // 'status'   => Client::STATUS_ACTIVE,
        ]);

        return $dataTable->render('admin.labels.detail', [
            'labels'                => $this->service->getListByAttributes([
                    'event_id'      => $label->event->id
                ], [], [], 0, []),
            'labelDetails'           => $label->label_details,
            'labelDetail'            => $this->service->label_detail()->init(),
            'model'                 => $label,
            'event'                 => $label->event,
            'customFieldTemplates'  => $customFieldTemplates,
            'cfTemplate'            => $this->service->custom_field_template()->init(),
            'cfTemplatesArray'      => $customFieldTemplates->mapWithKeys(function ($customFieldTemplate) {
                    return [$customFieldTemplate->name  => "{$customFieldTemplate->name} - {$customFieldTemplate->description}"];
                })->toArray(),
            'types'                 => $types,
            'client'                => $request->client_id ? $this->service->client()->findByAttributes(['id' => $request->client_id]) : null,
            'totalClients'          => $totalClients,
            'clients'               => $clients,
            'eventArray'            => $events->mapWithKeys(function ($event) {
                    return [$event->id  => "{$event->code} - {$event->name}"];
                })->toArray(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, Event $event)
    {
        $events = $this->service->event()->getEventList();

        $types = $this->service->client()->getListDistinctField([
            'event_id' => $event->id,
        ]);

        $types = $this->service
            ->removeEmptyElementInArray($types->pluck('type', 'type')
            ->toArray());

        foreach ($types as $key => $type) {
            $count = $this->service->client()->getListByAttributes([
                'event_id' => $event->id,
                'status'   => Client::STATUS_ACTIVE,
                'type'     => $key,
            ], [
                'email'    => null,
            ])->count();

            $types[$key] = "{$type} ({$count})";
        }

        return view('admin.labels.create', [
            'model'                 => $this->service->init(),
            'types'                 => $types,
            'eventArray'            => $events->mapWithKeys(function ($event) {
                return [$event->id  => "{$event->code} - {$event->name}"];
            })->toArray(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LabelsRequest $request): RedirectResponse
    {
        $attributes = $request->only([
            'event_id',
            'name',
            'type',
            'width',
            'height',
            'unit',
            'status',
        ]);

        $attributes['created_by'] = auth()->user()->id;
        $attributes['updated_by'] = auth()->user()->id;
        $attributes['status'] = Label::STATUS_NEW;
        $label = $this->service->create($attributes);

        return redirect()->route('admin.labels.edit', $label)->withSuccess("Tạo mới thành công");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LabelsRequest $request, Label $label): RedirectResponse
    {
        $attributes = $request->only([
            'event_id',
            'is_default',
            'name',
            'type',
            'width',
            'height',
            'unit',
            'status',
        ]);

        $attributes['is_default'] = isset($attributes['is_default']) ? 1 : 0;
        $attributes['unit'] = "unit";
        $attributes['status'] = Label::STATUS_ACTIVE;
        $attributes['updated_by'] = auth()->user()->id;

        if ($attributes['is_default']) {
            $this->service->setUnDefaultByEvent($label->event);
        }

        $this->service->update($label->id, $attributes);
        return redirect()->route('admin.labels.edit', $label)->withSuccess("Cập nhật thành công");
    }

    public function renderLabel(Label $label, Request $request)
    {
        $this->authorize('render_label', $label);

        return $this->responseSuccess([
            'html' => view('components.label_details.to-print', [
                'label'             => $label,
                'labelDetails'      => $label->label_details->where('status', '!=', LabelDetail::STATUS_DELETED) ?? null,
                'event'             => $label,
                'display'           => true,
                'client'            => $request->client_id ? $this->service->client()->findByAttributes(['id' => $request->client_id]) : null,
            ])->render()
        ]);
    }

    public function renderBox(Request $request)
    {
        $label          = new stdClass();
        $label->width   = $request->width;
        $label->height  = $request->height;
        $label->unit    = $request->unit;
        $label->rotate  = 0;
        return $this->responseSuccess([
            'html' => view('components.label_details.to-print', [
                'label'             => $label,
                'display'           => true,
                'client'            => null,
            ])->render()
        ]);
    }

    public function updateLive(UpdateLiveRequest $request, Label $label)
    {
        $attributes = $request->only([
            'type',
            'width',
            'height',
            'unit',
        ]);

        $attributes['status'] = Label::STATUS_ACTIVE;
        $attributes['updated_by'] = auth()->user()->id;
        $this->service->update($label->id, $attributes);
        return $this->responseSuccess(null, "Cập nhật thành công");
    }

    public function clone(CloneRequest $request, Label $label)
    {
        /* clone label */
        $newLabel = $label->replicate();
        $newLabel->is_default = false;
        $newLabel->event_id   = $request->event_id;
        $newLabel->name       = $request->name;
        $newLabel->created_by = auth()->user()->id;
        $newLabel->updated_by = auth()->user()->id;
        $newLabel->status     = Label::STATUS_ACTIVE;
        $newLabel->save();

        /* clone label details */
        $labelDetails = $label->label_details;
        if (!empty($labelDetails) && $labelDetails->count()) {
            foreach ($labelDetails as $labelDetail) {
                $newLabelDetail = $labelDetail->replicate();
                $newLabelDetail->label_id = $newLabel->id;
                $newLabelDetail->save();
            }
        }

        return redirect()->route('admin.labels.edit', $newLabel)
            ->withSuccess("Đã nhân bản thành công");
    }

    public function destroy (Label $label) {
        $this->service->update($label->id, [
            'status'        => Label::STATUS_DELETED,
        ]);
    }
}
