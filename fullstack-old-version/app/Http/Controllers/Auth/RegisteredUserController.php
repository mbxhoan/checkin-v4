<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Users\RegisterRequest;
use App\Models\Company;
use App\Models\Package;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\UserService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(private UserService $userService) {}

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', [
            'companyTypes' => Company::getTypes(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        // $request->validate([
        //     'g-recaptcha-response' => [
        //         new ReCaptcha
        //     ]
        // ]);

        /* get package */
        $package = Package::where('code', $request->package)->first();

        /* get roles */
        $roles[] = Role::where('name', Role::ROLE_ADMIN)->first()->id;

        /* calculate expire_date */
        $expireIn = config("info.packages.{$package->code}.expire_in") ?? null;
        $expireDate = ! empty($expireIn) ? Carbon::now()->addDays($expireIn) : null;

        /* create company */
        $company = Company::create([
            'code' => Company::generateUniqueCode(Company::PREFIX),
            'name' => $request->company_name,
            'type' => $request->company_type,
            'devices' => json_encode($request->devices),
            'limited_clients' => config("info.packages.{$package->code}.limited_clients") ?? null,
            'limited_events' => config("info.packages.{$package->code}.limited_events") ?? null,
            'limited_users' => config("info.packages.{$package->code}.limited_users") ?? null,
            'limited_emails' => config("info.packages.{$package->code}.limited_emails") ?? null,
            'status' => Company::STATUS_NEW,
            'languages' => $request->has('languages') ? json_encode($request) : json_encode([1, 2]),
        ]);

        $user = User::create([
            'company_id' => $company->id,
            'package_id' => $package->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'position' => $request->position,
            'password' => Hash::make($request->password),
            'username' => Helper::getUsernameFromEmail($request->email),
            'verify_token' => Str::random(40),
            'expire_date' => $expireDate ?? null,
            'status' => User::STATUS_INACTIVE,
            'registered_at' => now(),
            'must_accept_terms' => false,
            'terms_accepted_at' => now(),
        ]);

        /* assign roles */
        $user->roles()->sync($roles);

        $notifyResult = $this->userService->sendRegistrationNotifications($user);
        if (empty($notifyResult['status'])) {
            Log::warning('Registration onboarding mails partially failed', [
                'user_id' => $user->id,
                'message' => $notifyResult['message'] ?? null,
            ]);
        }

        return redirect()->route('login')->withSuccess(
            __('auth.messages.registration_pending')
        );
    }
}
