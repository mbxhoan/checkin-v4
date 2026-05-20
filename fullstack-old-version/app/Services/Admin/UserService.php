<?php
namespace App\Services\Admin;

use App\Models\Company;
use App\Models\User;
use App\Services\BaseService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Services\Middleware\EmailService as MiddlewareEmailService;

class UserService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(User::class);
    }

    public function company()
    {
        return app(CompanyService::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function role()
    {
        return app(RoleService::class);
    }

    public function package()
    {
        return app(PackageService::class);
    }

    public function middleware_email()
    {
        return app(MiddlewareEmailService::class);
    }

    public function applyFilters(array $filters = [], int $paginate = 0, $query = null)
    {
        if (empty($query)) {
            $query = $this->getQuery(); // Get the base query
        }

        if (count($filters)) {
            foreach ($filters as $key => $value) {
                $query->where($key, $value);
            }
        }

        // if (!auth()->user()->isSysAdmin()) {
        //     $query->where('company_id', auth()->user()->company->id);
        // } else {
        //     if (request()->filled('company_id')) {
        //         $attributes['company_id'] = request()->input('company_id');
        //     }
        // }

        $query->where('status', '!=', User::STATUS_DELETED);
        $query->where('id', '!=', auth()->user()->id);
        $query->orderBy('updated_at', 'DESC');
        $query->orderBy('status', 'ASC');

        /* lọc */
        if (request()->filled('company_id')) {
            $attributes['company_id'] = request()->input('company_id');
        }

        if (request()->filled('event_id')) {
            $attributes['event_id'] = request()->input('event_id');
        }

        if (request()->filled('status')) {
            $attributes['status'] = request()->input('status');
        }

        if (request()->filled('type')) {
            $attributes['type'] = request()->input('type');
        }

        $dateField = request()->input('field_date');

        if ($dateField) {
            if (request()->filled('from_date')) {
                $query->whereDate(request()->input('field_date'), '>=', request()->input('from_date'));
            }

            if (request()->filled('to_date')) {
                $query->whereDate(request()->input('field_date'), '<=', request()->input('to_date'));
            }
        }

        if (isset($attributes) && count($attributes)) {
            foreach ($attributes as $key => $value) {
                $query->where($key, $value);
            }
        }

        return $paginate > 0 ? $query->paginate($paginate) : ($query->get() ?? collect());
    }

    public function ensureLimited(int $companyId, string $field)
    {
        $company = $this->company()->findById($companyId);

        if (isset($company->$field) && $company->$field > 0) {
            $list = $this->getListByAttributes([
                'company_id' => $companyId,
            ]);

            if (!empty($list) && $list->count() >= $company->$field) {
                return false;
            }
        }

        return true;
    }

    public function sendVerification($user)
    {
        $templateId = 39930831;
        $url = URL::temporarySignedRoute(
            'users.verify', // The route name
            Carbon::now()->addMinutes(config('app.verification_expire') ?? 5), // Expiration time
            [
                'prefix'        => $user->username,
                'verify_token'  => $user->verify_token
            ]
        );

        $variables = [
            "name"          => $user->name,
            "email"         => $user->email,
            "product_name"  => env('APP_NAME'),
            "action_url"    => $url,
            // "action_url"    => route('users.verify', [
            //     'prefix'    => $user->username,
            //     'verify_token'     => $user->verify_token,
            // ]),
            "package"       => $user->package_id ? config('info.packages')[$user->package->code]['name'] : null,
            "start_date"    => now()->format('d-m-Y'),
            "end_date"      => $user->expire_date ? humanize_date($user->expire_date, 'd-m-Y') : null,
            "support_email" => env('FROM_MAIL'),
        ];

        $this->middleware_email()->sendMailTestCurl($user->email, $templateId, $variables);
        return true;
    }

    public function sendRegistrationNotifications(User $user): array
    {
        $user->loadMissing(['company', 'package']);

        $templates = config('info.onboarding_emails.templates', []);
        $salesConfig = config('info.onboarding_emails.sales_notification', []);
        $templateModel = $this->buildOnboardingTemplateModel($user);
        $errors = [];

        $customerTemplateId = (int) ($templates['customer_registered'] ?? 0);
        if ($customerTemplateId > 0) {
            $customerResult = $this->sendPostmarkTemplate($user->email, $customerTemplateId, $templateModel);
            if (!$customerResult['ok']) {
                $errors[] = "Không gửi được mail đăng ký cho khách: {$customerResult['message']}";
            }
        } else {
            $errors[] = 'Chưa cấu hình template customer_registered.';
        }

        $salesTemplateId = (int) ($templates['sales_registered'] ?? 0);
        $salesTo = $salesConfig['to'] ?? config('info.admin_email');
        $salesCc = $salesConfig['cc'] ?? [];
        if ($salesTemplateId > 0 && !empty($salesTo)) {
            $salesResult = $this->sendPostmarkTemplate($salesTo, $salesTemplateId, $templateModel, [
                'cc' => $salesCc,
            ]);
            if (!$salesResult['ok']) {
                $errors[] = "Không gửi được mail thông báo cho sale: {$salesResult['message']}";
            }
        } else {
            $errors[] = 'Chưa cấu hình template sales_registered hoặc người nhận sale.';
        }

        if (!empty($errors)) {
            Log::error('Send registration onboarding mails failed', [
                'user_id' => $user->id,
                'errors' => $errors,
            ]);

            return [
                'status' => false,
                'message' => implode(' | ', $errors),
            ];
        }

        return [
            'status' => true,
            'message' => 'Đã gửi mail onboarding sau đăng ký.',
        ];
    }

    public function approveAndNotifyAccess(User $user, ?User $approvedBy = null): array
    {
        $user->loadMissing(['company', 'package']);

        $templateId = (int) config('info.onboarding_emails.templates.customer_approved', 0);
        if ($templateId <= 0) {
            return [
                'status' => false,
                'message' => 'Chưa cấu hình template customer_approved.',
            ];
        }

        $sendResult = $this->sendPostmarkTemplate(
            $user->email,
            $templateId,
            $this->buildOnboardingTemplateModel($user, $approvedBy)
        );

        if (!$sendResult['ok']) {
            Log::error('Approve account mail failed', [
                'user_id' => $user->id,
                'message' => $sendResult['message'],
            ]);

            return [
                'status' => false,
                'message' => "Gửi mail kích hoạt thất bại: {$sendResult['message']}",
            ];
        }

        $user->update([
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => $user->email_verified_at ?? now(),
            'updated_by' => $approvedBy?->id,
        ]);

        if ($user->company && $user->company->status !== Company::STATUS_ACTIVE) {
            $user->company->update([
                'status' => Company::STATUS_ACTIVE,
                'updated_by' => $approvedBy?->id,
            ]);
        }

        return [
            'status' => true,
            'message' => 'Đã kích hoạt tài khoản và gửi mail thông báo thành công.',
        ];
    }

    private function sendPostmarkTemplate(string $to, int $templateId, array $templateModel, array $options = []): array
    {
        $postmarkToken = (string) config('services.postmark.token');
        $fromAddress = (string) config('mail.from.address');
        $fromName = (string) config('mail.from.name', config('app.name'));

        if ($postmarkToken === '' || $fromAddress === '') {
            return [
                'ok' => false,
                'message' => 'Thiếu POSTMARK_TOKEN hoặc MAIL_FROM_ADDRESS.',
            ];
        }

        $from = $fromName !== '' ? "{$fromName} <{$fromAddress}>" : $fromAddress;
        $apiUrl = rtrim((string) env('POSTMARK_API_URL', 'https://api.postmarkapp.com'), '/');

        $payload = [
            'From' => $from,
            'To' => $to,
            'TemplateId' => $templateId,
            'TemplateModel' => $templateModel,
            'MessageStream' => env('POSTMARK_MESSAGE_STREAM', 'outbound'),
        ];

        if (!empty($options['cc'])) {
            $payload['Cc'] = is_array($options['cc'])
                ? implode(',', array_filter($options['cc']))
                : (string) $options['cc'];
        }

        if (!empty($options['bcc'])) {
            $payload['Bcc'] = is_array($options['bcc'])
                ? implode(',', array_filter($options['bcc']))
                : (string) $options['bcc'];
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Postmark-Server-Token' => $postmarkToken,
            ])->post("{$apiUrl}/email/withTemplate", $payload);
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'message' => $exception->getMessage(),
            ];
        }

        if (!$response->successful()) {
            return [
                'ok' => false,
                'message' => "HTTP {$response->status()} - {$response->body()}",
            ];
        }

        $body = $response->json();
        if (is_array($body) && isset($body['ErrorCode']) && (int) $body['ErrorCode'] !== 0) {
            return [
                'ok' => false,
                'message' => "Postmark {$body['ErrorCode']}: " . ($body['Message'] ?? 'Lỗi không xác định'),
            ];
        }

        return [
            'ok' => true,
            'message' => 'OK',
        ];
    }

    private function buildOnboardingTemplateModel(User $user, ?User $approvedBy = null): array
    {
        $company = $user->company;
        $package = $user->package;
        $packageCode = $package?->code;
        $registeredAt = $user->registered_at ?: $user->created_at;
        $registeredAtCarbon = $registeredAt ? Carbon::parse($registeredAt) : null;

        return [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'position' => $user->position,
            'username' => $user->username,
            'company_name' => $company?->name,
            'company_code' => $company?->code,
            'company_type' => $company?->getTypeText() ?? $company?->type,
            'devices' => $this->formatCompanyDevices($company?->devices),
            'package' => $packageCode ? (config("info.packages.{$packageCode}.name") ?? $packageCode) : null,
            'package_code' => $packageCode,
            'registered_at' => $registeredAtCarbon?->format('d/m/Y H:i:s'),
            'registered_date' => $registeredAtCarbon?->format('d/m/Y'),
            'registered_time' => $registeredAtCarbon?->format('H:i:s'),
            'registered_at_iso' => $registeredAtCarbon?->toIso8601String(),
            'timezone' => config('app.timezone'),
            'support_email' => config('info.admin_email'),
            'login_url' => route('login'),
            'approved_by' => $approvedBy?->name,
            'approved_at' => $approvedBy ? now()->format('d/m/Y H:i:s') : null,
        ];
    }

    private function formatCompanyDevices($devices): string
    {
        $deviceMap = config('info.devices', []);
        if (empty($deviceMap)) {
            return '';
        }

        if (is_string($devices)) {
            $decoded = json_decode($devices, true);
            $devices = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($devices) || empty($devices)) {
            return 'Không có';
        }

        $codes = [];
        foreach ($devices as $key => $value) {
            if (is_string($key) && array_key_exists($key, $deviceMap)) {
                $codes[] = $key;
            }

            if (is_string($value) && array_key_exists($value, $deviceMap)) {
                $codes[] = $value;
            }
        }

        $codes = array_values(array_unique($codes));
        if (empty($codes)) {
            return 'Không có';
        }

        $deviceLabels = array_map(function ($code) use ($deviceMap) {
            return $deviceMap[$code] ?? $code;
        }, $codes);

        return implode(', ', $deviceLabels);
    }

    public function signOut(User $user)
    {
        $user->tokens()->delete();

        if ($user->session_id) {
            $path = storage_path('framework/sessions/' . $user->session_id);
            if (file_exists($path)) {
                unlink($path);
            }

            $user->session_id = null;

        }

        $user->last_login_at = null;
        $user->save();
        return true;
    }
}
