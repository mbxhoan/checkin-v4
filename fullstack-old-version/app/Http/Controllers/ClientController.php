<?php

namespace App\Http\Controllers;

use App\Http\Requests\Web\Clients\StoreRequest;
use App\Models\Client;
use App\Services\Web\ClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    public function __construct(ClientService $service)
    {
        $this->service = $service;
    }

    /**
     * Generate the img qrcode if not exist
     */
    public function generateQrcodeById($id)
    {
        $client = $this->service->findByAttributes([
            'id' => $id
        ]);

        if ($client) {
            $file = $client->img_qrcode;

            /* có hình mã qrcode */
            if ($file) {
                $filePath = "public/{$file}";

                if ($file && Storage::exists($filePath)) {
                    return response()->file(storage_path("app/{$filePath}"));
                }
            }

            /* chưa có hình mã qrcode */
            $this->service->middleware_client()->generateQrcode($client->event_code, $client->qrcode);

            $client = $this->service->findByAttributes([
                'id' => $id
            ]);

            $file = $client->img_qrcode;
            $filePath = "public/{$file}";

            if ($file && Storage::exists($filePath)) {
                return response()->file(storage_path("app/{$filePath}"));
            }
            /* chưa có nữa thì thua. Lỗi */
            /* end */
        }

        return redirect()->route('web.home')->withErrors('Không tìm thấy thông tin');
        return response()->json([
            'error' => 'Client not found.'
        ], 404);
    }

    /**
     * Show the img qrcode
     */
    public function viewQrcode($qrcode)
    {
        $client = $this->service->findByAttributes([
            'qrcode' => $qrcode
        ]);

        if ($client) {
            $file = $client->img_qrcode;
            $filePath = "public/{$file}";

            if ($file && Storage::exists($filePath)) {
                return response()->file(storage_path("app/{$filePath}"));
            }
        }

        return redirect()->route('web.home')->withErrors('Không tìm thấy thông tin');
        return response()->json([
            'error' => 'Client not found.'
        ], 404);
    }

    /**
     * Show the img qrcode
     */
    public function viewQrcodeById($id)
    {
        $client = $this->service->findByAttributes([
            'id' => $id
        ]);

        if ($client) {
            $file = $client->img_qrcode;
            $filePath = "public/{$file}";

            if ($file && Storage::exists($filePath)) {
                return response()->file(storage_path("app/{$filePath}"));
            }
        }

        return redirect()->route('web.home')->withErrors('Không tìm thấy thông tin');
        return response()->json([
            'error' => 'Client not found.'
        ], 404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request, string $slug): RedirectResponse
    {
        $client = null;
        $this->service->attributes = array_filter($request->only([
            'id',
            'qrcode',
            'event_id',
            'name',
            'email',
            'status',
            'type',
            'custom_fields',
            'lang',
            'campaign_id',
            'ref_id'
        ]));

        $event = $this->service->event()->findByAttributes([
            'id' => $this->service->attributes['event_id'],
        ]);

        if (!$event) {
            return back()->with('error', "Không tìm thấy sự kiện");
        }

        if (isset($this->service->attributes['id'])) {
            $id = $this->service->attributes['id'];
            $client = $this->service->findByAttributes([
                'id'    => $id,
            ]);
        }

        $customFields = $this->service->attributes['custom_fields'] ?? [];
        $this->service->attributes['event_code'] = $event->code;
        $this->service->attributes['register_source'] = Client::REGISTER_LP;

        if (!isset($this->service->attributes['qrcode'])) {
            $this->service->attributes['qrcode'] = $event->generateQrcodeOnSetting($event->code, $customFields['phone'] ?? null, $this->service->attributes['email'] ?? null, $this->service->attributes['name'], $customFields ?? []);
        }

        if ($client) {
            /* update */
            unset($this->service->attributes['id']);
            unset($this->service->attributes['qrcode']);
            $this->service->update($client->id, $this->service->attributes);
            $client->refresh();
        } else {
            /* create */
            $client = $this->service->create($this->service->attributes);
        }

        /* generate img_qrcode */
        $this->service->update($client->id, [
            'img_qrcode' => $client->generateImgQrcode(),
        ]);

        /* register */
        $result = $this->service->register($client);

        $landingPage = $this->service->landing_page()->findByAttributes([
            'slug' => $slug,
        ]);

        if ($landingPage) {
            return redirect()->route('landing_pages.success', [
                'slug'      => $landingPage->slug,
                'qrcode'    => $client->qrcode
            ])->with('success', $result['msg']);
        }

        return redirect()->route('web.home')->with('success', "Tạo mới thành công");
    }

    public function viewCard(int $cardId, string $clientId)
    {
        $card = $this->service->card()->findByAttributes([
            'id' => $cardId
        ]);

        if (!$card) {
            return response()->json(['error' => 'Card not found.'], 404);
        }

        $client = $this->service->findByAttributes([
            'id' => $clientId
        ]);

        if (!$client) {
            return response()->json(['error' => 'Client not found.'], 404);
        }

        /* check xem có card chưa */
        $file = $client->document_pdf;
        $filePath = "public/{$file}";

        /* comment đoạn này là cứ load trang sẽ chạy card mới */
        // if ($file && Storage::exists($filePath)) {
        //     return response()->file(storage_path("app/{$filePath}"));
        // }

        /* chưa thì tạo card */
        $result = $this->service->middleware_card()->generateCardNow($cardId, $clientId);

        if ($result['status']) {
            $client->refresh();
            $file = $client->document_pdf;
            $filePath = "public/{$file}";

            if ($file && Storage::exists($filePath)) {
                return response()->file(storage_path("app/{$filePath}"));
            } else {
                abort(404);
                return response()->json(['error' => 'Không tìm thấy file. Vui lòng thử lại sau...'], 404);
            }
        } else {
            abort(404, $result['msg']);
            return response()->json([
                'status'            => 'error',
                'status_code'       => 400,
                'message'           => $result['msg'],
            ]);
        }

        abort(404);
    }

    public function viewDocumentPdf(string $clientId)
    {
        $client = $this->service->findByAttributes([
            'id' => $clientId
        ]);

        if (!$client) {
            return response()->json(['error' => 'Client not found.'], 404);
        }

        /* check xem có card chưa */
        $file = $client->document_pdf;
        $filePath = "public/{$file}";

        /* comment đoạn này là cứ load trang sẽ chạy card mới */
        if ($file && Storage::exists($filePath)) {
            return response()->file(storage_path("app/{$filePath}"));
        }

        abort(404);
    }
}
