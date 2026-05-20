<?php

namespace App\Http\Controllers\Scan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Scan\CheckinRequest;
use App\Http\Requests\Scan\MultiCheckinRequest;
use App\Services\Scan\ScanService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use App\Models\Event;
use App\Models\EventSetting;
use App\Models\Label;
use App\Models\LabelDetail;
use App\Models\CustomFieldTemplate;

class ScanController extends Controller
{
    public function __construct(ScanService $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    public function index()
    {
        $user = Auth::user();

        return view('scan.index', [
            'events' => $user->event_id
                ? $user->company->events->where('id', $user->event_id)->values()
                : $user->company->events,
        ]);
    }

    public function scan(Event $event)
    {
        $agent = new Agent();

        if (auth()->user()->event_id) {
            if ($event->id != auth()->user()->event_id) {
                abort(404);
            }
        }

        if ($event->company_id !== auth()->user()->company_id) {
            abort(404);
        }

        $mainBg = $event->main_bg_mobile ? $event->mainBgMobile->getUrl() : null;
        $screen = "mobile";
        $col = 'is_checkin_mobile';
        $userGroup = EventSetting::GROUP_MOBILE;
        $labels = $event->labels;
        $label = $labels->first();

        if ($agent->isDesktop()) {
            $mainBg = $event->main_bg_desktop ? $event->mainBgDesktop->getUrl() : null;
            $screen = "desktop";
            $col = 'is_checkin_desktop';
            $userGroup = EventSetting::GROUP_DESKTOP;
        }

        $eventSettings = $event->getEventSettings($userGroup)
            ->pluck('value', 'name')
            ->toArray();

        /* sync settings from redis */
        $this->service->attributes['event_code'] = $event->code;
        $this->service->attributes['user_group'] = $userGroup;
        // $this->service->getEventSettings();

        return view('scan.scan', [
            'event'                 => $event,
            'screen'                => $screen,
            'mainBg'                => $mainBg,
            'cfTemplate'            => $this->service->custom_field_template()->init(),
            'customFieldTemplates'  => $this->service->custom_field_template()->getListByAttributes([
                'event_id'          => $event->id,
                $col                => true,
            ], [], [], 0, [
                'order'             => 'ASC',
            ]),
            // 'eventSettings'         => $eventSettings,
            'customCheckinMessages' => $event->custom_checkin_messages ? (json_decode($event->custom_checkin_messages, true)[$screen] ?? []) : [],
            'label'                 => isset($eventSettings['ALLOW_CHECKIN_PRINT']) && $eventSettings['ALLOW_CHECKIN_PRINT'] ? ($label ?? null) : null,
            'agent'                 => $agent,
            'clients'               => $this->service->client()->getListByAttributes([
                'event_id'          => $event->id,
            ]),
        ]);
    }

    public function saveLayout(Event $event, Request $request)
    {
        $user = auth()->user();

        if ($user->event_id && $event->id != $user->event_id) {
            abort(404);
        }

        if ($event->company_id !== $user->company_id) {
            abort(404);
        }

        $validated = $request->validate([
            'screen' => ['required', 'string', 'in:desktop,mobile'],
            'elements' => ['required', 'array'],
            'elements.*.type' => ['required', 'string', 'in:field,msg'],
            'elements.*.key' => ['required', 'string', 'max:191'],
            'elements.*.pos_x' => ['nullable', 'numeric', 'min:-20', 'max:120'],
            'elements.*.pos_y' => ['nullable', 'numeric', 'min:-20', 'max:120'],
            'elements.*.font_size' => ['nullable', 'numeric', 'min:10', 'max:500'],
            'elements.*.width' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'elements.*.bold' => ['nullable', 'boolean'],
            'elements.*.italic' => ['nullable', 'boolean'],
            'elements.*.underline' => ['nullable', 'boolean'],
            'elements.*.bg' => ['nullable', 'boolean'],
            'elements.*.bg_color' => ['nullable', 'string', 'max:16'],
            'elements.*.color' => ['nullable', 'string', 'max:16'],
            'elements.*.stroke' => ['nullable', 'string', 'max:16'],
            'elements.*.font' => ['nullable', 'string', 'max:64'],
            'elements.*.align' => ['nullable', 'string', 'in:left,center,right'],
        ]);

        $screen = $validated['screen'];
        $elements = $validated['elements'];

        $allowedAttrs = [
            'pos_x',
            'pos_y',
            'font_size',
            'width',
            'bold',
            'italic',
            'underline',
            'bg',
            'bg_color',
            'color',
            'stroke',
            'font',
            'align',
        ];

        $fieldKeys = collect($elements)
            ->filter(fn ($el) => ($el['type'] ?? null) === 'field')
            ->pluck('key')
            ->unique()
            ->values()
            ->all();

        if (!empty($fieldKeys)) {
            $templates = CustomFieldTemplate::query()
                ->where('event_id', $event->id)
                ->whereIn('name', $fieldKeys)
                ->get()
                ->keyBy('name');

            foreach ($elements as $el) {
                if (($el['type'] ?? null) !== 'field') continue;

                $name = $el['key'] ?? null;
                if (!$name || !$templates->has($name)) continue;

                /** @var CustomFieldTemplate $tpl */
                $tpl = $templates->get($name);
                $checkins = is_array($tpl->checkins) ? $tpl->checkins : [];
                $checkins[$screen] = is_array($checkins[$screen] ?? null) ? $checkins[$screen] : [];

                foreach ($allowedAttrs as $attr) {
                    if (!array_key_exists($attr, $el) || $el[$attr] === null) continue;
                    $checkins[$screen][$attr] = is_numeric($el[$attr]) ? round((float)$el[$attr], 2) : $el[$attr];
                }

                $tpl->checkins = $checkins;
                $tpl->save();
            }
        }

        $msgKeys = collect($elements)
            ->filter(fn ($el) => ($el['type'] ?? null) === 'msg')
            ->pluck('key')
            ->unique()
            ->values()
            ->all();

        if (!empty($msgKeys)) {
            $allMessages = [];
            if (!empty($event->custom_checkin_messages)) {
                $decoded = json_decode($event->custom_checkin_messages, true);
                $allMessages = is_array($decoded) ? $decoded : [];
            }

            $allMessages[$screen] = is_array($allMessages[$screen] ?? null) ? $allMessages[$screen] : [];

            foreach ($elements as $el) {
                if (($el['type'] ?? null) !== 'msg') continue;

                $key = $el['key'] ?? null;
                if (!$key) continue;

                $allMessages[$screen][$key] = is_array($allMessages[$screen][$key] ?? null) ? $allMessages[$screen][$key] : [];

                foreach ($allowedAttrs as $attr) {
                    if (!array_key_exists($attr, $el) || $el[$attr] === null) continue;
                    $allMessages[$screen][$key][$attr] = is_numeric($el[$attr]) ? round((float)$el[$attr], 2) : $el[$attr];
                }
            }

            $event->custom_checkin_messages = json_encode($allMessages, JSON_UNESCAPED_UNICODE);
            $event->save();
        }

        return $this->responseSuccess([
            'screen' => $screen,
            'saved' => count($elements),
        ], 'Đã lưu bố cục màn hình checkin');
    }

    public function checkin(CheckinRequest $request)
    {
        $agent = new Agent();
        $this->service->attributes = $request->all();
        $this->service->attributes['user_group'] = EventSetting::GROUP_MOBILE;

        if ($agent->isDesktop()) {
            $this->service->attributes['user_group'] = EventSetting::GROUP_DESKTOP;
        }

        if ($result = $this->service->checkin()) {
            if (is_array($result)) {
                $client = $result['client'] ?? null;

                return $this->responseSuccess([
                    'checkin'       => $result['checkin'] ?? false,
                    'model'         => $result['model']?? null,
                    'fields'        => !empty($client) ? $client->getFullFields() : [],
                    'is_duplicated' => $result['is_duplicated'] ?? false,
                ], $result['msg']);

                // return $this->responseSuccess([
                //     'checkin'       => false,
                //     'model'         => $result['model']?? null,
                //     'fields'        => !empty($client) ? $client->getFullFields() : [],
                //     'is_duplicated' => $result['is_duplicated'] ?? false,
                // ], $result['msg']);

                // return $this->responseError($result['msg'], 400);
            }
        }

        return $this->responseError(__('responses.error'), 400);
    }

    public function renderLabel(Label $label, Request $request)
    {
        return $this->responseSuccess([
            'html' => view('components.label_details.to-print', [
                'label'             => $label,
                'labelDetails'      => $label->label_details->where('status', '!=', LabelDetail::STATUS_DELETED) ?? null,
                'event'             => $label,
                'display'           => true,
                'client'            => $request->client_id ? $this->service->client()->findByAttributes([
                    'id' => $request->client_id
                ]) : null,
            ])->render()
        ]);
    }

    public function syncOffline(MultiCheckinRequest $request)
    {
        $agent = new Agent();
        $this->service->attributes = $request->all();
        $this->service->attributes['user_group'] = EventSetting::GROUP_MOBILE;

        if ($agent->isDesktop()) {
            $this->service->attributes['user_group'] = EventSetting::GROUP_DESKTOP;
        }

        if ($this->service->multiCheckin()) {
            return $this->responseSuccess([
                'synced_at'     => date('Y-m-d H:i:s'),
            ], 'Đã đồng bộ danh sách checkin thành công');
        } else {
            return $this->responseError('Không tìm thấy thông tin', 400);
        }
    }
}
