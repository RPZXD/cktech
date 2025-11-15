
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $global['pageTitle'].' | '. $global['nameschool']; ?></title>

    <!-- Google Font: Mali -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Mali:wght@200;300;400;500;600;700&display=swap">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- JQVMap -->
    <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
    <!-- summernote -->
    <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
    <link rel="icon" type="image/png" href="dist/img/logo-phicha.png" />
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- jQuery UI -->
    <script src="plugins/jquery-ui/jquery-ui.min.js"></script>
    <!-- jQuery UI CSS (optional, for default styling) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.3/css/jquery.dataTables.min.css">
    <!-- DataTables Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Tailwind CSS -->
     <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">



    <style>
    /* Theme tokens (WCAG-friendly choices) */
    :root {
        --bg: #ffffff;
        --text: #0f172a; /* slate-900 */
        --muted: #64748b; /* slate-500 */
        --primary: #4f46e5; /* indigo-600 */
        --primary-600: #4338ca;
        --accent: #06b6d4; /* cyan-500 */
        --sidebar-bg: #111827; /* gray-900 for contrast */
        --sidebar-text: #e6eef8;
        --focus-ring: rgba(79,70,229,0.24);
    }

    body.dark-mode {
        --bg: #071029; /* darker navy */
        --text: #e6eef8;
        --muted: #94a3b8;
        --primary: #8b5cf6; /* lighter indigo for dark */
        --primary-600: #7c3aed;
        --accent: #34d399;
        --sidebar-bg: #0b1220;
        --sidebar-text: #cbd5e1;
        --focus-ring: rgba(139,92,246,0.18);
    }

    body {
        font-family: 'Mali', 'Helvetica Neue', Helvetica, Arial, sans-serif;
        background-color: var(--bg);
        color: var(--text);
        transition: background-color 0.35s ease, color 0.35s ease;
    }

    .navbar-light.light-mode {
        background-color: #ffffff;
        color: #000000;
    }

    .navbar-dark.dark-mode {
        background-color: #121212;
        color: #ffffff;
    }

    .switch {
        display: flex;
        align-items: center;
        margin-left: auto;
    }

    .switch-label {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .switch-label input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e6e9ee;
        transition: 0.4s;
        border-radius: 34px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 5px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: 0.4s;
        border-radius: 50%;
    }

    .slider .icon-light,
    .slider .icon-dark {
        font-size: 16px;
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .slider .icon-light {
        color: #f59e0b;
    }

    .slider .icon-dark {
        color: var(--primary);
    }

    input:checked + .slider {
        background-color: var(--primary-600);
    }

    input:checked + .slider .icon-light {
        opacity: 0;
    }

    input:checked + .slider .icon-dark {
        opacity: 1;
    }

    input:not(:checked) + .slider .icon-light {
        opacity: 1;
    }

    input:not(:checked) + .slider .icon-dark {
        opacity: 0;
    }

    input:checked + .slider:before {
        transform: translateX(26px);
    }

    .preloader {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        width: 100%;
        position: fixed;
        top: 0;
        left: 0;
        background-color: var(--bg);
        z-index: 9999;
        transition: opacity 0.5s;
    }

    .preloader .animate-shake {
        animation: shake 1.5s infinite;
    }

    @keyframes shake {
        0% { transform: rotate(0deg); }
        25% { transform: rotate(-5deg); }
        50% { transform: rotate(0deg); }
        75% { transform: rotate(5deg); }
        100% { transform: rotate(0deg); }
    }

    .preloader p {
        margin-top: 20px;
        text-align: center;
    }

    @media (max-width: 576px) {
        .nav-item.d-none.d-sm-inline-block {
            display: none !important;
        }
    }

    @media (max-width: 768px) {
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            float: none;
            text-align: center;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            float: none;
            text-align: center;
        }

        table.dataTable thead {
            display: table-header-group;
        }

        table.dataTable tbody {
            display: table-row-group;
        }
    }

    /* Dark Mode Styles for Content */
    body.dark-mode section.content {
        background: linear-gradient(to bottom right, #1a1a1a, #2d2d2d, #4a4a4a) !important;
    }

    body.dark-mode section.content h2 {
        color: #ffffff !important;
    }

    body.dark-mode section.content p {
        color: #cccccc !important;
    }

    body.dark-mode section.content ul li {
        color: #cccccc !important;
    }

    body.dark-mode section.content ul li:hover {
        color: #ffffff !important;
    }

    body.dark-mode section.content .bg-white\/80 {
        background-color: rgba(31, 41, 55, 0.8) !important;
        border-color: #4a5568 !important;
    }

    body.dark-mode section.content .text-indigo-700 {
        color: #a78bfa !important;
    }

    body.dark-mode section.content .text-gray-600 {
        color: #cccccc !important;
    }

    body.dark-mode section.content .text-green-500,
    body.dark-mode section.content .text-blue-500,
    body.dark-mode section.content .text-purple-500,
    body.dark-mode section.content .text-orange-500,
    body.dark-mode section.content .text-pink-500,
    body.dark-mode section.content .text-teal-500 {
        filter: brightness(1.2);
    }

    /* Dark Mode for Sidebar */
    body.dark-mode .main-sidebar {
        background-color: #1a1a1a !important;
    }

    body.dark-mode .nav-link {
        color: #cccccc !important;
    }

    body.dark-mode .nav-link:hover {
        color: #ffffff !important;
        background-color: #374151 !important;
    }

    /* Sidebar nav styles and active indicator */
    .main-sidebar {
        background-color: var(--sidebar-bg) !important;
        color: var(--sidebar-text) !important;
    }

    .main-sidebar .nav-link {
        position: relative;
        transition: background-color 200ms ease, color 200ms ease, transform 150ms ease;
        border-radius: 0.5rem;
        padding-left: 0.75rem;
    }

    .main-sidebar .nav-link .bi {
        margin-right: 0.6rem;
        transition: transform 200ms ease, color 200ms ease;
    }

    .main-sidebar .nav-link:hover {
        transform: translateX(3px);
    }

    /* Left indicator bar */
    .main-sidebar .nav-link::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 0;
        border-radius: 4px;
        background: linear-gradient(180deg, #6ee7b7, #60a5fa);
        transition: height 220ms ease;
        opacity: 0;
    }

    .main-sidebar .nav-link:hover::before,
    .main-sidebar .nav-link.active::before {
        height: 60%;
        opacity: 1;
    }

    .main-sidebar .nav-link.active {
        background-color: rgba(255,255,255,0.06);
        color: #fff !important;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.03);
    }

    /* Dark mode adjustments for indicator */
    body.dark-mode .main-sidebar .nav-link::before {
        background: linear-gradient(180deg, #34d399, #7c3aed);
    }
</style>

</head>
