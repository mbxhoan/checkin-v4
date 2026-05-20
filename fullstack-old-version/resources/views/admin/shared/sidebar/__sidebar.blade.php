@php
    $sidebarMenus = config('sidebar-menu.admin');
    $user = Auth::user();
    $exceptMenus = [];
    $filteredMenus = [];

    if (!auth()->user()->isAdmin()) {
        unset($sidebarMenus['users']);
    }

    if (!auth()->user()->isSysAdmin()) {
        unset($sidebarMenus['media']);
        if ($user->package_id) {
            $exceptMenus = config("info.packages.{$user->package->code}.excepts.menus") ?? [];

            /* Chùa Minh Hiệp */
            if ($user->email == 'cmh01@gmail.com') {
                $exceptMenus = array_filter($exceptMenus, function ($item) {
                    return !in_array($item, ['landing_pages', 'campaigns']);
                });

                $exceptMenus = array_values($exceptMenus);
            }

            /* Thành - MKT */
            if ($user->email == 'thanh.nv@delfi.com.vn') {
                $exceptMenus = array_filter($exceptMenus, function ($item) {
                    return !in_array($item, ['campaigns']);
                });

                $exceptMenus = array_values($exceptMenus);
            }
        }
    }

    foreach ($sidebarMenus as $key => $menu) {
        // Hide whole menu items for packages that don't include them (e.g. basic).
        if (count($exceptMenus) && in_array($key, $exceptMenus, true)) {
            continue;
        }

        $originalMenu = $menu;
        $hasVisibleSubMenus = false;

        if (isset($menu['subMenus']) && is_array($menu['subMenus'])) {
            foreach ($menu['subMenus'] as $subKey => $subMenu) {
                /* remove by excepts */
                if (count($exceptMenus) && in_array($subKey, $exceptMenus)) {
                    unset($menu['subMenus'][$subKey]);
                }

                $isVisible = false;

                if ($user->is_admin && isset($subMenu['is_admin']) && $subMenu['is_admin']) {
                    $isVisible = true;
                }

                if (isset($subMenu['roles']) && is_array($subMenu['roles']) && count($subMenu['roles']) > 0) {
                    foreach ($subMenu['roles'] as $role) {
                        if ($user->hasRole($role)) {
                            $isVisible = true;
                            break;
                        }
                    }
                }

                if (!$isVisible) {
                    unset($menu['subMenus'][$subKey]);
                } else {
                    $hasVisibleSubMenus = true;
                }
            }

            /* remove menu if no subMenus left */
            if (!count($menu['subMenus'])) {
                continue;
            }
        }

        $allowMenu = false;

        if ($hasVisibleSubMenus) {
            $allowMenu = true;
        }

        if (!$allowMenu && $user->is_admin && isset($menu['is_admin']) && $menu['is_admin']) {
            $allowMenu = true;
        }

        if (!$allowMenu && isset($menu['roles']) && is_array($menu['roles']) && count($menu['roles']) > 0) {
            foreach ($menu['roles'] as $role) {
                if ($user->hasRole($role)) {
                    $allowMenu = true;
                    break;
                }
            }
        }

        if ($allowMenu) {
            $filteredMenus[$key] = $menu;
        }
    }

    $sidebarMenus = $filteredMenus;
    $translateOrFallback = static function (string $translationKey, ?string $fallback = null): string {
        $translated = __($translationKey);
        if ($translated !== $translationKey) {
            return $translated;
        }

        return $fallback ?? $translationKey;
    };
@endphp

