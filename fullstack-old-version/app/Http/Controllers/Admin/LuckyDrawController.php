<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DataTables\Admin\LuckyDrawClientDataTable;
use App\DataTables\Admin\LuckyDrawDataTable;
use App\Services\Admin\LuckyDrawService;
use App\Exports\LuckyDraw\Raffle\ResultExport;
use App\Exports\LuckyDraw\Raffle\WinnersExport;
use App\Http\Requests\Admin\LuckyDraws\ListRequest;
use App\Http\Requests\Admin\LuckyDraws\LuckyDrawRequest;
use App\Http\Requests\Admin\SelectEventToCreateRequest;
use App\Models\Client;
use App\Models\Event;
use App\Models\LuckyDraw;
use Maatwebsite\Excel\Facades\Excel;

class LuckyDrawController extends Controller
{
    public function __construct(LuckyDrawService $service)
    {
        $this->service = $service;
    }

    // public function selectEventToCreate(SelectEventToCreateRequest $request)
    // {
    //     return redirect()->route('admin.lucky_draws.create', [
    //         'event' => $request->event_id
    //     ]);
    // }

    public function index(ListRequest $request)
    {
        $dataTable = new LuckyDrawDataTable();
        $total = $dataTable->getFilter();
        $events = $this->service->event()->getEventList();

        return $dataTable->render('admin.lucky_draws.index', [
            'total'             => $total->count(),
            'eventArray'        => $events->mapWithKeys(function ($event) {
                return [
                    $event->id  => "{$event->code} - {$event->name}"
                ];
            })->toArray(),
        ]);
    }

    public function create()
    {
        // $this->authorize('create_lucky_draw', $event);
        $events = $this->service->event()->getEventList();
        $event = $events->first();

        $clients = $this->service->client()->getListByAttributes([
            'event_id' => $event->id
        ]);

        $totalCheckedIn = $this->service->middleware_client()->getClientCheckedIn($event->code);
        $groups = [
            "all"       => "- Tất cả ({$clients->count()}) -",
            "checked"   => "Đã checkin ({$totalCheckedIn->count()})"
        ];

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

        return view('admin.lucky_draws.create', [
            'model'             => $this->service->init(),
            'event'             => $event,
            'types'             => $types,
            'groups'            => $groups,
            'eventArray'        => $events->mapWithKeys(function ($event) {
                return [
                    $event->id  => "{$event->code} - {$event->name}"
                ];
            })->toArray(),
        ]);
    }

    public function edit(LuckyDraw $lucky_draw)
    {
        $this->authorize('edit', $lucky_draw);

        $clients = $this->service->client()->getListByAttributes([
            'event_id' => $lucky_draw->event->id
        ]);
        $totalCheckedIn = $this->service->middleware_client()->getClientCheckedIn($lucky_draw->event->code);
        $groups = [
            "all"       => "- Tất cả ({$clients->count()}) -",
            "checked"   => "Đã checkin ({$totalCheckedIn->count()})"
        ];

        $types = $this->service->client()->getListDistinctField([
            'event_id' => $lucky_draw->event->id,
        ]);

        $types = $this->service
            ->removeEmptyElementInArray($types->pluck('type', 'type')
            ->toArray());

        foreach ($types as $key => $type) {
            $count = $this->service->client()->getListByAttributes([
                'event_id' => $lucky_draw->event->id,
                'status'   => Client::STATUS_ACTIVE,
                'type'     => $key,
            ], [
                'email'    => null,
            ])->count();

            $types[$key] = "{$type} ({$count})";
        }

        $luckyDrawRewards = $this->service->luckyDrawReward()->getListByAttributes([
            'lucky_draw_id' => $lucky_draw->id
        ], [], [], 0, [
            "order"         => "ASC"
        ]);

        $luckyDrawClients = $this->service->luckyDrawClient()->getListByAttributes([
            'lucky_draw_id' => $lucky_draw->id
        ], [], [], 0, [
            "reward_id"     => "DESC"
        ]);

        $luckyDrawClientShuffle = (clone $luckyDrawClients)->shuffle();
        $luckyDrawClientsNoRewards = (clone $luckyDrawClients)->whereNull('reward_id');

        $assignees = ["" => " - "] + $luckyDrawClientsNoRewards->map(function ($client) {
            return [
                'id'        => $client->id,
                'name'      => "{$client->qrcode} - {$client->name}",
            ];
        })->pluck('name', 'id')->toArray();

        $dataTable = new LuckyDrawClientDataTable($lucky_draw);

        // Choose view based on lucky draw type
        $viewName = $lucky_draw->type === LuckyDraw::TYPE_WHEEL 
            ? 'admin.lucky_draws.detail-wheel' 
            : 'admin.lucky_draws.detail';

        return $dataTable->render($viewName, [
            'luckyDraws'                => $this->service->getListByAttributes([
                'event_id'              => $lucky_draw->event->id
            ], [], [], 0, []),
            'model'                     => $lucky_draw,
            'event'                     => $lucky_draw->event,
            'types'                     => $types,
            'groups'                    => $groups,
            'luckyDrawRewards'          => $luckyDrawRewards,
            'luckyDrawClients'          => $luckyDrawClients,
            'luckyDrawClientShuffle'    => $luckyDrawClientShuffle,
            'assignees'                 => $assignees,
        ]);
    }

