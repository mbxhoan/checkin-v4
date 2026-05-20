@php
    $xIconPrefix = $menu['x_icon_prefix'] ?? "fa-solid";
    $translateOrFallback = $translateOrFallback ?? static function (string $translationKey, ?string $fallback = null): string {
        $translated = __($translationKey);
        if ($translated !== $translationKey) {
            return $translated;
        }

        return $fallback ?? $translationKey;
    };

    $menuTranslationKey = !empty($parentKey)
        ? "sidebar.submenus.{$parentKey}.{$key}"
        : "sidebar.menus.{$key}";

    $menuLabel = $translateOrFallback($menuTranslationKey, $menu['text'] ?? __('dashboard.dashboard'));
@endphp

<li class="nav-item" role="presentation" data-bs-toggle="tooltip" data-bs-placement="right" title="{{ $menuLabel }}">
    <a class="nav-link text-sub-menu {{ request()->route()->named($menu['route_prefix']) ? 'active' : '' }}" href="{{ route($menu['route']) }}">
        <i class="{{ "{$xIconPrefix} fa-{$menu['x_icon_name']} fa-fw" }}" aria-hidden="true"></i>
        <span class="nav-link-text px-lg-2 px-0 text-sm">
            {{ $menuLabel }}
        </span>
    </a>
</li>
