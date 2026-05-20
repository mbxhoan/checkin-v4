<?php
namespace App\Http\Controllers\Admin;

use App\Services\Admin\LuckyDrawClientService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LuckyDrawClients\SyncRequest;
use App\Models\LuckyDraw;
use App\Models\LuckyDrawClient;

class LuckyDrawClientController extends Controller
{
    public function __construct(LuckyDrawClientService $service)
    {
        $this->service = $service;
    }

    public function sync(LuckyDraw $lucky_draw, SyncRequest $request)
    {
        $filters = $request->all();
        $result = $this->service->sync($lucky_draw, $filters);

        if ($result['success']) {
            return redirect()->route('admin.lucky_draws.edit', $lucky_draw)
                ->withSuccess($result['msg']);
        }

        return back()->withSuccess($result['msg']);
    }

    public function reset(Request $request)
    {
        if ($request->ajax()) {
            $luckyDraw = LuckyDraw::find($request->lucky_draw_id);
            if (!$luckyDraw) {
                toastr()->error("Không tìm thấy vòng quay");
                return $this->responseError();
            }
            if ($this->service->reset($luckyDraw)) {
                toastr()->success("Reset thành công!");
                return $this->responseSuccess();
            }

            toastr()->error("Đã có lỗi xảy ra");
            return $this->responseError();
        }
    }

    public function resetLuckyDraw(LuckyDraw $lucky_draw)
    {
        $this->authorize('edit', $lucky_draw);

        if ($this->service->reset($lucky_draw)) {
            return redirect()->route('admin.lucky_draws.edit', $lucky_draw)
                ->withSuccess('Đã xóa toàn bộ danh sách khách tham dự');
        }

        return redirect()->route('admin.lucky_draws.edit', $lucky_draw)
            ->withErrors('Không thể reset danh sách');
    }

    public function destroy(LuckyDrawClient $lucky_draw_client)
    {
        // $this->service->delete($lucky_draw_client->id);
        $this->service->update($lucky_draw_client->id, [
            'status' => LuckyDrawClient::STATUS_DELETED
        ]);
        return redirect()->route("admin.lucky_draws.edit", $lucky_draw_client->lucky_draw)
            ->withSuccess('Khách hàng đã được xóa');
    }

    /**
     * Bỏ gán giải khỏi một người trúng (chỉ xóa khỏi danh sách trúng thưởng, không xóa khỏi danh sách tham dự).
     */
    public function removeReward(LuckyDrawClient $lucky_draw_client)
    {
        $this->authorize('edit', $lucky_draw_client->lucky_draw);

        $this->service->resetReward($lucky_draw_client);

        return redirect()->route('admin.lucky_draws.edit', $lucky_draw_client->lucky_draw)
            ->withSuccess('Đã bỏ giải khỏi người này.');
    }
}
