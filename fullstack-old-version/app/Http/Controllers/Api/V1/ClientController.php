<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Clients\FindByQrcodeRequest;
use App\Http\Requests\Api\Clients\FindRequest;
use App\Http\Requests\Api\Clients\GenerateQrcodeOnSettingRequest;
use App\Http\Requests\Api\Clients\StoreRequest;
use App\Http\Requests\Api\Clients\UpsertByIdRequest;
use App\Http\Requests\Api\Clients\UpsertRequest;
use App\Services\Api\ClientService;
use App\Http\Resources\ClientWithEvent as ClientWithEventResource;
use App\Http\Resources\Client as ClientResource;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\CustomFieldTemplate;
use App\Models\Event;

class ClientController extends Controller
{
    public function __construct(ClientService $service)
    {
        $this->service = $service;
    }

    public function generateQrcodeOnSetting(Event $event, GenerateQrcodeOnSettingRequest $request)
    {
        $attributes = $request->only([
            'name',
            'email',
            'custom_fields',
        ]);

        return $event->generateQrcodeOnSetting(
            $event->code,
            $attributes['custom_fields']['phone'] ?? null,
            $attributes['email'] ?? null,
            $attributes['phone'] ?? null,
            $attributes['custom_fields'] ?? []
        );
    }

    public function find(FindRequest $request)
    {
        $attributes = array_filter($request->only([
            'event_id',
            'event_code',
            'qrcode',
            'id',
        ]));

        $client = $this->service->findByAttributes($attributes);

        if ($client) {
            return $this->responseSuccess(new ClientWithEventResource($client), "Khách mời");
        }

        return $this->responseError("Không tìm thấy khách mời", 404);
    }

    public function register(StoreRequest $request)
    {
        $client = null;
        $msg = "Tạo mới thành công";
        $attributes = $request->only([
            'id',
            'event_code',
            'qrcode',
            'name',
            'email',
            'status',
            'type',
            'custom_fields',
            'lang',
            'ref_id'
        ]);

        $event = $this->service->event()->findByAttributes([
            'code' => $attributes['event_code'],
        ]);

        if (!$event) {
            return $this->responseError("Không tìm thấy sự kiện", 404);
        }

        if (isset($attributes['id'])) {
            $id = $attributes['id'];
            $client = $this->service->findByAttributes([
                'event_code'    => $event->code,
                'id'            => $id,
            ]);
        }

        $customFields = $attributes['custom_fields'] ?? [];
        $attributes['event_id'] = $event->id;
        // $attributes['event_code'] = $event->code;
        $attributes['register_source'] = "LANDING_PAGE";

        /* customize */
        /* long-kan */
        if ($event->code == "long-kan") {
            $attributes['custom_fields']['lucky'] = $event->clients->count() == 0 ? "0001" : str_pad((int)$event->clients->last()->custom_fields['lucky'] + 1, 4, "0", STR_PAD_LEFT);
        }
        /* VIETNAMWATERWEEK2025 */
        if ($event->code == "VIETNAMWATERWEEK2025") {
            $oldNumber = $event->clients->last()->custom_fields['number'] ?? "VWW-00001";
            $oldNumber = substr($oldNumber, 4);
            $attributes['custom_fields']['number'] = "VWW-".($event->clients->count() == 0 ? "00001" : str_pad((int)$oldNumber + 1, 5, "0", STR_PAD_LEFT));
        }

        /* get qrcode if empty */
        if (!isset($attributes['qrcode'])) {
            $attributes['qrcode'] = $event->generateQrcodeOnSetting($event->code, $customFields['phone'] ?? null, $attributes['email'] ?? null, $attributes['name'], $customFields ?? []);
        }

        /* get landing page id */
        if ($request->slug) {
            $landingPage = $this->service->landing_page()->findByAttributes([
                'slug' => $request->slug,
            ]);

            if ($landingPage) {
                $attributes['lp_id'] = $landingPage->id;
            }
        }

        /* handle files */
        $typeFiles = $event->getCustomFieldTemplates(false, null, null, [
            'type' => CustomFieldTemplate::TYPE_FILE
        ]);

        if (count($typeFiles)) {
            foreach ($typeFiles as $key => $detail) {
                if ($request->hasFile("custom_fields.{$key}")) {
                    $files = $request->file("custom_fields.{$key}");
                    $attributes['custom_fields'][$key] = [];
                    foreach ($files as $file) {
                        if ($file->isValid()) {
                            $filename = time().'_'.$file->getClientOriginalName();
                            $path = $file->storeAs('uploads', $filename, 'public');
                            $attributes['custom_fields'][$key][] = $path;
                        }
                    }
                }
            }
        }

        if ($client) {
            /* update */
            unset($attributes['id']);
            $attributes['qrcode'] = $client->qrcode;
            $this->service->update($client->id, $attributes);
            $client->refresh();
            $msg = "Cập nhật thành công";
        } else {
            /* create */
            $client = $this->service->create($attributes);
        }

        /* generate img_qrcode */
        $this->service->update($client->id, [
            'img_qrcode' => $client->generateImgQrcode(),
        ]);

        /* register */
        $this->service->attributes['campaign_id'] = $request->campaign_id ?? null;
        $this->service->attributes['card_id'] = $request->card_id ?? null;
        $this->service->attributes['event_code'] = $event->code;
        $this->service->attributes['qrcode'] = $attributes['qrcode'];
        $result = $this->service->register($event, $client);

        // $landingPage = $this->service->landing_page()->findByAttributes([
        //     'slug' => $slug,
        // ]);

        // if ($landingPage) {
        //     return redirect()->route('landing_pages.success', [
        //         'slug'      => $landingPage->slug,
        //         'qrcode'    => $client->qrcode
        //     ])->with('success', $result['msg']);
        // }

        return $this->responseSuccess(new ClientWithEventResource($client->refresh()), $result['msg']);
        return redirect()->route('home')->with('success', $msg);
    }

