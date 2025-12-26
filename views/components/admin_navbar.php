<?php
/**
 * Admin Navbar Component
 * MVC Pattern - Top navigation bar for admin pages
 */

$userName = $_SESSION['user']['Teach_name'] ?? $_SESSION['username'] ?? 'ผู้ดูแลระบบ';
$userRole = 'ผู้ดูแลระบบ';
?>

<!-- Top Navbar -->
<header class="sticky top-0 z-30 glass border-b border-purple-200/50 dark:border-purple-800/50 min-h-[64px] flex items-center">
    <div class="w-full flex items-center justify-between px-4 md:px-6 py-2">
        <!-- Left: Mobile Toggle & Title -->
        <div class="flex items-center gap-3">
            <button onclick="toggleSidebar()" class="lg:hidden p-2.5 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20 active:scale-95 transition-all">
                <i class="fas fa-bars"></i>
            </button>
            <div class="flex flex-col">
                <div class="flex items-center gap-2">
                    <span class="hidden sm:inline px-2 py-0.5 bg-purple-500 text-white text-[10px] font-bold rounded-full uppercase tracking-wider">Admin</span>
                    <h1 class="text-sm sm:text-lg font-bold text-gray-800 dark:text-white truncate max-w-[120px] xs:max-w-[180px] sm:max-w-none"><?php echo $pageTitle ?? 'ระบบผู้ดูแล'; ?></h1>
                </div>
                <p class="hidden sm:block text-[10px] md:text-xs text-gray-500 dark:text-gray-400">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    <?php 
                    $thaiMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
                    echo date('j') . ' ' . $thaiMonths[(int)date('n')] . ' ' . (date('Y') + 543);
                    ?>
                </p>
            </div>
        </div>
        
        <!-- Right: User Menu & Actions -->
        <div class="flex items-center space-x-2 md:space-x-3">
            <!-- System Status Badge -->
            <div class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-emerald-100 dark:bg-emerald-900/30 rounded-full">
                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">ระบบปกติ</span>
            </div>
            
            <!-- Dark Mode Toggle -->
            <button onclick="toggleDarkMode()" class="p-2 rounded-xl bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 transition-colors">
                <i class="fas fa-sun text-amber-500 dark:hidden"></i>
                <i class="fas fa-moon text-indigo-400 hidden dark:inline"></i>
            </button>
            
            <!-- User Menu -->
            <div class="flex items-center space-x-2 px-2 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/30 dark:to-pink-900/30 border border-purple-200 dark:border-purple-800/50">
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center shadow-md admin-badge">
                    <i class="fas fa-user-shield text-white text-xs sm:text-sm"></i>
                </div>
                <div class="hidden xs:block">
                    <p class="text-[10px] sm:text-sm font-bold text-gray-800 dark:text-white truncate max-w-[80px] sm:max-w-none"><?php echo htmlspecialchars($userName); ?></p>
                    <p class="text-[8px] sm:text-xs text-purple-600 dark:text-purple-400 font-bold tracking-tight">PROTECTED</p>
                </div>
            </div>
        </div>
    </div>
</header>
