<?php
/**
 * Director Layout
 * MVC Pattern - Main layout template for director/admin pages
 * Theme: Indigo/Purple executive style
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check login and role
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'ผู้บริหาร') {
    header('Location: ../login.php');
    exit;
}

$config = json_decode(file_get_contents(__DIR__ . '/../../config.json'), true);
$global = $config['global'];
?>
<!DOCTYPE html>
<html lang="th" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $pageTitle ?? 'ผู้บริหาร'; ?> | <?php echo $global['nameschool']; ?></title>
    
    <!-- Google Font: Mali -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mali:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS v3 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        'mali': ['Mali', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        },
                        accent: {
                            50: '#faf5ff',
                            100: '#f3e8ff',
                            200: '#e9d5ff',
                            300: '#d8b4fe',
                            400: '#c084fc',
                            500: '#a855f7',
                            600: '#9333ea',
                            700: '#7e22ce',
                            800: '#6b21a8',
                            900: '#581c87',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'slide-in-left': 'slideInLeft 0.3s ease-out',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'bounce-slow': 'bounce 2s infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideInLeft: {
                            '0%': { opacity: '0', transform: 'translateX(-20px)' },
                            '100%': { opacity: '1', transform: 'translateX(0)' },
                        }
                    },
                }
            }
        }
    </script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <style>
        * { font-family: 'Mali', sans-serif; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: linear-gradient(135deg, #6366f1, #8b5cf6); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed); }
        
        /* Glass effect */
        .glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .dark .glass {
            background: rgba(30, 41, 59, 0.85);
        }

        /* Loading animation */
        .loader {
            width: 48px;
            height: 48px;
            border: 4px solid #e0e7ff;
            border-bottom-color: #6366f1;
            border-radius: 50%;
            animation: rotation 1s linear infinite;
        }
        @keyframes rotation { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        
        /* Page loading */
        #page-loader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #eef2ff 0%, #faf5ff 100%);
            transition: opacity 0.5s, visibility 0.5s;
        }
        #page-loader.hidden { opacity: 0; visibility: hidden; }
    </style>
</head>
<body class="font-mali bg-gradient-to-br from-slate-50 via-indigo-50/30 to-purple-50/30 dark:from-slate-900 dark:via-slate-900 dark:to-slate-800 min-h-screen">
    
    <!-- Page Loader -->
    <div id="page-loader">
        <div class="text-center">
            <img src="../dist/img/<?php echo $global['logoLink'] ?? 'logo-phicha.png'; ?>" alt="Logo" class="w-24 h-24 mx-auto animate-bounce-slow">
            <div class="loader mx-auto mt-6"></div>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300 animate-pulse"><?php echo $config['global']['pageTitle'] ?? 'ผู้บริหาร'; ?></p>
        </div>
    </div>
    
    <!-- Sidebar -->
    <?php include __DIR__ . '/../components/director_sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="lg:ml-64 min-h-screen">
        <!-- Top Navbar -->
        <header class="sticky top-0 z-30 glass border-b border-slate-200/50 dark:border-slate-700/50">
            <div class="px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <!-- Left: Mobile Menu + Title -->
                    <div class="flex items-center gap-4">
                        <button onclick="toggleSidebar()" class="lg:hidden p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div class="hidden sm:block">
                            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                                <span class="w-2 h-8 bg-gradient-to-b from-indigo-500 to-purple-500 rounded-full"></span>
                                <?php echo $config['global']['pageTitle'] ?? 'ผู้บริหาร'; ?>
                            </h1>
                        </div>
                    </div>
                    
                    <!-- Right: Date + User -->
                    <div class="flex items-center gap-4">
                        <div class="hidden md:flex items-center gap-2 px-4 py-2 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl">
                            <i class="fas fa-calendar-alt text-indigo-500"></i>
                            <span class="text-sm font-bold text-indigo-700 dark:text-indigo-300">
                                <?php 
                                $thaiMonths = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
                                echo date('j') . ' ' . $thaiMonths[date('n')] . ' ' . (date('Y') + 543);
                                ?>
                            </span>
                        </div>
                        
                        <div class="flex items-center gap-3 pl-4 border-l border-gray-200 dark:border-gray-700">
                            <div class="hidden sm:flex flex-col items-end">
                                <span class="text-xs font-black text-gray-800 dark:text-gray-200"><?php echo $_SESSION['user']['Teach_name'] ?? $_SESSION['username']; ?></span>
                                <span class="text-[9px] font-bold text-indigo-500 uppercase tracking-wider">ผู้บริหาร</span>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-black shadow-lg shadow-indigo-500/20">
                                <?php echo mb_substr($_SESSION['user']['Teach_name'] ?? $_SESSION['username'] ?? 'D', 0, 1, 'UTF-8'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="p-4 sm:p-6 lg:p-8">
            <?php echo $content ?? ''; ?>
        </main>
        
        <!-- Footer -->
        <footer class="mt-auto py-6 px-8 border-t border-slate-200/50 dark:border-slate-700/50">
            <div class="text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <?php echo $global['footerCredit'] ?? '© ' . (date('Y') + 543) . ' ระบบบริหารจัดการ'; ?>
                </p>
            </div>
        </footer>
    </div>
    
    <script>
        // Hide loader when page is ready
        window.addEventListener('load', function() {
            setTimeout(() => {
                document.getElementById('page-loader').classList.add('hidden');
            }, 300);
        });
        
        // Dark mode toggle
        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
        }
        
        // Check dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
</body>
</html>
