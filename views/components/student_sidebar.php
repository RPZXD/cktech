<?php 
$configPath = __DIR__ . '/../../config.json';
$config = file_exists($configPath) ? json_decode(file_get_contents($configPath), true) : ['global' => ['logoLink' => 'logo-phicha.png', 'nameTitle' => 'CLUB REGIS', 'nameschool' => 'School Name']];
$global = $config['global'];

// Menu configuration for Student
$menuItems = [
    [
        'key' => 'home',
        'name' => 'หน้าหลัก',
        'url' => 'index.php',
        'icon' => 'fa-home',
        'gradient' => ['from' => 'blue-500', 'to' => 'indigo-600'],
    ],
    [
        'key' => 'club_regis',
        'name' => 'สมัครชุมนุม',
        'url' => 'club_regis.php',
        'icon' => 'fa-users',
        'gradient' => ['from' => 'emerald-500', 'to' => 'teal-600'],
    ],
    [
        'key' => 'best_regis',
        'name' => 'สมัคร Best for Teen',
        'url' => 'best_regis.php',
        'icon' => 'fa-star',
        'gradient' => ['from' => 'amber-500', 'to' => 'orange-600'],
    ],
    [
        'key' => 'my_club',
        'name' => 'ชุมนุมของฉัน',
        'url' => 'my_club.php',
        'icon' => 'fa-heart',
        'gradient' => ['from' => 'rose-500', 'to' => 'pink-600'],
    ],
];
?>

<!-- Sidebar Overlay (Mobile) -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden hidden transition-opacity duration-300" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 z-40 w-72 sm:w-64 h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0">
    <div class="h-full overflow-y-auto bg-gradient-to-b from-emerald-900 via-emerald-950 to-black">
        
        <!-- Logo Section -->
        <div class="px-6 py-8 border-b border-white/5">
            <div class="flex items-center justify-between">
                <a href="index.php" class="flex items-center space-x-4 group flex-1">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full blur-lg opacity-40 group-hover:opacity-70 transition-opacity"></div>
                        <img src="../dist/img/<?php echo $global['logoLink'] ?? 'logo-phicha.png'; ?>" class="relative w-12 h-12 rounded-full ring-2 ring-white/10 group-hover:ring-emerald-400/50 transition-all" alt="Logo">
                    </div>
                    <div>
                        <span class="text-xl font-black text-white tracking-tight"><?php echo $global['nameTitle'] ?? 'นักเรียน'; ?></span>
                        <p class="text-[10px] font-bold text-emerald-400 tracking-[0.2em] uppercase">Student Portal</p>
                    </div>
                </a>
                <button onclick="toggleSidebar()" class="lg:hidden p-2 text-gray-400 hover:text-white rounded-xl hover:bg-white/5">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- User Info -->
        <?php if (isset($_SESSION['Student_login'])): ?>
        <div class="px-6 py-4 border-b border-white/5">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white font-black shadow-lg">
                    <?php echo mb_substr($_SESSION['username'] ?? 'S', 0, 1, 'UTF-8'); ?>
                </div>
                <div>
                    <p class="text-sm font-bold text-white"><?php echo $_SESSION['username'] ?? 'นักเรียน'; ?></p>
                    <p class="text-[10px] text-gray-400">รหัสนักเรียน: <?php echo $_SESSION['Student_login'] ?? ''; ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Navigation -->
        <nav class="mt-2 px-3 pb-24">
            <div class="mb-4 px-4">
                <p class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em]">เมนูนักเรียน</p>
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
                            <div class="ml-auto w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]"></div>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>
                
                <!-- System Divider -->
                <li class="my-6 px-4">
                    <div class="h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
                </li>
                
                <!-- Logout -->
                <li>
                    <a href="../logout.php" class="sidebar-item flex items-center px-4 py-3 text-gray-400 rounded-2xl hover:bg-rose-500/10 hover:text-rose-400 group">
                        <span class="w-10 h-10 flex items-center justify-center bg-gradient-to-br from-rose-500 to-red-600 rounded-xl shadow-lg shadow-rose-500/20 group-hover:shadow-rose-500/40 transition-shadow">
                            <i class="fas fa-sign-out-alt text-white"></i>
                        </span>
                        <span class="ml-4 font-bold text-sm tracking-tight">ออกจากระบบ</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- Bottom Credits -->
        <div class="absolute bottom-0 left-0 right-0 p-6 bg-gradient-to-t from-black to-transparent">
            <div class="text-center">
                <p class="text-[10px] font-black text-gray-600 uppercase tracking-widest"><?php echo $global['nameschool'] ?? 'โรงเรียนพิชัย'; ?></p>
                <p class="text-[8px] text-gray-700 mt-1 font-bold italic opacity-50 uppercase tracking-tighter">Student Portal v2.0</p>
            </div>
        </div>
    </div>
</aside>
