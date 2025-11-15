
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $global['pageTitle'].' | '. $global['nameschool']; ?></title>

    <!-- Google Font: Mali -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Mali:wght@200;300;400;500;600;700&display=swap">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet" href="../plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- JQVMap -->
    <link rel="stylesheet" href="../plugins/jqvmap/jqvmap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="../plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="../plugins/daterangepicker/daterangepicker.css">
    <!-- summernote -->
    <link rel="stylesheet" href="../plugins/summernote/summernote-bs4.min.css">
    <link rel="icon" type="image/png" href="../dist/img/logo-phicha.png" />
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- jQuery UI -->
    <script src="../plugins/jquery-ui/jquery-ui.min.js"></script>
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
        background-color: #ccc;
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
        color: #fbc02d;
    }

    .slider .icon-dark {
        color: #2196F3;
    }

    input:checked + .slider {
        background-color: #2196F3;
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
        background-color: #f4f6f9;
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

    body.dark-mode section.content .bg-white\/90 {
        background-color: rgba(31, 41, 55, 0.9) !important;
        border-color: #4a5568 !important;
    }

    body.dark-mode section.content .text-indigo-700 {
        color: #a78bfa !important;
    }

    body.dark-mode section.content .text-gray-700 {
        color: #cccccc !important;
    }

    body.dark-mode section.content .text-gray-500 {
        color: #94a3b8 !important;
    }

    body.dark-mode section.content .bg-gradient-to-r.from-blue-50.to-blue-100 {
        background: linear-gradient(to right, #1e3a8a, #1e40af) !important;
    }

    body.dark-mode section.content .bg-gradient-to-r.from-green-50.to-green-100 {
        background: linear-gradient(to right, #14532d, #166534) !important;
    }

    body.dark-mode section.content .bg-gradient-to-r.from-purple-50.to-purple-100 {
        background: linear-gradient(to right, #581c87, #6b21a8) !important;
    }

    body.dark-mode section.content .bg-gradient-to-r.from-pink-50.to-pink-100 {
        background: linear-gradient(to right, #831843, #9d174d) !important;
    }

    body.dark-mode section.content .bg-gradient-to-r.from-indigo-100.to-purple-100 {
        background: linear-gradient(to right, #312e81, #581c87) !important;
    }

    /* Utility overrides for Tailwind/AdminLTE elements in dark mode
       These make common utility classes (bg-white, bg-gray-50, text-gray-900, borders, tables, modals)
       readable when `body` has the `dark-mode` class. Use only targeted overrides to avoid
       interfering with other pages. */
    body.dark-mode .bg-white {
        background-color: #071029 !important;
        color: var(--text) !important;
    }

    body.dark-mode .bg-gray-50 {
        background-color: #0b1220 !important;
        color: var(--text) !important;
    }

    body.dark-mode .text-gray-900,
    body.dark-mode .text-gray-800 {
        color: var(--text) !important;
    }

    body.dark-mode .text-gray-700,
    body.dark-mode .text-gray-600,
    body.dark-mode .text-gray-500 {
        color: var(--muted) !important;
    }

    body.dark-mode .border-gray-200,
    body.dark-mode .border-gray-300,
    body.dark-mode .border-gray-500 {
        border-color: #374151 !important;
    }

    /* Tables and DataTables */
    body.dark-mode table,
    body.dark-mode table.dataTable,
    body.dark-mode .dataTables_wrapper {
        background-color: transparent !important;
        color: var(--text) !important;
    }
    body.dark-mode table thead th,
    body.dark-mode table thead td,
    body.dark-mode table tbody td,
    body.dark-mode table tbody th {
        border-color: #374151 !important;
    }

    /* Modals / overlays */
    body.dark-mode .modal,
    body.dark-mode .modal-content,
    body.dark-mode .shadow-inner,
    body.dark-mode .rounded-xl {
        background-color: #0b1220 !important;
        color: var(--text) !important;
        border-color: #374151 !important;
    }

    /* Buttons with light backgrounds — reduce contrast in dark mode */
    body.dark-mode .bg-blue-100,
    body.dark-mode .bg-red-50,
    body.dark-mode .bg-indigo-100,
    body.dark-mode .bg-purple-100,
    body.dark-mode .bg-yellow-50 {
        background-color: rgba(255,255,255,0.04) !important;
    }

    /* DataTables paging and controls coloring */
    body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button,
    body.dark-mode .dataTables_wrapper .dataTables_info,
    body.dark-mode .dataTables_wrapper .dataTables_length,
    body.dark-mode .dataTables_wrapper .dataTables_filter {
        color: var(--muted) !important;
    }

    /* Dark Mode for Sidebar */
    body.dark-mode .main-sidebar {
        background-color: var(--sidebar-bg) !important;
    }

    body.dark-mode .nav-link {
        color: var(--sidebar-text) !important;
    }

    body.dark-mode .nav-link:hover {
        color: #ffffff !important;
        background-color: #374151 !important;
    }

    /* Sidebar nav styles and active indicator */
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
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.03);
    }

    /* Dark mode adjustments for indicator */
    body.dark-mode .main-sidebar .nav-link::before {
        background: linear-gradient(180deg, #34d399, #7c3aed);
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

    /* Small tooltip utility */
    .tt {
        position: relative;
    }
    .tt[data-title]:hover::after {
        content: attr(data-title);
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%);
        background: rgba(2,6,23,0.85);
        color: #fff;
        padding: 6px 8px;
        border-radius: 6px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 60;
        box-shadow: 0 6px 18px rgba(2,6,23,0.6);
        opacity: 1;
    }

    /* Sticky thead for horizontally scrollable table */
    .overflow-x-auto thead th {
        position: sticky;
        top: 0;
        z-index: 30;
        backdrop-filter: blur(6px);
    }

    /* subtle card hover lift used across pages */
    .card-lift { transition: transform .28s cubic-bezier(.2,.8,.2,1), box-shadow .28s; }
    .card-lift:hover { transform: translateY(-6px); box-shadow: 0 18px 40px rgba(2,6,23,0.12); }

    /* DataTables & table polish */
    table.dataTable thead th {
        background-color: rgba(255,255,255,0.95);
        backdrop-filter: blur(6px);
    }
    body.dark-mode table.dataTable thead th {
        background-color: rgba(6,8,15,0.55) !important;
        color: var(--text) !important;
        border-bottom: 1px solid rgba(255,255,255,0.04) !important;
    }
    /* zebra striping */
    table.dataTable.stripe tbody tr:nth-child(odd) {
        background: rgba(0,0,0,0.02);
    }
    body.dark-mode table.dataTable.stripe tbody tr:nth-child(odd) {
        background: rgba(255,255,255,0.02) !important;
    }

    /* Modal transitions and scrollbars */
    .modal-fade {
        opacity: 0;
        transform: translateY(-6px) scale(.995);
        transition: opacity .22s ease, transform .22s ease;
    }
    .modal-fade.show {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    .modal-scroll {
        max-height: calc(100vh - 160px);
        overflow: auto;
        padding-right: 8px;
    }
    /* custom scrollbar for modal content */
    .modal-scroll::-webkit-scrollbar { width: 10px; }
    .modal-scroll::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius: 6px; }
    body.dark-mode .modal-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); }

    /* Focus ring for actionable controls */
    .focus-ring:focus {
        outline: none;
        box-shadow: 0 0 0 4px var(--focus-ring);
        border-radius: 6px;
    }
</style>

</head>
