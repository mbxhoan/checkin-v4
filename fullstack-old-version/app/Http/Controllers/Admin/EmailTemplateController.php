<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EmailTemplates\ListRequest;
use App\Http\Requests\Admin\EmailTemplates\SendTestPostmarkRequest;
use App\Http\Requests\Admin\EmailTemplates\StoreRequest;
use App\Http\Requests\Admin\EmailTemplates\TemplatePostmarkRequest;
use App\Models\CustomFieldTemplate;
use App\Services\Admin\EmailTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmailTemplateController extends Controller
{
    public function __construct(EmailTemplateService $service)
    {
        $this->service = $service;
    }

    /**
     * Show the application products index.
     */
    public function index(ListRequest $request)
    {
        $result = $this->service->getPostmarkTemplates(false, false, true);

        return view('admin.email_templates.index', [
            'templates' => $result['Templates'] ?? [],
            'total' => $result['TotalCount'] ?? 0,
            'forceSync' => false,
        ]);
    }

    public function editPostmarkTemplate(int $templateId)
    {
        $result = $this->service->middleware_email_template()->getPostmarkTemplate($templateId);

        if (count($result)) {
            $toolbarData = $this->getTemplateFieldToolbarData();

            return view('admin.email_templates.detail', [
                'object' => $result,
                'templateEvents' => $toolbarData['templateEvents'],
                'templateFieldsByEvent' => $toolbarData['templateFieldsByEvent'],
                'templateDefaultEventId' => $toolbarData['templateDefaultEventId'],
            ]);
        }

        abort(404);
    }

    public function create()
    {
        $toolbarData = $this->getTemplateFieldToolbarData();

        return view('admin.email_templates.create', [
            'templateEvents' => $toolbarData['templateEvents'],
            'templateFieldsByEvent' => $toolbarData['templateFieldsByEvent'],
            'templateDefaultEventId' => $toolbarData['templateDefaultEventId'],
        ]);
    }

    public function store(StoreRequest $request)
    {
        $result = $this->service->middleware_email_template()->storePostmarkTemplate([
            'Name' => $request->name,
            'Subject' => $request->subject,
            'TextBody' => Helper::convertHtmlToPlainText($request->html_body),
            'HtmlBody' => $request->html_body,
            'Alias' => Str::slug($request->name),
            'TemplateType' => 'Standard',
            'LayoutTemplate' => 'no-delfi',
        ]);

        if (count($result)) {
            if (! empty($result['TemplateId'])) {
                $this->service->middleware_email_template()->getPostmarkTemplates(true);

                return redirect()->route('admin.email_templates.edit-postmark-template', ['templateId' => $result['TemplateId']])
                    ->withSuccess('Tạo mới thành công');
            }

            return back()->withErrors($result['error'] ?? 'Tạo mới KHÔNG thành công');
        }

        return back()->withErrors('Tạo mới KHÔNG thành công');
    }

    public function syncPostmarkTemplate(int $templateId)
    {
        $result = $this->service->middleware_email_template()->getPostmarkTemplate($templateId, true);

        if (count($result)) {
            $toolbarData = $this->getTemplateFieldToolbarData();

            return view('admin.email_templates.detail', [
                'object' => $result,
                'templateEvents' => $toolbarData['templateEvents'],
                'templateFieldsByEvent' => $toolbarData['templateFieldsByEvent'],
                'templateDefaultEventId' => $toolbarData['templateDefaultEventId'],
            ]);
        }

        abort(404);
    }

    public function viewPostmarkTemplate(int $templateId)
    {
        $result = $this->service->middleware_email_template()->getPostmarkTemplate($templateId, false, true);

        return isset($result['FullHtmlBody']) ? response($result['FullHtmlBody']) : abort(404);

        return view('admin.email_templates.index', [
            'templates' => $result['Templates'],
            'total' => $result['TotalCount'],
        ]);
    }

    public function updatePostmarkTemplate(TemplatePostmarkRequest $request, int $templateId)
    {
        $attributes = $request->all();
        $result = $this->service->middleware_email_template()->updatePostmarkTemplate($templateId, $attributes);

        if (count($result) && isset($result['Name'])) {
            return back()->withSuccess('Cập nhật thành công');
        }

        return back()->withErrors('Cập nhật KHÔNG thành công');
    }

    public function sendTestPostmarkTemplate(SendTestPostmarkRequest $request, int $templateId)
    {
        $attributes = $request->all();
        $result = $this->service->middleware_email_template()->sendTestPostmarkTemplate($templateId, $attributes);

        if (count($result) && (isset($result['Message']) && $result['Message'] == 'OK')) {
            return back()->withSuccess('Đã gửi thành công');
        }

        return back()->withErrors('Đã có lỗi xảy ra');
    }

    public function clonePostmarkTemplate(Request $request)
    {
        /* validate confirm */
        $request->validate([
            'confirm' => ['required', 'string', 'max:20', 'in:COPY'],
        ]);

        $templateId = $request->template_id;
        $name = $request->name;
        $oldTemplate = $this->service->middleware_email_template()->getPostmarkTemplate((int) $templateId);

        if (count($oldTemplate)) {
            $result = $this->service->middleware_email_template()->storePostmarkTemplate([
                'Name' => $name,
                'Subject' => $oldTemplate['Subject'],
                'TextBody' => Helper::convertHtmlToPlainText($oldTemplate['HtmlBody']),
                'HtmlBody' => $oldTemplate['HtmlBody'],
                'TemplateType' => $oldTemplate['TemplateType'],
                'LayoutTemplate' => $oldTemplate['LayoutTemplate'],
                'Alias' => Str::slug($name).'-'.Helper::randomCode(3, true),
            ]);

            if (count($result)) {
                if (! empty($result['TemplateId'])) {
                    $this->service->middleware_email_template()->getPostmarkTemplates(true);

                    return redirect()->route('admin.email_templates.edit-postmark-template', ['templateId' => $result['TemplateId']])
                        ->withSuccess('Nhân bản thành công');
                }
            }

            return back()->withErrors($result['error'] ?? 'Tạo mới KHÔNG thành công');
        }

        return back()->withErrors('Đã có lỗi xảy ra');
    }

    public function reSyncPostmarkTemplates(ListRequest $request)
    {
        $result = $this->service->middleware_email_template()->getPostmarkTemplates(true, false, true);

        return view('admin.email_templates.index', [
            'templates' => $result['Templates'],
            'total' => $result['TotalCount'],
            'forceSync' => true,
        ]);
    }

    /**
     * Sync a single template in background (AJAX).
     * This returns a light JSON payload to avoid transferring full HtmlBody for all templates.
     */
    public function syncPostmarkTemplateAsync(Request $request, int $templateId)
    {
        $force = $request->boolean('force');
        $template = $this->service->middleware_email_template()->getPostmarkTemplate($templateId, $force, true);

        if (! count($template)) {
            return $this->responseError('Template not found', 404);
        }

        return $this->responseSuccess([
            'TemplateId' => $templateId,
            'Name' => $template['Name'] ?? null,
        ], 'Synced');
    }

    public function get($templateId)
    {
        $template = $this->service->middleware_email_template()->getPostmarkTemplate((int) $templateId, false, true);

        return view('admin.email_templates._view', compact('template'));
        abort(404);
    }

    public function updatePaidAccess(Request $request, int $templateId)
    {
        if (! auth()->user()->isSysAdmin()) {
            return back()->withErrors(__('auth.not_authorized'));
        }

        $validated = $request->validate([
            'is_paid_locked' => ['nullable', 'in:0,1'],
            'event_name' => ['nullable', 'string', 'max:255'],
            'event_time' => ['nullable', 'string', 'max:255'],
            'credit' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
        ]);

        $this->service->middleware_email_template()->savePaidTemplateAccess($templateId, [
            'is_paid_locked' => (string) ($validated['is_paid_locked'] ?? '0') === '1',
            'event_name' => $validated['event_name'] ?? '',
            'event_time' => $validated['event_time'] ?? '',
            'credit' => $validated['credit'] ?? '',
            'name' => $validated['name'] ?? '',
            'subject' => $validated['subject'] ?? '',
        ]);

        return back()->withSuccess('Đã cập nhật cấu hình khoá template.');
    }

    public function requestUnlock(Request $request, int $templateId)
    {
        $result = $this->service->middleware_email_template()->requestUnlockPaidTemplate($templateId);
        if (! ($result['ok'] ?? false)) {
            $message = (string) ($result['message'] ?? 'Không thể đăng ký mở khoá vào lúc này.');

            if ($request->expectsJson() || $request->ajax()) {
                return $this->responseError($message, 422);
            }

            return back()->withErrors($message);
        }

        $notifyEmail = trim((string) ($result['notify_email'] ?? optional($request->user())->email ?? ''));
        $message = "Bạn đã đăng ký thành công, hệ thống sẽ xử lý và mở nội dung mail này cho bạn sử dụng, chúng tôi sẽ thông báo qua email {$notifyEmail} khi nội dung mail được mở khoá.";

        if ($request->expectsJson() || $request->ajax()) {
            return $this->responseSuccess([
                'template_id' => $templateId,
                'notify_email' => $notifyEmail,
            ], $message);
        }

        return back()->withSuccess($message);
    }

    protected function getTemplateFieldToolbarData(): array
    {
        $user = auth()->user();
        $events = collect();
        $sharedTemplateVars = [
            'img_qrcode' => 'Ảnh Qrcode',
            'document_pdf' => 'Ảnh Thiệp/Thiệp',
        ];

        if ($user->isSysAdmin()) {
            $events = \App\Models\Event::query()
                ->orderBy('name')
                ->get();
        } else {
            if ($user && $user->company) {
                $events = $user->event_id
                    ? $user->company->events->where('id', $user->event_id)->values()
                    : $user->company->events->values();
            }
        }

        $fieldsByEvent = [];
        $defaultTemplates = app(CustomFieldTemplate::class)->getDefaultCustomFieldTemplate();

        if ($events->count()) {
            $events->loadMissing('custom_field_templates');

            foreach ($events as $event) {
                $items = [];
                $templates = $event->custom_field_templates
                    ->sortBy('order')
                    ->filter(function ($template) {
                        return ! isset($template->status) || $template->status !== CustomFieldTemplate::STATUS_DELETED;
                    });

                foreach ($templates as $template) {
                    $items[$template->name] = [
                        'name' => $template->name,
                        'label' => $template->description ?: $template->name,
                    ];
                }

                // Fallback for events missing custom field rows.
                if (! count($items)) {
                    foreach ($defaultTemplates as $name => $templateAttr) {
                        $items[$name] = [
                            'name' => $name,
                            'label' => $templateAttr['desc'] ?? $name,
                        ];
                    }
                }

                // Always provide shared template variables for Postmark content.
                foreach ($sharedTemplateVars as $name => $label) {
                    $items[$name] = [
                        'name' => $name,
                        'label' => $label,
                    ];
                }

                $fieldsByEvent[$event->id] = array_values($items);
            }
        }

        return [
            'templateEvents' => $events->map(fn ($event) => [
                'id' => $event->id,
                'name' => $event->name ?: $event->code,
            ])->values()->all(),
            'templateFieldsByEvent' => $fieldsByEvent,
            'templateDefaultEventId' => $events->first()->id ?? null,
        ];
    }
}
