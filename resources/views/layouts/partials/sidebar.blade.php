<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

  <!-- Sidebar - Brand -->
  <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/dashboard') }}">
      <div class="sidebar-brand-icon ">
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
          <span>Dashboard</span></a>
  </li>

  <!-- Divider -->
  <hr class="sidebar-divider">

  <!-- Heading -->
  <div class="sidebar-heading">Interface</div>

  {{-- Data Hujan --}}
  <li class="nav-item {{ request()->is('rainfall') ? 'active' : '' }}">
      <a class="nav-link" href="{{ url('rainfall') }}">
          <i class="fas fa-fw fa-cloud-rain"></i>
          <span>Data Hujan</span>
      </a>
  </li>

  <!-- Example Nav Item -->
  {{-- <li class="nav-item">
      <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
          aria-expanded="true" aria-controls="collapseTwo">
          <i class="fas fa-fw fa-cog"></i>
          <span>Components</span>
      </a>
      <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
              <h6 class="collapse-header">Custom Components:</h6>
              <a class="collapse-item" href="#">Buttons</a>
              <a class="collapse-item" href="#">Cards</a>
          </div>
      </div>
  </li> --}}

  {{-- data user / admin access --}}
  @auth
    @if(auth()->user()->role === 'admin')
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Data Pengguna</div>
        {{-- data user / admin access --}}
        <li class="nav-item {{ request()->is('users') ? 'active' : '' }}">
            <a class="nav-link" href="{{ url('users') }}">
                <i class="fas fa-fw fa-users"></i>
                <span>Data User</span>
            </a>
        </li>
    @endif
  @endauth

  <!-- Divider -->
  <hr class="sidebar-divider d-none d-md-block">

  <!-- Sidebar Toggler -->
  <div class="text-center d-none d-md-inline">
      <button class="rounded-circle border-0" id="sidebarToggle"></button>
  </div>

</ul>
<!-- End of Sidebar -->
