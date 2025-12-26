<?php 
/**
 * Director Sidebar Component
 * MVC Pattern - Sidebar navigation for director/admin pages
 * Theme: Indigo/Purple gradient (Executive feel)
 */

$configPath = __DIR__ . '/../../config.json';
$config = file_exists($configPath) ? json_decode(file_get_contents($configPath), true) : [];
$global = $config['global'] ?? ['logoLink' => 'logo-phicha.png', 'nameTitle' => 'Vichakan', 'nameschool' => 'โรงเรียน'];

// Get current user info
$userName = $_SESSION['user']['Teach_name'] ?? $_SESSION['username'] ?? 'ผู้บริหาร';
$userPhoto = $_SESSION['user']['Teach_photo'] ?? '';

// Menu configuration for Director
$menuItems = [
    [
        'key' => 'home',
        'name' => 'หน้าหลัก',
        'url' => 'index.php',
        'icon' => 'fa-home',
        'gradient' => ['from' => 'indigo-500', 'to' => 'purple-600'],
    ],
    [
        'key' => 'report',
        'name' => 'ตรวจสอบรายงานการสอน',
        'url' => 'report.php',
        'icon' => 'fa-file-alt',
        'gradient' => ['from' => 'blue-500', 'to' => 'indigo-600'],
    ],
    [
        'key' => 'supervision',
        'name' => 'การนิเทศการสอน',
        'url' => 'supervision.php',
        'icon' => 'fa-chalkboard-teacher',
        'gradient' => ['from' => 'purple-500', 'to' => 'violet-600'],
    ],
    [
        'key' => 'stat',
        'name' => 'สถิติและวิเคราะห์ข้อมูล',
        'url' => 'stat.php',
        'icon' => 'fa-chart-bar',
        'gradient' => ['from' => 'emerald-500', 'to' => 'teal-600'],
    ],
    [
        'key' => 'weekly',
        'name' => 'รายงานรายสัปดาห์',
        'url' => 'weekly_report.php',
        'icon' => 'fa-calendar-week',
        'gradient' => ['from' => 'amber-500', 'to' => 'orange-600'],
    ],
];
?>

<!-- Sidebar Overlay (Mobile) -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden hidden transition-opacity duration-300" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 z-40 w-72 sm:w-64 h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0">
    <div class="h-full overflow-y-auto bg-gradient-to-b from-indigo-800 via-purple-900 to-slate-900">
        
        <!-- Logo Section -->
        <div class="px-6 py-8 border-b border-white/5">
            <div class="flex items-center justify-between">
                <a href="index.php" class="flex items-center space-x-4 group flex-1">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-400 to-purple-500 rounded-full blur-lg opacity-40 group-hover:opacity-70 transition-opacity"></div>
                        <img src="../dist/img/<?php echo $global['logoLink'] ?? 'logo-phicha.png'; ?>" class="relative w-12 h-12 rounded-full ring-2 ring-white/10 group-hover:ring-indigo-400/50 transition-all" alt="Logo">
                    </div>
                    <div>
                        <span class="text-xl font-black text-white tracking-tight uppercase"><?php echo $global['nameTitle'] ?? 'ระบบ'; ?></span>
                        <p class="text-[10px] font-bold text-indigo-300 tracking-[0.2em] uppercase">Director Portal</p>
                    </div>
                </a>
                <button onclick="toggleSidebar()" class="lg:hidden p-2 text-gray-400 hover:text-white rounded-xl hover:bg-white/5">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- User Info -->
        <div class="px-6 py-4 border-b border-white/5">
            <div class="flex items-center space-x-3">
                <?php if (!empty($userPhoto)): ?>
                <img src="https://std.phichai.ac.th/teacher/uploads/phototeach/<?php echo htmlspecialchars($userPhoto); ?>" class="w-10 h-10 rounded-full object-cover ring-2 ring-indigo-400/50" alt="Profile">
                <?php else: ?>
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center">
                    <i class="fas fa-user-tie text-white"></i>
                </div>
                <?php endif; ?>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate"><?php echo htmlspecialchars($userName); ?></p>
                    <p class="text-xs text-indigo-300">ผู้บริหาร</p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="px-4 py-6">
            <p class="px-4 text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] mb-4">เมนูหลัก</p>
            
            <ul class="space-y-2">
                <?php foreach ($menuItems as $item): ?>
                <li>
                    <a href="<?php echo $item['url']; ?>" 
                       class="sidebar-item flex items-center px-4 py-3 text-gray-400 rounded-2xl hover:bg-white/5 hover:text-white group transition-all duration-200">
                        <span class="w-10 h-10 flex items-center justify-center bg-gradient-to-br from-<?php echo $item['gradient']['from']; ?> to-<?php echo $item['gradient']['to']; ?> rounded-xl shadow-lg shadow-<?php echo $item['gradient']['from']; ?>/20">
                            <i class="fas <?php echo $item['icon']; ?> text-white"></i>
                        </span>
                        <span class="ml-4 font-bold text-sm tracking-tight"><?php echo $item['name']; ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
                
                <!-- Logout -->
                <li class="pt-4 border-t border-white/5 mt-4">
                    <a href="../logout.php" class="sidebar-item flex items-center px-4 py-3 text-gray-400 rounded-2xl hover:bg-rose-500/10 hover:text-rose-400 group transition-all duration-200">
                        <span class="w-10 h-10 flex items-center justify-center bg-gradient-to-br from-rose-500 to-red-600 rounded-xl shadow-lg shadow-rose-500/20">
                            <i class="fas fa-sign-out-alt text-white"></i>
                        </span>
                        <span class="ml-4 font-bold text-sm tracking-tight">ออกจากระบบ</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Footer -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/5">
            <div class="text-center">
                <p class="text-[10px] font-bold text-gray-600 uppercase tracking-wider"><?php echo $global['nameschool'] ?? 'โรงเรียน'; ?></p>
                <p class="text-[9px] text-gray-700 mt-1">© <?php echo date('Y') + 543; ?></p>
            </div>
        </div>
    </div>
</aside>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
    document.body.classList.toggle('overflow-hidden');
}
</script>
