<div class="menu-item">
    <a class="menu-link" href="{{ route(($item->name ?? '') . '.view') }}">
        <span class="menu-icon">
            <span class="svg-icon svg-icon-2">
                <i class="bi {{ $item->icon ?? '' }} fs-1 {{ $color }}"></i>
            </span>
        </span>
        <span class="menu-title" style="color:white">
            {{ $item->{'name_' . app()->getLocale()} ?? '' }}
        </span>
    </a>
</div>
