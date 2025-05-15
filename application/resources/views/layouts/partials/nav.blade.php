<ul class="navbar-nav ms-auto">
    @guest
        @if (Route::has('login'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('login') }}">Login</a>
            </li>
        @endif
        @if (Route::has('register'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('register') }}">Register</a>
            </li>
        @endif
    @else
        <li class="nav-item">
            <a class="nav-link" href="{{ route('dashboard.index') }}">
                <i class="fa fa-home me-1"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('account.profile') }}">
                <i class="fa fa-id-card me-1"></i> Profile
            </a>
        </li>

        @can('viewAny', App\Models\User::class)
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.manage-users.index') }}">
                    Manage Users
                </a>
            </li>
        @endcan

        @can('viewAny', App\Models\Event::class)
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.manage-events.index') }}">
                    Manage Events
                </a>
            </li>
        @endcan

        @can('staff-scan')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('staff.scan-tickets.index') }}">
                    Scan Tickets
                </a>
            </li>
        @endcan

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button"
               data-bs-toggle="dropdown">{{ Auth::user()->name }}</a>
            <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="{{ route('logout') }}"
                   onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    Logout
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </li>
    @endguest
</ul>
