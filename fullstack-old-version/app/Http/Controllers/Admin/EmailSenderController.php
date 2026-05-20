<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EmailSendersRequest;
use App\Models\Campaign;
use App\Models\Email;
use App\Services\Admin\EmailSenderService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class EmailSenderController extends Controller
{
    public function __construct(EmailSenderService $service)
    {
        $this->service = $service;
    }

    /**
     * Show the application products index.
     */
    public function index()
    {
        $result = $this->service->getPostmarkSenders();

        return view('admin.email_senders.index', [
            'senders' => $result['SenderSignatures'] ?? [],
            'total' => $result['TotalCount'] ?? 0,
        ]);
    }

    public function syncOptions(Request $request)
    {
        $result = $this->service->getPostmarkSenders(true);
        $senders = collect($result['SenderSignatures'] ?? [])->map(function ($sender) {
            $email = trim((string) ($sender['EmailAddress'] ?? ''));
            $name = trim((string) ($sender['Name'] ?? ''));

            return [
                'email' => $email,
                'name' => $name,
                'label' => trim("{$email} - {$name}", ' -'),
            ];
        })->filter(fn ($sender) => ! empty($sender['email']))->values();

        return $this->responseSuccess([
            'senders' => $senders,
            'total' => $senders->count(),
            'selected' => (string) $request->input('from_email', ''),
        ], __('campaigns.sync.synced_senders', ['count' => $senders->count()]));
    }

    public function create()
    {
        return view('admin.email_senders.create');
    }

    public function edit(int $senderId)
    {
        $sender = (object) $this->service->getPostmarkSender($senderId);

        return view('admin.email_senders.detail', [
            'sender' => $sender,
        ]);
    }

    public function store(Request $request)
    {
        $replyTo = trim((string) $request->input('reply_to'));
        $personalNote = trim((string) $request->input('personal_note'));

        // Backward-compatible input names (old: form_email/form_name, new: from_email/from_name).
        $request->merge([
            'from_email' => trim((string) ($request->input('from_email') ?: $request->input('form_email'))),
            'from_name' => trim((string) ($request->input('from_name') ?: $request->input('form_name'))),
            // Convert empty strings to null so nullable validation works correctly.
            'reply_to' => $replyTo !== '' ? $replyTo : null,
            'personal_note' => $personalNote !== '' ? $personalNote : null,
        ]);

        $data = $request->validate([
            'from_email' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
            'reply_to' => 'nullable|email|max:255',
            'personal_note' => 'nullable|string|max:500',
        ]);

        $sender = $this->service->createPostmarkSender($data);

        if (! empty($sender) && isset($sender['ID'])) {
            // For non-sysadmin users, auto-authorize the sender for their company.
            if (! auth()->user()->isSysAdmin() && auth()->user()->company) {
                $company = auth()->user()->company;
                $ids = json_decode($company->senders ?? '[]', true);
                $ids = is_array($ids) ? $ids : [];
                if (! in_array($sender['ID'], $ids, true)) {
                    $ids[] = $sender['ID'];
                    $company->senders = json_encode(array_values($ids));
                    $company->save();
                }
            }

            return redirect()
                ->route('admin.email_senders.index')
                ->withSuccess('Gửi yêu cầu tạo sender thành công. Vui lòng kiểm tra email để xác nhận.');
        }

        // If Postmark returns an error body, surface a friendly message.
        $fallback = 'Tạo sender thất bại. Vui lòng thử lại sau.';
        $message = Arr::get($sender, 'Message') ?: Arr::get($sender, 'Error') ?: $fallback;

        return back()
            ->withInput()
            ->withErrors($message);
    }

    public function update(int $senderId, EmailSendersRequest $request)
    {
        $attributes = $request->only([
            'name',
        ]);

        $sender = $this->service->updatePostmarkSender($senderId, $attributes);

        if ($sender) {
            return back()->withSuccess('Cập nhật thành công');
        }

        return back()->withErrors('Cập nhật thất bại');
    }

    public function destroy(Campaign $campaign, Request $request) {}
}
