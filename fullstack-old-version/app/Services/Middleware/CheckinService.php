<?php
namespace App\Services\Middleware;

use App\Models\Checkin;
use App\Models\Client;
use App\Models\Event;
use App\Models\EventSetting;
use App\Services\BaseService;

class CheckinService extends BaseService
{
    public $event;
    public $eventCode;
    public $qrcode;
    public $scanTime;
    public $byPassDuplicate = false;

    public function __construct(string $eventCode, ?string $qrcode = null, ?string $scanTime = null, bool $byPassDuplicate = false)
    {
        $this->model = resolve(Checkin::class);
        $this->eventCode = $eventCode;
        $this->qrcode = $qrcode;
        $this->scanTime = $scanTime ?? now()->format('Y-m-d H:i:s');
        $this->byPassDuplicate = $byPassDuplicate;
    }

    public function event()
    {
        return app(EventService::class);
    }
    public function client()
    {
        return new ClientService();
    }

    public function checkin()
    {
        $this->attributes['type'] = !empty($this->attributes['type']) ? $this->attributes['type'] : null;
        $this->attributes['custom_fields'] = !empty($this->attributes['custom_fields']) ? $this->attributes['custom_fields'] : [];
        return $this->check();
    }

    public function multiCheckin()
    {
        $this->attributes['type'] = !empty($this->attributes['type']) ? $this->attributes['type'] : null;
        $totalRecords = $this->attributes['total_records'];
        $datas = $this->attributes['data'];

        if (count($datas) > 0) {
            $record = 0;

            foreach ($datas as $index => $data) {
                $this->qrcode = $data['qrcode'];
                $this->scanTime = $data['scan_time'] ?? now()->format('Y-m-d H:i:s');
                $this->attributes['custom_fields'] = !empty($data['custom_fields']) ? $data['custom_fields'] : [];

                if ($result = $this->check()) {
                    if (is_array($result)) {
                        if ($result['checkin']) {
                            if ($result['model']) {
                                $record++;
                                continue;
                            }
                        }

                        $errors[++$index] = [
                            'qrcode'    => $this->qrcode,
                            'error'     => $result['msg'],
                        ];
                    }
                }
            }

            return [
                'checkin'       => true,
                'msg'           => [
                    'total'     => "{$totalRecords} record(s)",
                    'checked'   => $record,
                    'failed'    => count($errors ?? [])." record(s)",
                    'errors'    => $errors ?? [],
                ],
            ];
        }

        return [
            'checkin'       => false,
            'msg'           => __('responses.checkin.errors.no_data_found'),
        ];;
    }

    public function getCustomMessages()
    {
        $userGroup = $this->attributes['user_group'] ?? EventSetting::GROUP_DESKTOP;
        $customCheckinMessages = $this->getRedis("checkin_custom_messages", $this->event->code, "array");

        if (!count($customCheckinMessages)) {
            $customCheckinMessages = $this->event->custom_checkin_messages ? json_decode($this->event->custom_checkin_messages, true) : [];
            $this->updateRedis("checkin_custom_messages", $this->event->code, json_encode($customCheckinMessages), config("app.times.seconds.five-minutes"));
            $customCheckinMessages = $this->getRedis("checkin_custom_messages", $this->event->code, "array");
        }

        $customCheckinMessages = count($customCheckinMessages) && isset($customCheckinMessages[strtolower($userGroup)]) ? $customCheckinMessages[strtolower($userGroup)] : [];
        return $customCheckinMessages;
    }

