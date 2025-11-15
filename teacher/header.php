
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
        /* color: #fff; */
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
