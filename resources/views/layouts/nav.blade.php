<nav class="sb-topnav navbar navbar-expand navbar-dark bg-custom justify-content-between">
    <div class="d-flex justify-content-between @auth() w-14-rem @endauth">
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-3" href="/">
        <svg class="" width="50" height="50" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" style="display: inline-block; vertical-align: middle;">
            <path d="M918.187 180.907l-303.787 662.187h102.4l303.787-662.187h-102.4zM614.4 180.907h-102.4l-153.6 354.987h102.4l153.6-354.987zM716.8 180.907l-307.2 662.187h102.4l303.787-662.187h-98.987zM307.2 180.907l-303.787 662.187h102.4l303.787-662.187h-102.4z" style="fill: rgb(255, 255, 255);"></path>
        </svg>
        BAKWIT
    </a>
    @auth()
    <!-- Sidebar Toggle-->
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
    </div>
    <!-- Navbar-->
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="#"
                       onclick="event.preventDefault();
                       document.getElementById('logout-form').submit();">Logout</a></li>
            </ul>
        </li>
    </ul>
    @else
    <!-- Navbar-->
    <ul class="navbar-nav align-items-center">
        <li class="nav-item {{ request()->routeIs('barangays.index*') ? 'active' : '' }}">
            <a href="{{ route('barangays.index') }}" class="nav-link">Barangays</a>
        </li>
        <li class="nav-item">
            <a href="{{ route('calamity.index') }}"
               class="nav-link {{ request()->routeIs('calamity.index') ? 'active' : '' }}">Calamity</a>
        </li>
        <li class="nav-item">
            <a href="{{ route('gsis.index') }}" class="nav-link {{ request()->routeIs('gsis.index') ? 'active' : '' }}">GIS</a>
        </li>
    </ul>
    @endauth
</nav>
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>