    public function getEventSettings()
    {
        $userGroup = $this->attributes['user_group'] ?? EventSetting::GROUP_DESKTOP;
        $eventSettings = $this->getRedis("event_settings:{$userGroup}", $this->event->code, "array");

        if (empty($eventSettings) || !count($eventSettings)) {
            $eventSettings = $this->event->getEventSettings($userGroup);
            $this->updateRedis("event_settings:{$userGroup}", $this->event->code, json_encode($eventSettings), config("app.times.seconds.five-minutes"));
            $eventSettings = $this->getRedis("event_settings:{$userGroup}", $this->event->code, "array");
        }

        $checkinSettings = $this->getRedis("checkin_settings:{$userGroup}", $this->event->code, "array");
        if (empty($checkinSettings) || !count($checkinSettings)) {
            $checkinSettings = $this->event->getEventSettings($userGroup);
            $settingKeys = [
                'show_checkin_count',
                'allow_checkin_nodata',
                'allow_checkin_by_date',
                'allow_checkin_by_user',
                'no_duplicate_checkin',
            ];

            $eventSettings = array_filter($eventSettings, function ($setting) use ($settingKeys) {
                return isset($setting['name']) && in_array(strtolower($setting['name']), $settingKeys);
            });

            foreach ($eventSettings as $setting) {
                if (isset($setting['name']) && isset($setting['value'])) {
                    $checkinSettings[strtolower($setting['name'])] = $setting['value'] ?? 0;
                }
            }
            $this->updateRedis("checkin_settings:{$userGroup}", $this->event->code, json_encode($checkinSettings), config("app.times.seconds.five-minutes"));
            $checkinSettings = $this->getRedis("checkin_settings:{$userGroup}", $this->event->code, "array");
        }

        return $checkinSettings;
    }

    public function check()
    {
        $type = !empty(auth()->user()) && auth()->user()->is_checkout ? Checkin::TYPE_CHECKOUT : Checkin::TYPE_CHECKIN;
        $responseType = !empty(auth()->user()) && auth()->user()->is_checkout ? "checkout" : "checkin";
        // $this->event = $this->event()->findByAttributes([
        $this->event = Event::where([
            'code' => $this->eventCode,
        ])->first();

        if (empty($this->event)) return [
            'checkin'       => false,
            'is_duplicated' => false,
            'msg'           => __("responses.{$responseType}.errors.event_not_found", [
                'code'      => $this->eventCode
            ]),
        ];

        // $userGroup = $this->attributes['user_group'] ?? EventSetting::GROUP_DESKTOP;
        $settings = $this->getEventSettings();
        $customCheckinMessages = $this->getCustomMessages();
        // $customCheckinMessages = $this->event->custom_checkin_messages ? json_decode($this->event->custom_checkin_messages, true) : [];
        // $customCheckinMessages = count($customCheckinMessages) && isset($customCheckinMessages[strtolower($userGroup)]) ? $customCheckinMessages[strtolower($userGroup)] : [];

        if (empty($settings) || $settings['allow_checkin_nodata']) {
            $checkin = $this->storeCheckin(null, [], $type);
            if ($checkin) {
                return [
                    'checkin'       => true,
                    'is_duplicated' => false,
                    'msg'           => $settings['show_checkin_count'] ? __("responses.{$responseType}.successes.checkin_count", [
                        'count'     => $checkin->checkin_count,
                    ]) : ($customCheckinMessages['success']['msg'] ?? __("responses.{$responseType}.successes.checkin_no_data")),
                    'count'         => $settings['show_checkin_count'] ? $checkin->checkin_count : null,
                    'model'         => $checkin,
                ];
            }
        }

        /* Find client */
        // $client = $this->client()->findByAttributes([
        //     'event_id'  => $this->event->id,
        //     'qrcode'    => $this->qrcode
        // ]);
        $client = Client::where([
                'event_id'  => $this->event->id,
                'qrcode'    => $this->qrcode
            ])
            ->whereIn('status', [
                Client::STATUS_ACTIVE,
                Client::STATUS_NEW,
            ])
            ->first();

        if (empty($client)) {
            return [
                'checkin'       => false,
                'is_duplicated' => false,
                'msg'           => $customCheckinMessages['failed']['msg'] ?? __("responses.{$responseType}.errors.client_not_found", [
                    'qrcode'    => $this->qrcode,
                ]),
            ];
        }

        $skipCheckForDuplicate = false;

        /* theo khu vực */
        if (!empty(auth()->user()->area_id)) {
            if (in_array($client->type, auth()->user()->area->client_types)) {
                /* CHECK FOR CHECKIN DUPLICATE ON AREA */
                if ($settings['no_duplicate_checkin'] && !$this->byPassDuplicate) {
                    $userId = auth()->user()->id;
                    $userIds = auth()->user()->area->users->pluck('id')->toArray();

                    $checkin = Checkin::where([
                        'event_id'  => $this->event->id,
                        'qrcode'    => $this->qrcode,
                        'type'      => $type,
                    ])->first();

                    if ($checkin) {
                        if (in_array($userId, $userIds)) {
                            $checkin->where([
                                'user_id'   => $userId,
                            ]);
                        } else {
                            $checkin->whereIn('user_id', $userIds);
                        }

                        return [
                            'checkin'       => false,
                            'is_duplicated' => true,
                            'msg'           => $customCheckinMessages['duplicated']['msg'] ?? __("responses.{$responseType}.errors.duplicate_checkin"),
                            'model'         => $checkin,
                            'count'         => $checkin->checkin_count ?? null,
                            'client'        => $customCheckinMessages['duplicated']['show_info'] ? $client : null,
                        ];
                    }

                    $skipCheckForDuplicate = true;
                }
            } else {
                return [
                    'checkin'       => false,
                    'is_duplicated' => false,
                    'msg'           => $customCheckinMessages['failed']['msg'] ?? __("responses.{$responseType}.errors.client_not_found", [
                        'qrcode'    => $this->qrcode,
                    ]),
                ];
            }
        }

        /* CHECK FOR CHECKIN DUPLICATE */
        if (!$skipCheckForDuplicate) {
            if ($settings['no_duplicate_checkin'] && !$this->byPassDuplicate) {
                $checkin = Checkin::where([
                    'event_id'  => $this->event->id,
                    'qrcode'    => $this->qrcode,
                    'type'      => $type,
                ])->first();

                if ($checkin) {
                    return [
                        'checkin'       => false,
                        'is_duplicated' => true,
                        'msg'           => $customCheckinMessages['duplicated']['msg'] ?? __("responses.{$responseType}.errors.duplicate_checkin"),
                        'model'         => $checkin,
                        'count'         => $checkin->checkin_count ?? null,
                        'client'        => $customCheckinMessages['duplicated']['show_info'] ? $client : null,
                    ];
                }
            }
        }

        $checkin = $this->storeCheckin($client, $this->attributes['custom_fields'], $type);

        if ($checkin) {
            return [
                'checkin'       => true,
                'is_duplicated' => false,
                'msg'           => $settings['show_checkin_count'] ? __("responses.{$responseType}.successes.checkin_count", [
                    'count'     => $checkin->checkin_count,
                ]) : ($customCheckinMessages['success']['msg'] ?? __("responses.{$responseType}.success")),
                'count'         => $checkin->checkin_count ?? null,
                'model'         => $checkin,
                'client'        => isset($customCheckinMessages['success']['show_info']) ? $client : null,
                // 'client'        => $client,
            ];
        }
    }

