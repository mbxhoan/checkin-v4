<nav class="navbar bg-transparent sticky-top navbar-expand-md">
    <div class="container">
        <!-- Branding Image -->
        <a href="{{ route('home') }}" class="navbar-brand">
            {{-- {{ config('app.name', 'Laravel') }} --}}
            <img src="{{ asset(config('info.page.logo_1.internal_path_white')) }}" alt="{{  __('dashboard.dashboard') }}" width="30%">
        </a>

        <!-- Collapsed Hamburger -->
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarCollapse"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarCollapse">
            {{-- @admin
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link">
                            @lang('dashboard.dashboard')
                        </a>
                    </li>
                </ul>
            @endadmin --}}

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

            <ul class="navbar-nav ms-auto align-items-md-center">
                @guest
                    <li class="nav-item me-2">
                        <a href="{{ route('login') }}" class="text-white">
                            @lang('auth.login')
                        </a>
                    </li>
                    <li class="nav-item me-2">
                        <a href="{{ route('register') }}" class="text-white">
                            @lang('auth.register')
                        </a>
                    </li>
                @else
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle text-white" id="navbarDropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ Auth::user()->name }}
                        </a>

                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                            {{-- <a href="{{ route('users.show', Auth::user()) }}" class="dropdown-item">
                                @lang('users.public_profile')
                            </a> --}}

                            <a href="{{ route('users.edit') }}" class="dropdown-item">
                                @lang('users.settings')
                            </a>

                            <div class="dropdown-divider"></div>

                            <a href="{{ url('/logout') }}"
                                class="dropdown-item"
                                onclick="event.preventDefault();
                                            document.getElementById('logout-form').submit();">
                                @lang('auth.logout')
                            </a>

                            <form id="logout-form" class="d-none" action="{{ url('/logout') }}" method="POST">
                                {{ csrf_field() }}
                            </form>
                        </div>
                    </li>
                @endguest

                <li class="nav-item dropdown">
                    <a
                        href="#"
                        class="nav-link dropdown-toggle text-white d-inline-flex align-items-center gap-2"
                        id="navbarLanguageDropdown"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        aria-label="{{ __('common.language') }}"
                    >
                        <img src="{{ $currentLanguage['flag'] }}" alt="{{ $currentLanguage['name'] }}" width="18" height="12">
                        <span class="fw-semibold">{{ $currentLanguage['label'] }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarLanguageDropdown">
                        @foreach ($languageOptions as $localeCode => $option)
                            <li>
                                <a
                                    href="{{ route('change-language', ['locale' => $localeCode]) }}"
                                    class="dropdown-item d-flex align-items-center gap-2 {{ $currentLocale === $localeCode ? 'active' : '' }}"
                                >
                                    <img src="{{ $option['flag'] }}" alt="{{ $option['name'] }}" width="18" height="12">
                                    <span>{{ $option['name'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
