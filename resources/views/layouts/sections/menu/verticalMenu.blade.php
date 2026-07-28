@php
    use Illuminate\Support\Facades\Route;
    $configData = Helper::appClasses();
    use Illuminate\Support\Facades\Auth;
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <!-- ! Hide app brand if navbar-full -->
    @if (!isset($navbarFull))
        <div class="app-brand demo">
            <a href="{{ url('/') }}" class="app-brand-link">
                <img src="{{ asset('assets/img/favicon/logo.png') }}" style="width: 165px;">
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                <i class="ti menu-toggle-icon d-none d-xl-block align-middle"></i>
                <i class="ti ti-x d-block d-xl-none ti-md align-middle"></i>
            </a>
        </div>
    @endif

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @foreach ($menuData[0]->menu as $menu)
            @if (!empty($menu->access) && Auth::guard($menu->access)->check())
                {{-- adding active and open class if child is active --}}

                {{-- menu headers --}}
                @if (isset($menu->menuHeader))
                    <li class="menu-header small">
                        <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
                    </li>
                @else
                    {{-- active menu method --}}
                    @php
                        $activeClass = null;
                        $currentRouteName = Route::currentRouteName();
                        $parts = explode('.', $currentRouteName);
                        $currentRouteName = implode('.', array_slice($parts, 0, 2));
                        // dd($currentRouteName);
                        if (strtolower($currentRouteName) === strtolower($menu->slug)) {
                            $activeClass = 'active';
                        } elseif (isset($menu->submenu)) {
                            if (gettype($menu->slug) === 'array') {
                                foreach ($menu->slug as $slug) {
                                    if (
                                        str_contains($currentRouteName, $slug) and
                                        strpos($currentRouteName, $slug) === 0
                                    ) {
                                        $activeClass = 'active open';
                                    }
                                }
                            } else {
                                if (
                                    str_contains($currentRouteName, $menu->slug) and
                                    strpos($currentRouteName, $menu->slug) === 0 and
                                    $currentRouteName != 'check_history'
                                ) {
                                    $activeClass = 'active open';
                                }
                            }
                        }
                        $tooltip='';
                        $tooltips = [
                            '/user/client'=> 'Company/Clients you receive payment from',
                            '/user/vendor' => 'Company/Clients you send payment to',
                        ];
                        if (isset($tooltips[$menu->url])) {
                            $tooltip = $tooltips[$menu->url];
                        }

                        //if(in_array($menu->slug,['admin.suggestions','user.suggestion'])){
                          //  $activeClass .= ' mt-auto';
                        //}
                    @endphp

                    <li class="menu-item {{ $activeClass }}">
                        <a data-toggle="tooltip" data-placement="top" title="{{ $tooltip }}"
                            href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
                            class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                            @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
                            @isset($menu->icon)
                                <i class="{{ $menu->icon }}"></i>
                            @endisset
                            <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
                            @isset($menu->badge)
                                <div class="badge bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}
                                </div>
                            @endisset
                        </a>

                        {{-- submenu --}}
                        @isset($menu->submenu)
                            @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
                        @endisset
                    </li>
                @endif
            @endif
        @endforeach
    </ul>
    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
</aside>