    public function storeCheckin($modelClient = null, $customFields = [], $type = Checkin::TYPE_CHECKIN)
    {
        $attributes = [
            'event_id'      => $this->event->id,
            'event_code'    => $this->event->code,
            'user_id'       => auth()->user()->id ?? null,
            // 'device_name'   => auth()->user()->username ?? null,
            'client_name'   => (!empty($modelClient) && !empty($modelClient->name)) ? $modelClient->name : Checkin::NO_DATA_NAME,
            'qrcode'        => $this->qrcode,
            'scan_time'     => $this->scanTime,
            'type'          => $type,
            'status'        => Checkin::STATUS_NEW,
            'custom_fields' => json_encode($customFields),
        ];

        if ($model = $this->create($attributes)){
            if (!empty(auth()->user()->area_id)) {
                if (in_array($modelClient->type, auth()->user()->area->client_types)) {
                    $userId = auth()->user()->id;
                    $userIds = auth()->user()->area->users->pluck('id')->toArray();
                    if (in_array($userId, $userIds)) {
                        $userId = $userIds;
                    }
                }
            }

            $model->checkin_count = $this->getListByAttributes(
                array_filter([
                    'event_id'  => $this->event->id,
                    'qrcode'    => $this->qrcode,
                    'type'      => $type,
                    'user_id'   => $userId ?? null,
                ])
            )->count();
            return $model;
        }

        return null;
    }
}
