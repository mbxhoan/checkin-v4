<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="{{ route('admin.dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ URL::asset('build/images/logo.svg') }}" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ URL::asset('build/images/logo-dark.png') }}" alt="" height="17">
                    </span>
                </a>
                <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ URL::asset('build/images/logo-light.svg') }}" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ URL::asset('build/images/logo-light.png') }}" alt="" height="40">
                    </span>
                </a>
            </div>
            <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect" id="vertical-menu-btn">
                <i class="fa fa-fw fa-bars"></i>
            </button>
            <div class="header-item d-flex align-items-center justify-content-center flex-grow-1">
                @yield('title')
                <div class="ms-2">
                    @yield('buttons')
                </div>
            </div>
        </div>
        <div class="d-flex">
            @php
                $languageOptions = [
                    'vi' => [
                        'label' => 'VI',
                        'name' => __('common.locales.vi'),
                        'flag' => asset('build/images/flags/vn.png'),
                    ],
                    'en' => [
                        'label' => 'EN',
                        'name' => __('common.locales.en'),
                        'flag' => asset('build/images/flags/us.jpg'),
                    ],
                ];
                $currentLocale = app()->getLocale();
                $currentLanguage = $languageOptions[$currentLocale] ?? $languageOptions['vi'];
            @endphp
            <div class="dropdown d-inline-block" id="topbarLanguageDropdownWrap">
                <button
                    type="button"
                    class="btn header-item waves-effect dropdown-toggle d-inline-flex align-items-center gap-2"
                    id="topbarLanguageDropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                >
                    <img src="{{ $currentLanguage['flag'] }}" alt="{{ $currentLanguage['name'] }}" height="16">
                    <span class="align-middle fw-semibold">{{ $currentLanguage['label'] }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="topbarLanguageDropdown">
                    @foreach ($languageOptions as $localeCode => $option)
                        <a
                            href="{{ route('change-language', ['locale' => $localeCode]) }}"
                            class="dropdown-item d-flex align-items-center gap-2 {{ $currentLocale === $localeCode ? 'active' : '' }}"
                        >
                            <img src="{{ $option['flag'] }}" alt="{{ $option['name'] }}" width="18" height="12">
                            <span>{{ $option['name'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="dropdown d-inline-block">
                <button type="button"
                        class="btn header-item waves-effect"
                        id="page-header-user-dropdown"
                        data-bs-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false">
                    <img class="rounded-circle header-profile-user"
                        src="{{ isset(Auth::user()->avatar) ? asset(Auth::user()->avatar) : asset('build/images/users/user-dummy-img.jpg') }}"
                        alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ms-1 font-size-13">{{ ucfirst(Auth::user()->name) }}</span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="page-header-user-dropdown" style="min-width: 220px;">
                    <a href="{{ route('users.edit') }}" class="dropdown-item py-2">
                        <i class="bx bx-user me-2"></i> @lang('users.settings')
                    </a>

                    <a href="{{ asset(config('info.document.internal_path')) }}" class="dropdown-item py-2" download>
                        <i class="bx bx-book me-2"></i> {{ __('common.topbar.documentation') }}
                    </a>

                    @sys_admin
                    <a href="{{ route('admin.histories.index') }}" class="dropdown-item py-2">
                        <i class="bx bx-history me-2"></i> {{ __('common.topbar.history') }}
                    </a>
                    @endsys_admin

                    @sys_admin
                    <a href="{{ route('admin.logs') }}" target="_blank" class="dropdown-item py-2">
                        <i class="bx bx-lock-open me-2"></i> {{ __('common.topbar.activity_logs') }}
                    </a>
                    @endsys_admin

                    <div class="dropdown-divider my-1"></div>

                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 text-danger">
                            <i class="bx bx-power-off me-2 text-danger"></i> @lang('auth.logout')
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
<!--  Change-Password example -->
<div class="modal fade change-password" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">{{ __('common.topbar.change_password') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="change-password">
                    @csrf
                    <input type="hidden" value="{{ Auth::user()->id }}" id="data_id">
                    <div class="mb-3">
                        <label for="current_password">{{ __('common.topbar.current_password') }} <span class="text-danger">*</span></label>
                        <input id="current-password" type="password"
                            class="form-control @error('current_password') is-invalid @enderror"
                            name="current_password" autocomplete="current_password"
                            placeholder="{{ __('common.topbar.current_password') }}" value="{{ old('current_password') }}">
                        <div class="text-danger" id="current_passwordError" data-ajax-feedback="current_password">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="newpassword">{{ __('common.topbar.new_password') }} <span class="text-danger">*</span></label>
                        <input id="password" type="password"
                            class="form-control @error('password') is-invalid @enderror" name="password"
                            autocomplete="new_password" placeholder="{{ __('common.topbar.new_password') }}">
                        <div class="text-danger" id="passwordError" data-ajax-feedback="password"></div>
                    </div>

                    <div class="mb-3">
                        <label for="userpassword">{{ __('common.topbar.confirm_password') }} <span class="text-danger">*</span></label>
                        <input id="password-confirm" type="password" class="form-control"
                            name="password_confirmation" autocomplete="new_password"
                            placeholder="{{ __('common.topbar.confirm_password') }}">
                        <div class="text-danger" id="password_confirmError" data-ajax-feedback="password-confirm">
                        </div>
                    </div>

                    <div class="mt-3 d-grid">
                        <button class="btn btn-primary waves-effect waves-light UpdatePassword"
                            data-id="{{ Auth::user()->id }}" type="submit">{{ __('common.topbar.update_password') }}</button>
                    </div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
