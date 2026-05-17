<header class="navbar-bl">
    <nav class="main-nav">
        @auth
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.*') ? 'active' : '' }}">Admin</a>
            @elseif (auth()->user()->isHospital())
                <a href="{{ route('hospital.dashboard') }}" class="{{ request()->routeIs('hospital.*') ? 'active' : '' }}">Dashboard</a>
            @else
                <a href="{{ route('donor.dashboard') }}" class="{{ request()->routeIs('donor.dashboard') ? 'active' : '' }}">Dashboard</a>
            @endif
        @endauth

        <a href="{{ route('urgent.index') }}" class="{{ request()->routeIs('urgent.*') ? 'active' : '' }}">Urgent</a>
        <a href="{{ route('map') }}" class="{{ request()->routeIs('map') ? 'active' : '' }}">Map</a>
        <a href="{{ route('campaigns.index') }}" class="{{ request()->routeIs('campaigns.*') ? 'active' : '' }}">Campaigns</a>
        <a href="{{ route('donors.top') }}" class="{{ request()->routeIs('donors.top') ? 'active' : '' }}">Top Donors</a>
        <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About</a>

        @guest
            <a href="{{ route('register') }}">Register</a>
            <a href="{{ route('login') }}">Login</a>
        @endguest

        <div class="nav-extra">
            <i class="fas fa-moon dark-toggle-icon" id="dark-icon" title="Toggle dark mode"></i>
        </div>
    </nav>

    <div class="nav-extra">
        @auth
            <a href="{{ route('profile.show') }}" class="profile-icon-link" title="{{ auth()->user()->full_name }}">
                <i class="fas fa-user-circle"></i>
            </a>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" style="background:none;border:none;color:white;cursor:pointer;padding:0;width:auto;font-size:22px;" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        @endauth

        <a href="{{ url('/') }}" class="brand-name">BloodLine <i class="fas fa-tint"></i></a>

        <a href="{{ url('/') }}" class="home-page-btn">Home</a>
    </div>
</header>