    public function store(LuckyDrawRequest $request)
    {
        $attributes = $request->only([
            'event_id',
            'name',
            'type',
        ]);

        $attributes['type'] = $attributes['type'] ?? LuckyDraw::TYPE_RAFFLE;
        $attributes['created_by'] = auth()->user()->id;
        $attributes['updated_by'] = auth()->user()->id;
        $attributes['status'] = LuckyDraw::STATUS_ACTIVE;
        $luckyDraw = $this->service->create($attributes);
        $medias = $request->only(array_keys($luckyDraw->getMediaFields()));

        if (count($medias)) {
            foreach ($medias as $key => $media) {
                if ($media) {
                    $this->service->attributes['image'] = $media;
                    $this->service->attributes['name'] = $media->getClientOriginalName();

                    if ($result = $this->service->mediaLibraryService()->store()) {
                        if (!empty($result['media'])) {
                            $this->service->update($luckyDraw->id, [
                                $key => $result['media']->id
                            ]);
                        } else {
                            return redirect()->route('admin.landing_pages.edit', [
                                'event'         => $luckyDraw->event,
                                'landing_page'  => $luckyDraw,
                            ])->withErrors($result['msg']);
                        }
                    }
                }
            }
        }

        return redirect()->route('admin.lucky_draws.edit', $luckyDraw)
            ->withSuccess("Tạo mới thành công");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LuckyDrawRequest $request, LuckyDraw $lucky_draw)
    {
        $attributes = $request->only([
            'event_id',
            'name',
            'type',
        ]);

        $medias = $request->only(array_keys($lucky_draw->getMediaFields()));

        if (count($medias)) {
            foreach ($medias as $key => $media) {
                if ($media) {
                    $this->service->attributes['image'] = $media;
                    $this->service->attributes['name'] = $media->getClientOriginalName();

                    if ($result = $this->service->mediaLibraryService()->store()) {
                        if (!empty($result['media'])) {
                            $this->service->update($lucky_draw->id, [
                                $key => $result['media']->id
                            ]);
                        } else {
                            return redirect()->route('admin.landing_pages.edit', [
                                'event'         => $lucky_draw->event,
                                'landing_page'  => $lucky_draw,
                            ])->withErrors($result['msg']);
                        }
                    }
                }
            }
        }

        $attributes['updated_by'] = auth()->user()->id;
        $attributes['status'] = LuckyDraw::STATUS_ACTIVE;
        $this->service->update($lucky_draw->id, $attributes);
        return redirect()->route('admin.lucky_draws.edit', $lucky_draw)->withSuccess("Cập nhật thành công");
    }

