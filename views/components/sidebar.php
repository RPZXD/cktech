<?php 
$configPath = __DIR__ . '/../../config.json';
$config = file_exists($configPath) ? json_decode(file_get_contents($configPath), true) : ['global' => ['logoLink' => 'logo-phicha.png', 'nameTitle' => 'Vichakan', 'nameschool' => 'โรงเรียนพิชัย']];
$global = $config['global'];

// Menu configuration for Academic (Vichakan) System
// URLs are at root level for public statistics/dashboard pages
$menuItems = [
    [
        'key' => 'home',
        'name' => 'หน้าหลัก',
        'url' => 'index.php',
        'icon' => 'fa-home',
        'gradient' => ['from' => 'blue-500', 'to' => 'indigo-600'],
    ],
    [
        'key' => 'teaching_report',
        'name' => 'สถิติรายงานการสอน',
        'url' => 'teaching-stats.php',
        'icon' => 'fa-chart-line',
        'gradient' => ['from' => 'green-500', 'to' => 'emerald-600'],
    ],
    [
        'key' => 'supervision',
        'name' => 'สถิตินิเทศการสอน',
        'url' => 'supervision-stats.php',
        'icon' => 'fa-clipboard-check',
        'gradient' => ['from' => 'purple-500', 'to' => 'violet-600'],
    ],
    [
        'key' => 'certificate',
        'name' => 'สถิติใบประกาศ',
        'url' => 'certificate-stats.php',
        'icon' => 'fa-certificate',
        'gradient' => ['from' => 'orange-500', 'to' => 'amber-600'],
    ],
    [
        'key' => 'attendance',
        'name' => 'สถิติการเช็คชื่อ',
        'url' => 'attendance-stats.php',
        'icon' => 'fa-user-check',
        'gradient' => ['from' => 'pink-500', 'to' => 'rose-600'],
    ],
];
?>

<!-- Sidebar Overlay (Mobile) -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden hidden transition-opacity duration-300" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 z-40 w-72 sm:w-64 h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0">
    <div class="h-full overflow-y-auto bg-gradient-to-b from-slate-900 via-slate-950 to-black">
        
        <!-- Logo Section -->
        <div class="px-6 py-8 border-b border-white/5">
            <div class="flex items-center justify-between">
                <a href="index.php" class="flex items-center space-x-4 group flex-1">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-primary-500 to-accent-500 rounded-full blur-lg opacity-40 group-hover:opacity-70 transition-opacity"></div>
                        <img src="dist/img/<?php echo $global['logoLink'] ?? 'logo-phicha.png'; ?>" class="relative w-12 h-12 rounded-full ring-2 ring-white/10 group-hover:ring-primary-400/50 transition-all" alt="Logo">
                    </div>
                    <div>
                        <span class="text-xl font-black text-white tracking-tight uppercase"><?php echo $global['nameTitle'] ?? 'Vichakan'; ?></span>
                        <p class="text-[10px] font-bold text-primary-400 tracking-[0.2em] uppercase">Academic Portal</p>
                    </div>
                </a>
                <button onclick="toggleSidebar()" class="lg:hidden p-2 text-gray-400 hover:text-white rounded-xl hover:bg-white/5">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="mt-2 px-3 pb-24">
            <div class="mb-4 px-4">
                <p class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em]">เมนูหลัก</p>
            </div>
            <ul class="space-y-1.5">
                <?php foreach ($menuItems as $menu): 
                    $fromColor = $menu['gradient']['from'];
                    $toColor = $menu['gradient']['to'];
                    $isActive = basename($_SERVER['PHP_SELF']) == basename($menu['url']);
                    $colorName = explode('-', $fromColor)[0];
                ?>
                <li>
                    <a href="<?php echo htmlspecialchars($menu['url']); ?>" 
                       class="sidebar-item flex items-center px-4 py-3 rounded-2xl transition-all group active:scale-[0.98] <?php echo $isActive ? 'bg-white/10 text-white border border-white/5 shadow-xl shadow-black/20' : 'text-gray-400 hover:bg-white/5 hover:text-white'; ?>">
                        <span class="w-10 h-10 flex items-center justify-center bg-gradient-to-br from-<?php echo $fromColor; ?> to-<?php echo $toColor; ?> rounded-xl shadow-lg shadow-<?php echo $colorName; ?>-500/20 group-hover:shadow-<?php echo $colorName; ?>-500/40 transition-shadow">
                            <i class="fas <?php echo $menu['icon']; ?> text-white text-base"></i>
                        </span>
                        <span class="ml-4 font-bold text-sm tracking-tight"><?php echo htmlspecialchars($menu['name']); ?></span>
                        <?php if($isActive): ?>
                            <div class="ml-auto w-1.5 h-1.5 rounded-full bg-primary-500 shadow-[0_0_8px_rgba(139,92,246,0.6)]"></div>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>
                
                <!-- System Divider -->
                <li class="my-6 px-4">
                    <div class="h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
                </li>
                
                <!-- Login/Logout -->
                <?php if (isset($_SESSION['Teacher_login']) || isset($_SESSION['Student_login']) || isset($_SESSION['Officer_login']) || isset($_SESSION['Admin_login'])): ?>
                <li>
                    <a href="logout.php" class="sidebar-item flex items-center px-4 py-3 text-gray-400 rounded-2xl hover:bg-rose-500/10 hover:text-rose-400 group">
                        <span class="w-10 h-10 flex items-center justify-center bg-gradient-to-br from-rose-500 to-red-600 rounded-xl shadow-lg shadow-rose-500/20 group-hover:shadow-rose-500/40 transition-shadow">
                            <i class="fas fa-sign-out-alt text-white"></i>
                        </span>
                        <span class="ml-4 font-bold text-sm tracking-tight">ออกจากระบบ</span>
                    </a>
                </li>
                <?php else: ?>
                <li>
                    <a href="login.php" class="sidebar-item flex items-center px-4 py-3 text-gray-400 rounded-2xl hover:bg-white/5 hover:text-white group">
                        <span class="w-10 h-10 flex items-center justify-center bg-gradient-to-br from-primary-500 to-accent-500 rounded-xl shadow-lg shadow-primary-500/20 group-hover:shadow-primary-500/40 transition-shadow">
                            <i class="fas fa-sign-in-alt text-white"></i>
                        </span>
                        <span class="ml-4 font-bold text-sm tracking-tight">เข้าสู่ระบบ</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <!-- Bottom Credits -->
        <div class="absolute bottom-0 left-0 right-0 p-6 bg-gradient-to-t from-black to-transparent">
            <div class="text-center">
                <p class="text-[10px] font-black text-gray-600 uppercase tracking-widest"><?php echo $global['nameschool'] ?? 'โรงเรียนพิชัย'; ?></p>
                <p class="text-[8px] text-gray-700 mt-1 font-bold italic opacity-50 uppercase tracking-tighter">Vichakan System v2.0</p>
            </div>
        </div>
    </div>
</aside>
