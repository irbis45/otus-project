<ul class="navbar-nav me-auto mb-2 mb-lg-0">
    @foreach($publicMenuItems as $item)
        <li class="nav-item {{ request()->routeIs($item['route']) ? 'active' : '' }}">
            <a class="nav-link" href="{{ route($item['route']) }}">
                @if(!empty($item['icon']))
                    <i class="{{ $item['icon'] }}"></i>
                @endif
                {{ $item['title'] }}
            </a>
        </li>
    @endforeach
</ul>