    public function upsert(UpsertRequest $request)
    {
        $client = null;
        $msg = "Tạo mới thành công";
        $attributes = $request->only([
            'event_id',
            'qrcode',
            'name',
            'email',
            'status',
            'type',
            'custom_fields',
            'lang',
            'ref_id'
        ]);

        $event = $this->service->event()->findByAttributes([
            'id' => $attributes['event_id'],
        ]);

        if (!$event) {
            return $this->responseError("Không tìm thấy sự kiện", 404);
        }

        if (!$this->service->user()->validateApiUser($request->getUser(), $event)) {
            return $this->responseError("Thông tin không hợp lệ", 404);
        }

        $customFields = $attributes['custom_fields'] ?? [];
        $attributes['event_id'] = $event->id;
        $attributes['event_code'] = $event->code;
        $attributes['register_source'] = Client::REGISTER_API;

        if (isset($attributes['qrcode'])) {
            $qrcode = $attributes['qrcode'];
            $client = $this->service->findByAttributes([
                'event_code'    => $event->code,
                'qrcode'        => $qrcode,
            ]);
        } else {
            /* get qrcode if empty */
            $attributes['qrcode'] = $event->generateQrcodeOnSetting(
                $event->code,
                $customFields['phone'] ?? null,
                $attributes['email'] ?? null,
                $attributes['name'],
                $customFields ?? []
            );
        }

        /* get landing page id */
        if ($request->slug) {
            $landingPage = $this->service->landing_page()->findByAttributes([
                'slug' => $request->slug,
            ]);

            if ($landingPage) {
                $attributes['lp_id'] = $landingPage->id;
            }
        }

        if ($client) {
            /* update */
            unset($attributes['id']);
            $attributes['qrcode'] = $client->qrcode;
            $this->service->update($client->id, $attributes);
            $client->refresh();
            $msg = "Cập nhật thành công";
        } else {
            /* create */
            $client = $this->service->create($attributes);
            /* generate img_qrcode */
            $this->service->update($client->id, [
                'img_qrcode' => $client->generateImgQrcode(),
            ]);
        }

        /* register */
        /* hidec */
        $this->service->attributes['campaign_id'] = ($event->id == 39 ? 37 : null) ?? null;
        $this->service->attributes['event_code'] = $event->code;
        $this->service->attributes['qrcode'] = $attributes['qrcode'];
        $this->service->register($event, $client);
        return $this->responseSuccess(new ClientResource($client), $msg);
    }

