<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LuckyDrawBuilder\StoreLayoutRequest;
use App\Http\Requests\Admin\LuckyDrawBuilder\UpdateLayoutRequest;
use App\Models\LuckyDraw;
use App\Models\LuckyDrawLayout;
use App\Models\LuckyDrawReward;
use App\Services\Admin\LuckyDrawBuilderService;
use App\Services\Admin\LuckyDrawFieldService;
use App\Services\Admin\LuckyDrawService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LuckyDrawBuilderController extends Controller
{
    public function __construct(
        protected LuckyDrawBuilderService $builderService,
        protected LuckyDrawFieldService $fieldService,
        protected LuckyDrawService $luckyDrawService
    ) {}

    /**
     * Show builder interface
     */
    public function index(LuckyDraw $luckyDraw)
    {
        $layouts = $luckyDraw->layouts()->with('reward')->get();
        $rewards = $luckyDraw->rewards()->orderBy('order')->get();
        $fields = $this->fieldService->getAvailableFields($luckyDraw);
        $clients = $luckyDraw->clients()->whereNull('reward_id')->where('status', 'ACTIVE')->get();

        return view('admin.lucky_draws.builder', [
            'luckyDraw' => $luckyDraw,
            'layouts' => $layouts,
            'rewards' => $rewards,
            'fields' => $fields,
            'clients' => $clients,
            'event' => $luckyDraw->event,
        ]);
    }

    /**
     * Get available fields for this lucky draw
     */
    public function getFields(LuckyDraw $luckyDraw): JsonResponse
    {
        return response()->json([
            'fields' => $this->fieldService->getAvailableFields($luckyDraw),
        ]);
    }

    /**
     * Store new layout
     */
    public function storeLayout(StoreLayoutRequest $request, LuckyDraw $luckyDraw): JsonResponse
    {
        $reward = $request->reward_id
            ? LuckyDrawReward::findOrFail($request->reward_id)
            : null;

        $layout = $this->builderService->createLayout(
            $luckyDraw,
            $request->validated(),
            $reward
        );

        return response()->json([
            'message' => 'Layout created successfully',
            'layout' => $layout,
        ], 201);
    }

    /**
     * Get layout details
     */
    public function showLayout(LuckyDraw $luckyDraw, LuckyDrawLayout $layout): JsonResponse
    {
        return response()->json([
            'layout' => $layout->load('reward'),
        ]);
    }

    /**
     * Get default layout (create if not exists)
     */
    public function getDefaultLayout(LuckyDraw $luckyDraw): JsonResponse
    {
        $layout = $this->builderService->getOrCreateDefaultLayout($luckyDraw);

        return response()->json([
            'layout' => $layout,
        ]);
    }

    /**
     * Update layout
     */
    public function updateLayout(
        UpdateLayoutRequest $request,
        LuckyDraw $luckyDraw,
        LuckyDrawLayout $layout
    ): JsonResponse {
        $layout = $this->builderService->updateLayout($layout, $request->validated());

        return response()->json([
            'message' => 'Layout updated successfully',
            'layout' => $layout,
        ]);
    }

    /**
     * Delete layout
     */
    public function destroyLayout(LuckyDraw $luckyDraw, LuckyDrawLayout $layout): JsonResponse
    {
        $layout->delete();

        return response()->json([
            'message' => 'Layout deleted successfully',
        ]);
    }

    /**
     * Generate preview data
     */
    public function preview(Request $request, LuckyDraw $luckyDraw): JsonResponse
    {
        $layoutId = $request->input('layout_id');
        $clientId = $request->input('client_id');

        $layout = $layoutId
            ? LuckyDrawLayout::findOrFail($layoutId)
            : $luckyDraw->defaultLayout;

        if (!$layout) {
            return response()->json(['error' => 'No layout found'], 404);
        }

        $previewData = $this->builderService->generatePreviewData($layout, $clientId);

        return response()->json($previewData);
    }

    /**
     * Clone layout
     */
    public function cloneLayout(Request $request, LuckyDraw $luckyDraw, LuckyDrawLayout $layout): JsonResponse
    {
        $targetReward = $request->input('target_reward_id')
            ? LuckyDrawReward::findOrFail($request->input('target_reward_id'))
            : null;

        $newLayout = $this->builderService->cloneLayout($layout, $targetReward);

        return response()->json([
            'message' => 'Layout cloned successfully',
            'layout' => $newLayout,
        ], 201);
    }

    /**
     * Proxy image to avoid CORS issues
     */
    public function proxyImage(Request $request, LuckyDraw $luckyDraw)
    {
        $url = $request->input('url');
        
        if (!$url) {
            abort(400, 'URL parameter is required');
        }

        // Nếu url trỏ tới proxy khác (layout cũ lưu nhầm proxy URL), lấy ra URL ảnh gốc
        while (str_contains($url, 'proxy-image') && preg_match('#[?&]url=([^&]+)#', $url, $m)) {
            $url = urldecode($m[1]);
        }

        // Validate URL belongs to allowed domains or is relative
        $allowedDomains = [
            parse_url(config('app.url'), PHP_URL_HOST),
            'checkin.delfi.vn',
            'checkin.test.delfi.vn',
            'delfi.vn',
            '127.0.0.1',
            'localhost',
        ];
        $allowedDomains = array_filter(array_unique($allowedDomains));
        
        $urlHost = parse_url($url, PHP_URL_HOST);
        
        // Allow relative URLs or URLs from same domain
        if (!$urlHost || in_array($urlHost, $allowedDomains) || str_starts_with($url, '/')) {
            try {
                // If relative URL, convert to full URL
                if (str_starts_with($url, '/')) {
                    $url = config('app.url') . $url;
                }
                
                // Use cURL for better HTTPS support and error handling
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local development
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // For local development
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Laravel Image Proxy)');
                
                $imageContent = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                $error = curl_error($ch);
                curl_close($ch);
                
                if ($imageContent === false || $httpCode !== 200) {
                    Log::error('Proxy image failed', [
                        'url' => $url,
                        'http_code' => $httpCode,
                        'error' => $error,
                    ]);
                    abort(404, 'Image not found: ' . ($error ?: "HTTP $httpCode"));
                }
                
                // Detect content type if not provided by server
                if (!$contentType || strpos($contentType, 'image/') === false) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $contentType = finfo_buffer($finfo, $imageContent) ?: 'image/jpeg';
                    finfo_close($finfo);
                }
                
                return response($imageContent, 200)
                    ->header('Content-Type', $contentType)
                    ->header('Access-Control-Allow-Origin', '*')
                    ->header('Cache-Control', 'public, max-age=3600');
            } catch (\Exception $e) {
                Log::error('Proxy image exception', [
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);
                abort(500, 'Failed to load image: ' . $e->getMessage());
            }
        }
        
        abort(403, 'URL not allowed: ' . $urlHost);
    }

    /**
     * Show public display screen
     */
    public function display(LuckyDraw $luckyDraw)
    {
        if ($luckyDraw->type === LuckyDraw::TYPE_WHEEL) {
            return $this->displayWheel($luckyDraw);
        }

        // Load layout (default or current reward) - for RAFFLE type
        $layout = $luckyDraw->defaultLayout ?? new LuckyDrawLayout([
            'canvas_width' => 1920,
            'canvas_height' => 1080,
            'background_type' => 'color',
            'background_value' => '#000000',
            'blocks' => [],
        ]);

        // Load eligible clients for spinning
        $clients = $luckyDraw->clients()
            ->whereNull('reward_id')
            ->where('status', 'ACTIVE')
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'qrcode' => $client->qrcode,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'custom_fields' => $client->custom_fields,
                ];
            });

        return view('admin.lucky_draws.display', [
            'luckyDraw' => $luckyDraw,
            'layout' => $layout,
            'clients' => $clients,
        ]);
    }

    /**
     * Display Wheel of Names style (no prizes, no rewards)
     */
    protected function displayWheel(LuckyDraw $luckyDraw)
    {
        $initialEntries = $luckyDraw->clients()
            ->where('status', 'ACTIVE')
            ->get()
            ->pluck('name')
            ->filter()
            ->values()
            ->toArray();

        $backgroundDesktopUrl = $luckyDraw->bgDesktopUrl?->getUrl();
        $backgroundMobileUrl = $luckyDraw->bgMobileUrl?->getUrl();

        return view('admin.lucky_draws.display-wheel', [
            'luckyDraw' => $luckyDraw,
            'initialEntries' => $initialEntries,
            'backgroundDesktopUrl' => $backgroundDesktopUrl,
            'backgroundMobileUrl' => $backgroundMobileUrl,
        ]);
    }

    /**
     * Update background image for wheel display (upload).
     */
    public function updateBackground(Request $request, LuckyDraw $luckyDraw): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'string', Rule::in(['desktop', 'mobile'])],
            'image' => ['required', 'file', 'image', 'max:5120'],
        ]);

        $type = $request->input('type');
        $key = $type === 'mobile' ? 'background_url_mobile' : 'background_url_desktop';

        $this->luckyDrawService->attributes['image'] = $request->file('image');
        $this->luckyDrawService->attributes['name'] = $request->file('image')->getClientOriginalName();

        $result = $this->luckyDrawService->mediaLibraryService()->store();
        if (empty($result['media'])) {
            return response()->json(['message' => $result['msg'] ?? 'Upload failed'], 422);
        }

        $this->luckyDrawService->update($luckyDraw->id, [$key => $result['media']->id]);

        return response()->json([
            'url' => $result['media']->getUrl(),
            'type' => $type,
        ]);
    }
}
