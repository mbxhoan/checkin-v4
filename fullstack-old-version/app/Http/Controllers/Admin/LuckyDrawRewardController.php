<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Services\Admin\LuckyDrawRewardService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LuckyDrawRewards\LuckyDrawRewardRequest;
use App\Models\LuckyDrawReward;
use App\Models\LuckyDraw;
use App\Exports\LuckyDraw\RewardTemplateExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class LuckyDrawRewardController extends Controller
{
    public function __construct(LuckyDrawRewardService $service)
    {
        $this->service = $service;
    }

    public function store(LuckyDraw $lucky_draw, LuckyDrawRewardRequest $request)
    {
        $attributes = $request->only('reward')['reward'];
        $attributes['lucky_draw_id'] = $lucky_draw->id;
        $attributes['status'] = LuckyDrawReward::STATUS_ACTIVE;
        $attributes['order_name'] = $attributes['order_name'] ?? $attributes['name'] ?? '';
        $attributes['time'] = $attributes['time'] ?? 10;
        $attributes['probability'] = $attributes['probability'] ?? null;
        $this->service->create($attributes);
        return redirect()->to(route('admin.lucky_draws.edit', $lucky_draw) . '#rewards')
                ->withSuccess("Đã thêm mới giải thưởng");
    }

    public function update(LuckyDrawReward $lucky_draw_reward, LuckyDrawRewardRequest $request)
    {
        $attributes = $request->only('reward')['reward'];
        unset($attributes['code']);
        $attributes['order_name'] = $attributes['order_name'] ?? $attributes['name'] ?? $lucky_draw_reward->order_name;
        $attributes['time'] = $attributes['time'] ?? $lucky_draw_reward->time ?? 10;
        $attributes['probability'] = $attributes['probability'] ?? $lucky_draw_reward->probability;
        $this->service->update($lucky_draw_reward->id, $attributes);
        return redirect()->to(route('admin.lucky_draws.edit', $lucky_draw_reward->lucky_draw) . '#rewards')
                ->withSuccess("Đã cập nhật giải thưởng");
    }

    public function upload(LuckyDraw $lucky_draw, Request $request)
    {
        $request->validate([
            'file'          => "required|file|max:".config('app.upload_data_size_max')."|mimes:".config('app.upload_data_allow_types'),
        ]);

        try {
            $file = $request->file('file');
            $storePath = storage_path("app/import-lucky-draw-rewards/{$lucky_draw->id}");
            Storage::deleteDirectory("import-lucky-draw-rewards/{$lucky_draw->id}");
            $fileName = date('Ymd-his').'.'.$file->extension();
            $file->move($storePath, $fileName);
            if ($this->service->upload($lucky_draw, "{$storePath}/{$fileName}")) {
                return redirect()->to(route('admin.lucky_draws.edit', $lucky_draw) . '#rewards')
                    ->withSuccess("Đã nạp file giải thưởng thành công");
            }

        } catch (\Exception $e) {
            Log::alert($e);

            if (auth()->user()->isSysAdmin()) {
                $msgError = $e->getMessage();
            }
        }

        return back()->withErrors($msgError ?? "Không thể nạp file");
    }

    public function downloadTemplate(LuckyDraw $lucky_draw)
    {
        $this->authorize('edit', $lucky_draw);
        $fileName = "mau_danh_sach_giai_thuong_{$lucky_draw->id}_" . date('Ymd') . ".xlsx";
        return Excel::download(new RewardTemplateExport(), $fileName);
    }

    public function destroy(LuckyDrawReward $lucky_draw_reward, Request $request)
    {
        /* validate confirm */
        $request->validate([
            'confirm' => ['required', 'string', 'max:20', 'in:DELETE'],
        ]);

        if ($this->service->destroy($lucky_draw_reward)) {
            return redirect()->to(route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw) . '#rewards')
                ->withSuccess('Giải thưởng đã được xóa');
        }

        return redirect()->to(route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw) . '#rewards')
                ->withErrors('Đã có lỗi xảy ra');
    }

    public function destroyAll(LuckyDrawReward $lucky_draw_reward, Request $request)
    {
        /* validate confirm */
        $request->validate([
            'confirm' => ['required', 'string', 'max:20', 'in:RESET'],
        ]);

        try {
            $this->service->destroyAll($lucky_draw_reward);
            return redirect()->to(route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw) . '#rewards')
                ->withSuccess('Tất cả giải thưởng đã được xóa');
        } catch (\Exception $e) {
            if (auth()->user()->isSysAdmin()) {
                return redirect()->to(route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw) . '#rewards')
                    ->withErrors($e->getMessage());
            }
        }

        return redirect()->to(route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw) . '#rewards')
                    ->withErrors("Reset giải thưởng KHÔNG thành công");
    }

    public function resetAssigness(LuckyDrawReward $lucky_draw_reward, Request $request)
    {
        /* validate confirm */
        $request->validate([
            'confirm' => ['required', 'string', 'max:20', 'in:RESET'],
        ]);

        try {
            $this->service->resetAssigness($lucky_draw_reward);
            return redirect()->to(route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw) . '#rewards')
                ->withSuccess('Làm mới thông tin nhận giải thành công');
        } catch (\Exception $e) {
            if (auth()->user()->isSysAdmin()) {
                return redirect()->to(route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw) . '#rewards')
                    ->withErrors($e->getMessage());
            }
        }

        return redirect()->to(route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw) . '#rewards')
                    ->withErrors("Làm mới thông tin nhận giải KHÔNG thành công");
    }

    /**
     * Reset assignees (làm mới) - bỏ gán người trúng giải, giữ nguyên danh sách giải.
     */
    public function resetAssignees(LuckyDraw $lucky_draw, Request $request)
    {
        $this->authorize('edit', $lucky_draw);

        try {
            $this->service->resetAssigness($lucky_draw);
            return redirect()->to(route("admin.lucky_draws.edit", $lucky_draw) . '#rewards')
                ->withSuccess('Làm mới thông tin nhận giải thành công');
        } catch (\Exception $e) {
            return redirect()->to(route("admin.lucky_draws.edit", $lucky_draw) . '#rewards')
                ->withErrors('Làm mới thông tin nhận giải KHÔNG thành công');
        }
    }

    /**
     * Destroy all rewards (reset) - xóa tất cả giải thưởng và bỏ gán người trúng.
     */
    public function destroyAllRewards(LuckyDraw $lucky_draw, Request $request)
    {
        $this->authorize('edit', $lucky_draw);

        try {
            $this->service->destroyAll($lucky_draw);
            return redirect()->to(route("admin.lucky_draws.edit", $lucky_draw) . '#rewards')
                ->withSuccess('Tất cả giải thưởng đã được xóa');
        } catch (\Exception $e) {
            return redirect()->to(route("admin.lucky_draws.edit", $lucky_draw) . '#rewards')
                ->withErrors('Reset giải thưởng KHÔNG thành công');
        }
    }

    public function cancelReward(LuckyDrawReward $lucky_draw_reward)
    {
        $cancel = $this->service->cancelReward($lucky_draw_reward);

        if ($cancel) {
            return redirect()->to(route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw) . '#rewards')
                ->withSuccess('Đã huỷ giải thành công');
        }

        return redirect()->to(route("admin.lucky_draws.edit", $lucky_draw_reward->lucky_draw) . '#rewards')
                    ->withErrors("Huỷ giải KHÔNG thành công");
    }
}
