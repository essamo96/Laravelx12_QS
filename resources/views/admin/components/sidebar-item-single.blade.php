@php $r = ($item->name ?? '') . '.view'; @endphp
@if(\Illuminate\Support\Facades\Route::has($r))
<div class="menu-item">
    <a class="menu-link {{ $active_menu == ($item->name ?? '') ? 'here show' : '' }}" href="{{ route($r) }}">
        <span class="menu-icon">
            <span class="svg-icon svg-icon-2">
                <i class="bi {{ $item->icon ?? '' }} fs-1 text-{{  $item->color }}"></i>
            </span>
        </span>
        <span class="menu-title" style="color:white">
            {{ $item->{'name_' . app()->getLocale()} ?? '' }}
        </span>
    </a>
</div>
@endif
