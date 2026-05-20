<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\CampaignDataTable;
use App\DataTables\Admin\CardDataTable;
use App\DataTables\Admin\ClientDataTable;
use App\DataTables\Admin\EventDataTable;
use App\DataTables\Admin\LabelDataTable;
use App\DataTables\Admin\LandingPageDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Events\CloneRequest;
use App\Http\Requests\Admin\Events\EventsRequest;
use App\Http\Requests\Admin\Events\ListRequest;
use App\Http\Requests\Admin\Events\RemoveFeatureRequest;
use App\Http\Requests\Admin\Events\UpdateCustomCheckinMessageRequest;
use App\Http\Requests\Admin\Events\UpdateFeatureRequest;
use App\Http\Requests\Admin\Events\UploadMediaRequest;
use App\Models\Card;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Services\Admin\EventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Company;
use App\Models\Email;
use App\Models\Event;
use App\Models\EventSetting;
use App\Models\Label;
use App\Models\LandingPage;
use App\Models\LuckyDraw;
use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function __construct(EventService $service)
    {
        $this->service = $service;
    }

    /**
     * Show the application events index.
     */
    public function index(EventDataTable $dataTable, ListRequest $request)
    {
        if (auth()->user()->isSysAdmin()) {
            $companys = $this->service->company()->getListByAttributes([
                'status'    => [
                    Company::STATUS_ACTIVE,
                ],
            ]);
        }

        $total = $dataTable->getFilter();

        /* get upcoming & happening events */
        $today = Carbon::today();
        $events = $this->service->getEventList();
        $events = $events->filter(function ($event) use ($today) {
            return \Carbon\Carbon::parse($event->to_date)->gte($today);
        })
        ->sortBy('created_at')
        ->groupBy(function ($event) {
            return Carbon::parse($event->from_date)->toDateString();
        });

        // $events = $this->service->getEventList()
        //     ->whereDate('to_date', '>=', $today)   // upcoming or still running
        //     ->orderBy('to_date', 'asc')
        //     ->get()
        //     ->groupBy(function ($event) {
        //         return \Carbon\Carbon::parse($event->to_date)->toDateString();
        //     });

        return $dataTable->render('admin.events.index', [
            'companyArray'          => !empty($companys) ? $companys->mapWithKeys(function ($company) {
                    return [$company->id => "{$company->code} - {$company->name}"];
                })->toArray() : [],
            'proviceArray'          => ["" => "-"] + $this->service->province()->getListByAttributes([], [], [], 0, [
                    'id'            => 'ASC',
                    'is_default'    => 'DESC',
                ])->pluck('name', 'id')->toArray(),
            'total'                 => $total->count(),
            'events'                => $events,
            'today'                 => $today,
        ]);
    }

    /**
     * Display the specified resource edit form.
     */
    public function create(): View
    {
        $companys = $this->service->company()->getListByAttributes([
            'status'    => [
                Company::STATUS_ACTIVE,
            ],
        ]);

        if (!auth()->user()->isSysAdmin()) {
            $company = auth()->user()->company;
        }

        return view('admin.events.create', [
            'model'         => $this->service->init(),
            'company'       => $company ?? null,
            'companyArray'  => $companys->mapWithKeys(function ($company) {
                    return [$company->id => "{$company->code} - {$company->name}"];
                })->toArray(),
            'proviceArray'          => ["" => "-"] + $this->service->province()->getListByAttributes([], [], [], 0, [
                    'id'            => 'ASC',
                    'is_default'    => 'DESC',
                ])->pluck('name', 'id')->toArray(),
            'eventTypeArray'        => ["" => "-"] + $this->service->event_type()->getListByAttributes([], [], [], 0, [
                    'id'            => 'ASC',
                ])->pluck('name', 'id')->toArray(),
        ]);
    }

     /**
     * Display the specified resource edit form.
     */
    public function edit(Event $event)
    {
        $this->authorize('edit', $event);

        $companys = $this->service->company()->getListByAttributes([
            'status'    => [
                Company::STATUS_ACTIVE,
            ],
        ]);

        if (!auth()->user()->isSysAdmin()) {
            $company = auth()->user()->company;
        }

        $creatorName = $this->service->getEventCreator($event->id);
        $getRecentClients = $this->service->getRecentClients($event->id, 5);

        $datas = [
            'model'                 => $event,
            'event'                 => $event,
            'customFieldTemplates'  => $this->service->custom_field_template()->getListByAttributes([
                    'event_id' => $event->id
                ], [], [], 0, [
                    'order' => 'ASC',
                ]),
            'setting'               => $this->service->event_setting()->init(),
            'settings'              => $this->service->event_setting()->getListByAttributes([
                    'event_id'      => $event->id,
                    'status'        => [
                        EventSetting::STATUS_ACTIVE
                    ]
                ], [], [], 0, [
                    'id'            => 'ASC',
                    'group'         => 'ASC',
                    'input_type'    => 'DESC',
                    // 'updated_at'    => 'DESC'
                ]),
            'creatorName'          => $creatorName,
            'recentClients'        => $getRecentClients,
            'landingPages'         => $this->service->landing_page()->getListByAttributes([
                    'event_id'     => $event->id
                ], [], [], 0,
                ['id' => 'desc']),
            'campaigns'            => $this->service->campaign()->getListByAttributes(
                ['event_id' => $event->id],
                [], [], 0,
                ['id' => 'desc']),
            'labels'               => $this->service->label()->getListByAttributes(
                ['event_id' => $event->id],
                [], [], 0,
                ['id' => 'desc']),
            'cards'               => $this->service->card()->getListByAttributes(
                ['event_id' => $event->id],
                [], [], 0,
                ['id' => 'desc']),
        ];

        switch (request()->key) {
            case 'nguoi-tham-du':
                $dataTable = new ClientDataTable($event);

                if (session()->has("import_clients_errors_{$event->id}")) {
                    $this->cancelImport("import_clients_errors_{$event->id}");
                }

                $filteredClientsQuery = $dataTable->getFilter();
                $totalGuests = (clone $filteredClientsQuery)->count();
                $notHavingImgQrcodes = (clone $filteredClientsQuery)->whereNull('img_qrcode')->count();
                $totalCheckedIn = (clone $filteredClientsQuery)->whereExists(function ($subquery) {
                    $subquery->select(DB::raw(1))
                        ->from('checkins')
                        ->whereRaw('clients.event_code = checkins.event_code')
                        ->whereRaw('clients.qrcode = checkins.qrcode');
                })->count();

                /* label */
                $labels = $event->labels;
                $label = $labels->first();

                if (!empty($label)) {
                    $clients = $this->service->client()->getListByAttributes([
                        'event_id'  => $label->event->id,
                        "type"      => $label->type
                    ]);
                }

                $datas = array_merge($datas, [
                    'total'                 => $totalGuests,
                    'notHavingImgQrcodes'   => $notHavingImgQrcodes,
                    'totalCheckedIn'        => $totalCheckedIn,
                    'label'                 => $label ?? null,
                    'labelDetail'           => $this->service->label_detail()->init(),
                    'labelDetails'          => $label->label_details ?? null,
                    'clients'               => $clients ?? null,
                    'cfFilters'             => $this->service->client()->getFilterCustomFields($event->id),
                    'cfTemplate'            => $this->service->custom_field_template()->init(),
                ]);
                break;
            case 'checkin':
                $checkins = [
                    'defaultScreen'         => request()->screen ?? "desktop",
                    'defaultMsg'            => request()->msg ?? "success",
                    'mainBg'                => empty(request()->screen) || request()->screen == "desktop" ?
                        ($event->main_bg_desktop ? $event->mainBgDesktop->getUrl() : null) :
                        ($event->main_bg_mobile ? $event->mainBgMobile->getUrl() : null),
                    'event'                 => $event,
                    'settings'              => $this->service->event_setting()->getListByAttributes([
                        'event_id'          => $event->id,
                        'group'             => strtoupper(request()->screen ?? "desktop"),
                        'status'            => [
                            EventSetting::STATUS_ACTIVE
                        ]
                    ]),
                    'cfTemplate'            => $this->service->custom_field_template()->init(),
                    'screens'               => [
                        // 'desktop'           => 'Desktop/PC/iPad/Tablet '.'<i class="fa-solid fa-desktop"></i> <i class="fa-solid fa-tablet-screen-button"></i>',
                        // 'mobile'            => 'Điện thoại/PDA/Di động '.'<i class="fa-solid fa-mobile-screen"></i>',
                        'desktop'           => '<i class="fa-solid fa-desktop"></i>',
                        'mobile'            => '<i class="fa-solid fa-mobile-screen"></i>',
                    ],
                    'messages'              => [
                        'success'           => [
                            "text"          => "Thành công",
                            "msg"           => __('responses.checkin.success'), // hoặc số lần checkin
                            "showInfo"      => true,
                        ],
                        'failed'            => [
                            "text"          => "Thất bại",
                            "msg"           => __('responses.checkin.errors.no_data_found'),
                            "showInfo"      => false,
                        ],
                        'duplicated'        => [
                            "text"          => "Trùng",
                            "msg"           => __('responses.checkin.errors.duplicate_checkin'),
                            "showInfo"      => true,
                        ],
                    ],
                    'audio'                 => $this->service->checkin()->audio()->init(),
                ];
                $datas = array_merge($datas, $checkins);
                break;
            default:
                $additionalDatas = [
                    'company'               => $company ?? null,
                    'companyArray'          => $companys->mapWithKeys(function ($company) {
                            return [$company->id => "{$company->code} - {$company->name}"];
                        })->toArray(),
                    'proviceArray'          => ["" => "-"] + $this->service->province()->getListByAttributes([], [], [], 0, [
                            'id'            => 'ASC',
                            'is_default'    => 'DESC',
                        ])->pluck('name', 'id')->toArray(),
                    'eventTypeArray'        => ["" => "-"] + $this->service->event_type()->getListByAttributes([], [], [], 0, [
                            'id'            => 'ASC',
                        ])->pluck('name', 'id')->toArray(),
                ];
                $datas = array_merge($datas, $additionalDatas);
                break;
        }

        if (!empty($dataTable)) {
            $datas['dataTable'] = $dataTable;
            return $dataTable->render('admin.events.detail', $datas);
        }
        // dd($datas);
        return view('admin.events.detail', $datas);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventsRequest $request): RedirectResponse
    {
        $attributes = $request->only(['company_id', 'type_id', 'province_id', 'code', 'name', 'status', 'from_date', 'to_date', 'description', 'features']);
        $attributes['code'] = Event::generateUniqueEventCode($attributes['name']);
        $attributes['features'] = json_encode($attributes['features'] ?? []);
        $attributes['created_by'] = auth()->user()->id;
        $attributes['updated_by'] = auth()->user()->id;
        $event = $this->service->create($attributes);

        /* init custom fields template */
        $this->service->custom_field_template()->initByEvent($event);
        $this->service->event_setting()->syncByEvent($event);
        return redirect()->route('admin.events.edit', $event)->withSuccess("Tạo mới thành công");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventsRequest $request, Event $event): RedirectResponse
    {
        $attributes = $request->only(['company_id', 'type_id', 'province_id', 'name', 'status', 'from_date', 'to_date', 'description']);
        // $attributes['features'] = json_encode($attributes['features'] ?? []);
        $attributes['updated_by'] = auth()->user()->id;
        $attributes['status'] = $request->status == Event::STATUS_NEW ? Event::STATUS_ACTIVE : $request->status;
        $this->service->update($event->id, $attributes);

        $logo = $request->file('logo');

        if ($logo) {
            $this->service->attributes['image'] = $logo;
            $this->service->attributes['name'] = $logo->getClientOriginalName();

            if ($result = $this->service->mediaLibraryService()->store()) {
                if (!empty($result['media'])) {
                    $this->service->update($event->id, [
                        'logo' => $result['media']->id
                    ]);

                    if ($event->logo) {
                        $this->service->mediaLibraryService()->deleteMedia($event->logo);
                    }
                } else {
                    return redirect()->route('admin.events.edit', $event)->withErrors($result['msg']);
                }
            }
        }

        return redirect()->route('admin.events.edit', $event)->withSuccess("Cập nhật thành công");
    }

    /**
     * Render a QR code preview image based on current event QR settings.
     */
    public function qrcodePreview(Event $event)
    {
        try {
            $this->authorize('edit', $event);

            $eventSettings = $this->service->event_setting()->getListByAttributes([
                'event_id' => $event->id,
                'group' => EventSetting::GROUP_QRCODE,
                'status' => [
                    EventSetting::STATUS_ACTIVE,
                ],
            ], [], [], 0, [
                'id' => 'ASC',
            ]);

            $config = [
                'white_border' => true,
                'is_barcode' => false,
                'logo_width' => .3,
                // Keep preview stable and avoid generating tons of files.
                'file_name' => 'preview',
            ];

            foreach ($eventSettings as $setting) {
                switch ($setting->name) {
                    case config('event-settings.QRCODE.qrcode_attach_logo.name'):
                        if ($setting->value) {
                            if ($event->logo && is_numeric($event->logo)) {
                                $config['logo_path'] = optional($event->logoUrl)->getPath();
                            } else {
                                $config['logo_path'] = $event->logo;
                            }
                        }
                        break;
                    case config('event-settings.QRCODE.qrcode_logo_width.name'):
                        if (!empty($config['logo_path'])) {
                            $config['logo_width'] = $setting->value;
                        }
                        break;
                    case config('event-settings.QRCODE.qrcode_attach_text.name'):
                        $config['with_text'] = $setting->value ? true : false;
                        break;
                    case config('event-settings.QRCODE.qrcode_color.name'):
                        $config['qrcode_color'] = $setting->value;
                        break;
                    case config('event-settings.QRCODE.qrcode_bg_color.name'):
                        $config['qrcode_bg_color'] = $setting->value;
                        break;
                    case config('event-settings.QRCODE.qrcode_correction.name'):
                        $config['qrcode_correction'] = $setting->value;
                        break;
                    case config('event-settings.QRCODE.qrcode_output.name'):
                        $config['output'] = $setting->value;
                        break;
                    default:
                        break;
                }
            }

            $qrcodeText = strtoupper(($event->code ?: 'EVENT') . '-DEMO');
            $relativePath = Helper::generateImgQrcode($qrcodeText, $event->code ?: 'event', $config);

            if ($relativePath) {
                $filePath = "public/{$relativePath}";
                if (Storage::exists($filePath)) {
                    return response()->file(storage_path("app/{$filePath}"), [
                        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                        'Pragma' => 'no-cache',
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to generate QR preview: ' . $e->getMessage());
        }

        return response()->file(public_path(config('info.placeholders.qrcode')), [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function erase(Event $event)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('checkins')
            ->where('event_id', $event->id)
            ->delete();
		DB::table('clients')
            ->where('event_id', $event->id)
            ->delete();
		DB::table('event_settings')
            ->where('event_id', $event->id)
            ->delete();
		DB::table('event_files')
            ->where('event_id', $event->id)
            ->delete();
		DB::table('custom_field_templates')
            ->where('event_id', $event->id)
            ->delete();
		DB::table('export_datas')
            ->where('event_id', $event->id)
            ->delete();
		DB::table('impexp_files')
            ->where('event_id', $event->id)
            ->delete();

        /* landing_page_campaigns */
        /* landing_page_cards */
        // Get landing_page_campaigns belong to landing_pages belong to $event
        $landingPageIds = DB::table('landing_pages')
            ->where('event_id', $event->id)
            ->pluck('id');
        DB::table('landing_page_campaigns')
            ->whereIn('landing_page_id', $landingPageIds)
            ->delete();
        DB::table('landing_page_cards')
            ->whereIn('landing_page_id', $landingPageIds)
            ->delete();
        DB::table('language_defines')
            ->where('event_id', $event->id)
            ->delete();
        /* landing_pages */
		DB::table('landing_pages')
            ->whereIn('id', $landingPageIds)
            ->delete();

		DB::table('campaigns')
            ->where('event_id', $event->id)
            ->delete();

        /* lucky_draws */
        $luckyDrawIds = DB::table('lucky_draws')
            ->where('event_id', $event->id)
            ->pluck('id');
		DB::table('lucky_draw_clients')
            ->whereIn('lucky_draw_id', $luckyDrawIds)
            ->delete();
		DB::table('lucky_draw_rewards')
            ->whereIn('lucky_draw_id', $luckyDrawIds)
            ->delete();
		DB::table('lucky_draws')
            ->whereIn('id', $luckyDrawIds)
            ->delete();
        /* end */

		DB::table('cards')
            ->where('event_id', $event->id)
            ->update([
                'status' => Card::STATUS_DELETED
            ]);

        /* labels */
        $labelIds = DB::table('labels')
            ->where('event_id', $event->id)
            ->pluck('id');
        DB::table('label_details')
            ->whereIn('label_id', $labelIds)
            ->delete();
        DB::table('labels')
            ->whereIn('id', $labelIds)
            ->delete();
        /* end */

        DB::table('users')
            ->where('event_id', $event->id)
            ->delete();
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $event->delete();
        return redirect()->route('admin.events.index')->withSuccess("Đã xoá toàn bộ dữ liệu của sự kiện thành công");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $this->service->deleteOnStatus($event->id);
        return redirect()->route('admin.events.index')->withSuccess("Đã xoá sự kiện thành công");
    }

    public function uploadMedias(UploadMediaRequest $request, Event $event)
    {
        $uploadedSomething = false;
        $files = $request->only([
            'main_bg_desktop',
            'main_bg_mobile',
            'logo',
            'favicon',
        ]);

        $sounds = $request->only([
            'sound_success',
            'sound_fail',
        ]);

        // if (count($sounds)) {
        //     foreach ($sounds as $key => $sound) {
        //         $path = $sound->store('sounds', 'public');
        //         $this->service->update($event->id, [
        //             $key => "medias/{$path}"
        //         ]);
        //     }

        //     $uploadedSomething = true;
        // }

        if (count($files)) {
            foreach ($files as $key => $file) {
                if ($file) {
                    $this->service->attributes['image'] = $file;

                    if ($result = $this->service->mediaLibraryService()->store()) {
                        if (!empty($result['media'])) {
                            $this->service->update($event->id, [
                                $key => $result['media']->id
                            ]);

                            if ($event->$key) {
                                $this->service->mediaLibraryService()->deleteMedia($event->$key);
                            }
                        } else {
                            return back()->withErrors($result['msg']);
                        }
                    }
                }
            }

            $uploadedSomething = true;
        }

        if (!$uploadedSomething) {
            return back()->withErrors('Không thể xử lý file');
        }

        return back()->withSuccess('Nạp file thành công');
    }

    /**
     * Display the specified resource edit form.
     */
    public function getListByCompanyId($companyId)
    {
        $events = $this->service->getListByAttributes([
            'company_id' => $companyId,
        ]);

        if (!empty($events)) {
            return $this->responseSuccess([
                'list' => $events,
            ]);
        }

        return $this->responseError("Không tìm thấy thông tin công ty {$companyId}");
    }

    public function updateCustomCheckinMessages(Event $event, UpdateCustomCheckinMessageRequest $request)
    {
        $customCheckinMessages = $request->custom_checkin_messages;
        $defaultCustomCheckinMessages = $event->custom_checkin_messages ? json_decode($event->custom_checkin_messages, true) : [];

        // foreach ($checkins as $screen => $configs) {
        foreach ($customCheckinMessages as $screen => $customCheckinMessageAttrs) {
            foreach ($customCheckinMessageAttrs as $msg => $attrs) {
                /* set for boolean columns */
                foreach ([
                    'bold',
                    'italic',
                    'underline',
                    'bg',
                    'show_info',
                ] as $field) {
                    if (isset($attrs[$field])) {
                        $attrs[$field] = (($attrs[$field] == "true" || $attrs[$field] == "1") ? 1 : 0);
                    } else {
                        $attrs[$field] = 0;
                    }

                    if (in_array($field, [
                        'show_info'
                    ])) {
                        if (in_array($msg, [
                            'success',
                        ])) {
                            $attrs[$field] = true;
                        }
                    }
                }

                $defaultCustomCheckinMessages[$screen][$msg] = $attrs;
            }

            if (count($defaultCustomCheckinMessages)) {
                $this->service->update($event->id, [
                    'custom_checkin_messages' => $defaultCustomCheckinMessages,
                ]);
            }
        }

        return $this->responseSuccess(null, "Đã cập nhật");
    }

    public function clone(CloneRequest $request, Event $event)
    {
        /* validate confirm */
        $request->validate([
            'confirm' => ['required', 'string', 'max:20', 'in:COPY'],
        ]);

        $newEvent = $this->service->cloneModel($event, [
            'ref_id'        => $event->id,
            'company_id'    => $request->company_id,
            // 'code'          => $request->code,
            'code'          => Event::generateUniqueEventCode($request->name),
            'name'          => $request->name,
            'from_date'     => $request->from_date,
            'to_date'       => $request->to_date,
            'status'        => Event::STATUS_NEW,
            'created_by'    => auth()->user()->id,
            'updated_by'    => auth()->user()->id,
        ]);

        /* clone custom fields template */
        $customFieldTemplates = $event->custom_field_templates;
        foreach ($customFieldTemplates as $customFieldTemplate) {
            $this->service->custom_field_template()->cloneModel($customFieldTemplate, [
                'event_id' => $newEvent->id,
            ]);
        }

        /* clone settings */
        $settings = $event->getEventSettings();
        foreach ($settings as $setting) {
            $this->service->event_setting()->cloneModel($setting, [
                'event_id' => $newEvent->id,
            ]);
        }

        return redirect()->route('admin.events.index')
            ->withSuccess("Đã nhân bản sự kiện {$newEvent->code}");
    }

    public function updateFeatures(UpdateFeatureRequest $request, Event $event)
    {
        $attributes['features'] = json_encode($request->features);
        $this->service->update($event->id, $attributes);
        return redirect()->route('admin.events.edit', $event)
            ->withSuccess("Cập nhật tính năng thành công");
    }

    public function removeFeature(RemoveFeatureRequest $request, Event $event)
    {
        $features = json_decode($event->features, true);
        // dd($features, $request->feature);

        if ($event->hasFeature($request->feature)) {
            $toRemoveFeature = $request->feature;
            $features = array_values(array_filter($features, fn($item) => $item !== $toRemoveFeature));
        }

        $this->service->update($event->id, [
            'features' => $features
        ]);

        return redirect()->route('admin.events.edit', $event)
            ->withSuccess("Đã ẩn tính năng thành công");
    }

    public function getClientTypesByEvent($eventId) {
        $types = $this->service->client()->getListByAttributes([
            'event_id' => $eventId,
        ]);

        $types = $this->service->removeEmptyElementInArray(
            $types->pluck('type', 'type')->toArray()
        );

        foreach ($types as $key => $type) {
            $count = $this->service->client()->getListByAttributes([
                'event_id' => $eventId,
                'status'   => [Client::STATUS_ACTIVE, Client::STATUS_NEW],
                'type'     => $key,
            ], [
                'email'    => null,
            ])->count();

            $types[$key] = "{$type} ({$count})";
        }

        if (!empty($types)) {
            return $this->responseSuccess([
                'list' => $types,
            ]);
        }

        return $this->responseError("Không tìm thấy thông tin loại khách hàng cho sự kiện {$eventId}");
    }
}