    public function viewRaffle(LuckyDraw $lucky_draw)
    {
        $luckyDrawRewards = $this->service->luckyDrawReward()->repo->getItemsByLuckyDrawId($lucky_draw->id, null, "order", "DESC", false);
        $luckyDrawClients = $this->service->luckyDrawClient()->repo->getItemsByLuckyDrawId($lucky_draw->id, null, false, false, true);
        $luckyDrawWinners = $this->service->luckyDrawClient()->repo->getItemsByLuckyDrawId($lucky_draw->id, null, false, true);
        $luckyDrawClients = $luckyDrawClients->map(function ($client) {
            return [
                'id'            => $client->id,
                'name'          => $client->name,
                'qrcode'        => $client->qrcode,
                'uid'           => $client->custom_fields['uid'] ?? null,
                'company'       => $client->custom_fields['company'] ?? null,
                'daily'         => $client->custom_fields['daily'] ?? null,
                'phone'         => substr($client->phone, -4),
            ];
        });

        $eventCode = $lucky_draw->event->code;
        $viewPath = "backend.lucky-draw.raffle.customs.{$eventCode}";

        if (view()->exists($viewPath)) {
            return view($viewPath, [
                'luckyDraw'         => $lucky_draw,
                'luckyDrawRewards'  => $luckyDrawRewards,
                'luckyDrawClients'  => $luckyDrawClients,
                'luckyDrawWinners'  => $luckyDrawWinners
            ]);
        } else {
            abort(404, "View for event code {$eventCode} not found.");
        }

        abort(404);
    }

    public function updateRaffle(Request $request)
    {
        $this->service->attributes = $request->all();
        $this->service->attributes['assignee_id'] = $request->input('assignee_id');

        if ($this->service->updateRaffle()) {
            return $this->responseSuccess();
        }

        return $this->responseError([
            'message' => 'Lỗi, vui lòng kiểm tra lại'
        ], 400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LuckyDraw $lucky_draw)
    {
        $this->authorize('edit', $lucky_draw);
        
        $lucky_draw->status = LuckyDraw::STATUS_DELETED;
        $lucky_draw->save();

        return redirect()->route('admin.lucky_draws.index')
            ->withSuccess("Xóa vòng quay thành công");
    }

    // Hàm export lucky draw
    public function exportExcelRaffleResult(LuckyDraw $lucky_draw)
    {
        $fileName = "ketquaxoso_{$lucky_draw->id}_".date('YmdHis').".xlsx";
        return Excel::download(new ResultExport($lucky_draw->id), $fileName);
    }

    /**
     * Reset danh sách người trúng thưởng (bỏ gán giải cho tất cả, giữ nguyên danh sách tham dự).
     */
    public function resetWinners(LuckyDraw $lucky_draw)
    {
        $this->authorize('edit', $lucky_draw);

        $this->service->luckyDrawReward()->resetAssigness($lucky_draw);

        return redirect()->route('admin.lucky_draws.edit', $lucky_draw)
            ->withSuccess('Đã reset danh sách người trúng thưởng.');
    }

    /**
     * Export danh sách người trúng thưởng (chỉ những người có giải).
     */
    public function exportWinners(LuckyDraw $lucky_draw)
    {
        $this->authorize('edit', $lucky_draw);

        $fileName = "nguoi_trung_thuong_{$lucky_draw->id}_".date('YmdHis').".xlsx";
        return Excel::download(new WinnersExport($lucky_draw->id), $fileName);
    }

    /**
     * Upload ảnh để lấy URL dùng cho cột img_link trong file Excel danh sách giải thưởng.
     * Nếu truyền lucky_draw_id thì URL sẽ được lưu vào danh sách ảnh của mẫu quay số (chọn khi thêm/sửa giải).
     */
    public function uploadImageLink(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:png,jpg,jpeg|max:5120',
            'lucky_draw_id' => 'nullable|integer|exists:lucky_draws,id',
        ]);

        $this->service->attributes['image'] = $request->file('image');
        $this->service->attributes['name'] = $request->file('image')->getClientOriginalName();

        $result = $this->service->mediaLibraryService()->store();

        if (empty($result['media'])) {
            return response()->json(['success' => false, 'message' => $result['msg'] ?? 'Lỗi upload'], 422);
        }

        $media = $result['media'];
        $url = $media->getUrl();
        $name = $media->name ?? $request->file('image')->getClientOriginalName();

        $uploadedRewardImages = null;
        $luckyDrawId = $request->input('lucky_draw_id');
        if ($luckyDrawId) {
            $luckyDraw = LuckyDraw::find($luckyDrawId);
            if ($luckyDraw) {
                $list = $luckyDraw->uploaded_reward_images ?? [];
                $list[] = ['url' => $url, 'name' => $name];
                $luckyDraw->update(['uploaded_reward_images' => $list]);
                $uploadedRewardImages = $luckyDraw->fresh()->uploaded_reward_images;
            }
        }

        return response()->json([
            'success' => true,
            'url' => $url,
            'name' => $name,
            'uploaded_reward_images' => $uploadedRewardImages,
        ]);
    }
}
