<div class="menu menu-column menu-rounded menu-sub-indention" id="#kt_app_sidebar_menu"
     data-kt-menu="true" data-kt-menu-expand="false">
    <div data-kt-menu-trigger="click"
         class="menu-item {{ $active_menu == ($item->name ?? '') ? 'here show' : '' }} menu-accordion">
        <span class="menu-link">
            <span class="menu-icon">
                <span class="svg-icon svg-icon-2">
                    <i class="bi {{ $item->icon ?? '' }} fs-1 text-{{  $item->color }}"></i>
                </span>
            </span>
            <span class="menu-title" style="color:white">
                {{ $item->{'name_' . app()->getLocale()} ?? '' }}
            </span>
            <span class="menu-arrow"></span>
        </span>
        <div class="menu-sub menu-sub-accordion">
            @foreach ($item->mychild ?? [] as $child)
                @php $permission = 'admin.' . ($child->name ?? '') . '.view'; @endphp
                @can($permission)
                    @php $cr = ($child->name ?? '') . '.view'; @endphp
                    @if(\Illuminate\Support\Facades\Route::has($cr))
                    <div class="menu-item">
                        <a class="menu-link {{ $active_menu == ($child->name ?? '') ? 'active' : '' }}"
                           href="{{ route($cr) }}">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title" style="color:white">
                                {{ $child->{'name_' . app()->getLocale()} ?? '' }}
                            </span>
                        </a>
                    </div>
                    @endif
                @endcan
            @endforeach
        </div>
    </div>
</div>
