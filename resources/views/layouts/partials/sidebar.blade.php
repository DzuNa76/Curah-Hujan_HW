<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <!-- isi sidebar -->
    <div class="sidebar-brand-wrapper d-none d-lg-flex align-items-center justify-content-center fixed-top">
      <a class="sidebar-brand brand-logo" style="color: white;" href="{{ url('/dashboard') }}">Peramalan Hujan</a>
      <a class="sidebar-brand brand-logo-mini" style="color: white;" href="{{ url('/dashboard') }}">PH</a>
    </div>
    <ul class="nav">
      <li class="nav-item nav-category"><span class="nav-link">Navigation</span></li>
      <li class="nav-item menu-items">
        <a class="nav-link" href="{{ url('/dashboard') }}">
          <span class="menu-icon"><i class="mdi mdi-speedometer"></i></span>
          <span class="menu-title">Dashboard</span>
        </a>
      </li>
      {{-- Data Peramalan --}}
      <li class="nav-item menu-items">
        <a class="nav-link" href="{{ url('/rainfall') }}">
          <span class="menu-icon"><i class="mdi mdi-chart-bar"></i></span>
          <span class="menu-title">Data Peramalan</span>
        </a>
      </li>
      <li class="nav-item nav-category"><span class="nav-link"></span></li>
      {{-- Proses Peramalan --}}
      <li class="nav-item menu-items">
        <a class="nav-link" href="{{ url('/proses-peramalan') }}">
          <span class="menu-icon"><i class="mdi mdi-chart-bar"></i></span>
          <span class="menu-title">Proses Peramalan</span>
        </a>
      </li>
      <li class="nav-item nav-category"><span class="nav-link"></span></li>
      {{-- Pengaturan profil (hanya admin)--}}
      @if (Auth::user()->role == 'admin')
      <li class="nav-item menu-items">
        <a class="nav-link" href="{{ url('') }}">
          <span class="menu-icon"><i class="mdi mdi-account-group"></i></span>
          <span class="menu-title">Pengaturan User</span>
        </a>
      </li>
      @endif
    </ul>
</nav>
  