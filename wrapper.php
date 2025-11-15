<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex flex-col justify-center items-center h-screen w-full fixed top-0 left-0 bg-gray-100 z-50" role="status" aria-live="polite" aria-label="Loading">
      <img class="animate-shake h-36 w-36" src="dist/img/logo-phicha.png" alt="AdminLTE Logo">
      <h3 class="mt-4 text-center"><?php echo $global['pageTitle']; ?></h3>
  </div>

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand bg-white">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link hover:bg-gray-200 hover:text-gray-800 transition-all duration-300 transform hover:scale-105" data-widget="pushmenu" href="#" role="button" aria-controls="main-sidebar" aria-expanded="true" aria-label="Toggle sidebar">
          <i class="bi bi-list"></i>
        </a>
      </li>
      <li class="nav-item hidden sm:inline-block">
        <a href="index.php" class="nav-link hover:bg-gray-200 hover:text-gray-800 transition-all duration-300 transform hover:scale-105">
          <i class="bi bi-house-door"></i> หน้าหลัก
        </a>
      </li>
    </ul>

    <div class="switch ml-auto">
      <label class="switch-label" for="theme-toggle">
        <input type="checkbox" id="theme-toggle" aria-label="Toggle color theme" role="switch" aria-checked="false">
        <span class="slider flex items-center justify-between px-1">
          <i class="bi bi-brightness-high icon-light" aria-hidden="true"></i>
          <i class="bi bi-moon icon-dark" aria-hidden="true"></i>
        </span>
      </label>
    </div>
  </nav>
  <!-- /.navbar -->

    <!-- Main Sidebar Container -->
  <aside id="main-sidebar" class="main-sidebar text-white" role="navigation" aria-label="Main Sidebar">
    <!-- Brand Logo -->
    <a href="index.php" class="brand-link flex items-center">
      <img src="dist/img/logo-phicha.png" alt="AdminLTE Logo" class="brand-image rounded-full opacity-80">
      <span class="brand-text font-light ml-2"><?php echo $global['nameTitle']; ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      
      <!-- SidebarSearch Form -->
      <div class="form-inline mt-4">
        <br><br>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <?php require_once('leftmenu.php');?>        
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>