<ul class="navbar-nav navbar-sidenav hide-scrollbar menu-root" style="
        max-width: 250px;
    ">
    {{-- <li class="menu-title nav-item pt-2" key="t-menu">
        <span class="menu-title-text text-secondary fw-bold px-3 py-2">
            <span class="nav-link-text text-xs">
                MENU
            </span>
        </span>
    </li> --}}
    {{-- <li class="has-children">
        <div class="menu-toggle nav-item pe-lg-2 pe-0" aria-expanded="false" aria-controls="sm-users" id="btn-users">
            <div class="nav-link text-sub-menu text-light py-2 px-3">
                <i class="{{ "fa-solid fa-user fa-fw" }}" aria-hidden="true"></i>
                <span class="nav-link-text px-lg-3 px-0">
                    Users
                </span>
            </div>
            <svg class="chevron text-light" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M8 10l4 4 4-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
        </div>
        <ul class="submenu" id="sm-users" role="region" aria-labelledby="btn-users">
            <li class="nav-item" role="presentation" data-bs-toggle="tooltip" data-bs-placement="right" title="">
                <a class="text-sub-menu px-3 py-2 text-decoration-none">
                    <i class="fa-solid fa-user fa-fw opacity-0" aria-hidden="true"></i>
                    <span class="nav-link-text px-lg-3 px-0">
                        All users
                    </span>
                </a>
            </li>
            <li class="nav-item" role="presentation" data-bs-toggle="tooltip" data-bs-placement="right" title="">
                <a class="text-sub-menu px-3 py-2 text-decoration-none">
                    <i class="fa-solid fa-user fa-fw opacity-0" aria-hidden="true"></i>
                    <span class="nav-link-text px-lg-3 px-0">
                        Invitations
                    </span>
                </a>
            </li>
        </ul>
    </li> --}}
    <li>
        <ul class="px-0">
            @foreach ($sidebarMenus as $key => $menu)
                @if (isset($menu['subMenus']))
                    @php
                        $menuLabel = $translateOrFallback("sidebar.menus.{$key}", $menu['text'] ?? __('dashboard.dashboard'));
                    @endphp
                    <li class="nav-item" role="presentation" data-bs-toggle="tooltip" data-bs-placement="right"
                        title="{{ $menuLabel }}">
                        <div
                            class="nav-link text-sub-menu-title fw-bold {{ request()->route()->named($menu['route_prefix']) ? 'active' : '' }}">
                            <span class="nav-link-text text-xs text-secondary">
                                {{ $menuLabel }}
                            </span>
                        </div>
                    </li>
                    @foreach ($menu['subMenus'] as $subMenuKey => $subMenu)
                        @include('admin.shared.sidebar._menu', [
                            'key' => $subMenuKey,
                            'menu' => $subMenu,
                            'parentKey' => $key,
                        ])
                    @endforeach
                @else
                    @include('admin.shared.sidebar._menu', [
                        'key' => $key,
                        'menu' => $menu,
                        'parentKey' => null,
                    ])
                @endif
            @endforeach
        </ul>
    </li>
</ul>

<ul class="navbar-nav sidenav-toggler">
    <li class="nav-item">
        <a class="nav-link text-center" id="sidenavToggler">
            <x-icon name="angle-left" />
        </a>
    </li>
</ul>

{{-- <script>
    // Collapsible behavior with smooth height animation
    document.querySelectorAll('.menu-toggle[aria-controls]').forEach(btn => {
        const submenu = document.getElementById(btn.getAttribute('aria-controls'));

        // Ensure closed by default
        submenu.style.maxHeight = '0px';

        btn.addEventListener('click', () => {
            const expanded = btn.getAttribute('aria-expanded') === 'true';

            if (expanded) {
                // Close
                submenu.style.maxHeight = `${submenu.scrollHeight}px`; // set current height
                requestAnimationFrame(() => {
                    submenu.style.maxHeight = '0px';
                });
                btn.setAttribute('aria-expanded', 'false');
            } else {
                // Open
                submenu.style.maxHeight = '0px'; // reset for recalculation
                btn.setAttribute('aria-expanded', 'true');
                const targetHeight = submenu.scrollHeight;
                submenu.style.maxHeight = `${targetHeight}px`;
                // After transition, remove max-height to accommodate dynamic content
                submenu.addEventListener('transitionend', function tidy(e) {
                    if (btn.getAttribute('aria-expanded') === 'true') submenu.style.maxHeight =
                        'none';
                    submenu.removeEventListener('transitionend', tidy);
                });
            }
        });
    });
</script> --}}