    public function upsertById(UpsertByIdRequest $request)
    {
        $client = null;
        $msg = "Tạo mới thành công";
        $attributes = $request->only([
            'id',
            'event_id',
            'name',
            'email',
            'status',
            'type',
            'custom_fields',
            'lang',
            'ref_id'
        ]);

        $event = $this->service->event()->findByAttributes([
            'id' => $attributes['event_id'],
        ]);

        if (!$event) {
            return $this->responseError("Không tìm thấy sự kiện", 404);
        }

        if (!$this->service->user()->validateApiUser($request->getUser(), $event)) {
            return $this->responseError("Thông tin không hợp lệ", 404);
        }

        $customFields = $attributes['custom_fields'] ?? [];
        $attributes['event_id'] = $event->id;
        $attributes['event_code'] = $event->code;
        $attributes['register_source'] = Client::REGISTER_API;

        if (isset($attributes['id'])) {
            $id = $attributes['id'];
            $client = $this->service->findByAttributes([
                'id'        => $id,
            ]);
        } else {
            /* get qrcode if empty */
            $attributes['qrcode'] = $event->generateQrcodeOnSetting(
                $event->code,
                $customFields['phone'] ?? null,
                $attributes['email'] ?? null,
                $attributes['name'],
                $customFields ?? []
            );
        }

        /* get landing page id */
        if ($request->slug) {
            $landingPage = $this->service->landing_page()->findByAttributes([
                'slug' => $request->slug,
            ]);

            if ($landingPage) {
                $attributes['lp_id'] = $landingPage->id;
            }
        }

        if ($client) {
            /* update */
            unset($attributes['id']);
            $attributes['qrcode'] = $client->qrcode;
            $this->service->update($client->id, $attributes);
            $client->refresh();
            $msg = "Cập nhật thành công";
        } else {
            /* create */
            $client = $this->service->create($attributes);
            /* generate img_qrcode */
            $this->service->update($client->id, [
                'img_qrcode' => $client->generateImgQrcode(),
            ]);
        }

        /* register */
        /* hidec */
        $this->service->attributes['campaign_id'] = ($event->id == 39 ? 37 : null) ?? null;
        $this->service->attributes['event_code'] = $event->code;
        $this->service->attributes['qrcode'] = $attributes['qrcode'];
        $this->service->register($event, $client);
        return $this->responseSuccess(new ClientResource($client->refresh()), $msg);
    }

    public function findByQrcode(FindByQrcodeRequest $request)
    {
        $attributes = array_filter($request->only([
            'event_id',
            'qrcode',
        ]));

        $client = $this->service->findByAttributes($attributes);

        if ($client) {
            if (!$this->service->user()->validateApiUser($request->getUser(), $client->event)) {
                return $this->responseError("Thông tin không hợp lệ", 404);
            }

            return $this->responseSuccess(new ClientResource($client), "Thông tin khách mời");
        }

        return $this->responseError("Không tìm thấy khách mời", 404);
    }

    public function findById(int $id, Request $request)
    {
        $client = $this->service->findByAttributes([
            'id' => $id
        ]);

        if ($client) {
            if (!$this->service->user()->validateApiUser($request->getUser(), $client->event)) {
                return $this->responseError("Thông tin không hợp lệ", 404);
            }

            return $this->responseSuccess(new ClientResource($client), "Thông tin khách mời");
        }

        return $this->responseError("Không tìm thấy khách mời", 404);
    }
}
