<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="position-sticky pt-3 sidebar-sticky">
        <ul class="nav flex-column">
            @foreach($adminMenuItems as $item)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}" href="{{ route($item['route']) }}">
                        <span class="nav-icon"><i class="{{ $item['icon'] }}"></i></span>
                        {{ $item['title'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Аккаунт</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <a class="nav-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                        Выход
                    </a>
                </form>
            </li>
        </ul>
    </div>
</nav>
