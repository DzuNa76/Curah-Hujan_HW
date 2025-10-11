<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/dashboard') }}">
        <div class="sidebar-brand-icon">
            <i class="fas fa-cloud-rain"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Peramalan Curah Hujan</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ url('dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">Data Curah Hujan</div>

    <!-- Nav Item - Data Hujan -->
    <li class="nav-item {{ request()->is('rainfall*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('rainfall.index') }}">
            <i class="fas fa-fw fa-cloud-rain"></i>
            <span>Data Hujan</span>
        </a>
    </li>

    <!-- Nav Item - Forecasting -->
    <li class="nav-item {{ request()->is('forecasting*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('forecasting.index') }}">
            <i class="fas fa-fw fa-chart-line"></i>
            <span>Peramalan</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">Master Data Wilayah</div>

    <!-- Nav Item - Master Data Collapse Menu -->
    @php
        $isMasterActive = request()->is('master/regencies*') ||
                          request()->is('master/districts*') ||
                          request()->is('master/villages*') ||
                          request()->is('master/stations*');
    @endphp

    <li class="nav-item {{ $isMasterActive ? 'active' : '' }}">
        <a class="nav-link {{ $isMasterActive ? '' : 'collapsed' }}"
           href="#"
           data-toggle="collapse"
           data-target="#collapseMaster"
           aria-expanded="{{ $isMasterActive ? 'true' : 'false' }}"
           aria-controls="collapseMaster">
            <i class="fas fa-fw fa-database"></i>
            <span>Master Wilayah</span>
        </a>
        <div id="collapseMaster"
             class="collapse {{ $isMasterActive ? 'show' : '' }}"
             aria-labelledby="headingMaster"
             data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Wilayah & Stasiun:</h6>
                <a class="collapse-item {{ request()->is('master/regencies*') ? 'active' : '' }}"
                   href="{{ route('regencies.index') }}">
                   Kabupaten / Kota
                </a>
                <a class="collapse-item {{ request()->is('master/districts*') ? 'active' : '' }}"
                   href="{{ route('districts.index') }}">
                   Kecamatan
                </a>
                <a class="collapse-item {{ request()->is('master/villages*') ? 'active' : '' }}"
                   href="{{ route('villages.index') }}">
                   Desa / Kelurahan
                </a>
                <a class="collapse-item {{ request()->is('master/stations*') ? 'active' : '' }}"
                   href="{{ route('stations.index') }}">
                   Stasiun / Pos Pengamatan
                </a>
            </div>
        </div>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    @auth
        @if(auth()->user()->role === 'admin')
            <div class="sidebar-heading">Manajemen Pengguna</div>

            <li class="nav-item {{ request()->is('users*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('users') }}">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Data User</span>
                </a>
            </li>

            <hr class="sidebar-divider">
        @endif
    @endauth

    <!-- Sidebar Toggler -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->
