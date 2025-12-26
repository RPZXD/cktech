<?php
/**
 * Admin Dashboard View
 * MVC Pattern - View for admin index page
 * Enhanced UI/UX with Tailwind CSS - Mobile Responsive
 */
?>

<style>
    /* Floating Animation */
    .float-animation { animation: floating 3s ease-in-out infinite; }
    @keyframes floating { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
    
    /* Shimmer Effect */
    .shimmer-bg {
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        background-size: 200% 100%;
        animation: shimmer 2s linear infinite;
    }
    @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
    
    /* Pulse Glow */
    .pulse-glow { animation: pulseGlow 2s ease-in-out infinite; }
    @keyframes pulseGlow { 0%, 100% { box-shadow: 0 0 0 0 rgba(168, 85, 247, 0.7); } 50% { box-shadow: 0 0 0 15px rgba(168, 85, 247, 0); } }
</style>

<!-- Welcome Section -->
<div class="mb-6 md:mb-8">
    <div class="relative glass rounded-2xl md:rounded-3xl p-5 md:p-8 shadow-xl overflow-hidden">
        <!-- Background Decoration -->
        <div class="absolute -top-20 -right-20 w-40 h-40 md:w-60 md:h-60 bg-gradient-to-br from-purple-400/30 to-pink-400/30 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 w-40 h-40 md:w-60 md:h-60 bg-gradient-to-br from-orange-400/20 to-amber-400/20 rounded-full blur-3xl"></div>
        
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-3xl md:text-4xl float-animation">🛡️</span>
                    <span class="px-3 py-1 bg-purple-500 text-white text-xs font-bold rounded-full uppercase tracking-wider pulse-glow">Admin</span>
                </div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold gradient-text-admin">
                    ยินดีต้อนรับ, <?php echo htmlspecialchars($user['name'] ?? 'ผู้ดูแลระบบ'); ?>
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400 text-sm md:text-base">
                    คุณเข้าสู่ระบบในฐานะ <span class="font-semibold text-purple-600 dark:text-purple-400">ผู้ดูแลระบบ</span>
                    <br class="sm:hidden">
                    <span class="text-gray-400">•</span> จัดการข้อมูลและตั้งค่าระบบได้ที่นี่
                </p>
            </div>
            <div class="mt-4 md:mt-0 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <a href="teacher.php" class="inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl text-white shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5 text-sm md:text-base font-medium">
                    <i class="fas fa-users-cog mr-2"></i>
                    <span>จัดการผู้ใช้</span>
                </a>
                <a href="settings.php" class="inline-flex items-center justify-center px-5 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-200 shadow hover:shadow-lg transition-all hover:-translate-y-0.5 text-sm md:text-base font-medium">
                    <i class="fas fa-cog mr-2"></i>
                    <span>ตั้งค่า</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 lg:gap-6 mb-6 md:mb-8">
    <!-- Total Teachers -->
    <div class="card-hover glass rounded-xl md:rounded-2xl p-4 md:p-5 group relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-indigo-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400">ครูทั้งหมด</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($stats['total_teachers'] ?? 0); ?>
                </h3>
                <p class="text-[10px] md:text-xs text-gray-400 mt-1">คน</p>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-chalkboard-teacher text-white text-base md:text-lg"></i>
            </div>
        </div>
    </div>
    
    <!-- Total Reports -->
    <div class="card-hover glass rounded-xl md:rounded-2xl p-4 md:p-5 group relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-green-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400">รายงานทั้งหมด</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($stats['total_reports'] ?? 0); ?>
                </h3>
                <p class="text-[10px] md:text-xs text-gray-400 mt-1">รายการ</p>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-file-alt text-white text-base md:text-lg"></i>
            </div>
        </div>
    </div>
    
    <!-- Total Subjects -->
    <div class="card-hover glass rounded-xl md:rounded-2xl p-4 md:p-5 group relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-violet-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400">รายวิชาทั้งหมด</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($stats['total_subjects'] ?? 0); ?>
                </h3>
                <p class="text-[10px] md:text-xs text-gray-400 mt-1">วิชา</p>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl shadow-lg shadow-purple-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-book text-white text-base md:text-lg"></i>
            </div>
        </div>
    </div>
    
    <!-- Total Students -->
    <div class="card-hover glass rounded-xl md:rounded-2xl p-4 md:p-5 group relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-orange-500/10 to-amber-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400">นักเรียนทั้งหมด</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($stats['total_students'] ?? 0); ?>
                </h3>
                <p class="text-[10px] md:text-xs text-gray-400 mt-1">คน</p>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl shadow-lg shadow-orange-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-users text-white text-base md:text-lg"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Access Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-8">
    <!-- User Management -->
    <a href="teacher.php" class="group glass rounded-2xl p-5 md:p-6 shadow-lg hover:shadow-xl transition-all hover:-translate-y-1 border border-purple-100 dark:border-purple-900/50">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                <i class="fas fa-users-cog text-white text-xl md:text-2xl"></i>
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-gray-900 dark:text-white text-base md:text-lg group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">จัดการผู้ใช้</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">เพิ่ม แก้ไข ลบ ผู้ใช้ในระบบ</p>
                <div class="flex items-center gap-2 mt-3">
                    <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-2 py-1 rounded-full">ครู</span>
                    <span class="text-xs bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 px-2 py-1 rounded-full">หัวหน้ากลุ่ม</span>
                    <span class="text-xs bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 px-2 py-1 rounded-full">ผู้บริหาร</span>
                </div>
            </div>
            <i class="fas fa-chevron-right text-gray-300 dark:text-gray-600 group-hover:text-purple-500 group-hover:translate-x-1 transition-all"></i>
        </div>
    </a>
    
    <!-- Reports -->
    <a href="report.php" class="group glass rounded-2xl p-5 md:p-6 shadow-lg hover:shadow-xl transition-all hover:-translate-y-1 border border-emerald-100 dark:border-emerald-900/50">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                <i class="fas fa-clipboard-list text-white text-xl md:text-2xl"></i>
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-gray-900 dark:text-white text-base md:text-lg group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">รายงานการสอน</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">ดูรายงานการสอนทั้งหมด</p>
                <div class="flex items-center gap-2 mt-3">
                    <span class="text-xs bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 px-2 py-1 rounded-full">📊 ดูสถิติ</span>
                    <span class="text-xs bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 px-2 py-1 rounded-full">📋 รายงาน</span>
                </div>
            </div>
            <i class="fas fa-chevron-right text-gray-300 dark:text-gray-600 group-hover:text-emerald-500 group-hover:translate-x-1 transition-all"></i>
        </div>
    </a>
    
    <!-- Settings -->
    <a href="settings.php" class="group glass rounded-2xl p-5 md:p-6 shadow-lg hover:shadow-xl transition-all hover:-translate-y-1 border border-pink-100 dark:border-pink-900/50">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                <i class="fas fa-cog text-white text-xl md:text-2xl"></i>
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-gray-900 dark:text-white text-base md:text-lg group-hover:text-pink-600 dark:group-hover:text-pink-400 transition-colors">ตั้งค่าระบบ</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">ปรับแต่งการทำงานของระบบ</p>
                <div class="flex items-center gap-2 mt-3">
                    <span class="text-xs bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 px-2 py-1 rounded-full">⚙️ ทั่วไป</span>
                    <span class="text-xs bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 px-2 py-1 rounded-full">🔐 ความปลอดภัย</span>
                </div>
            </div>
            <i class="fas fa-chevron-right text-gray-300 dark:text-gray-600 group-hover:text-pink-500 group-hover:translate-x-1 transition-all"></i>
        </div>
    </a>
</div>

<!-- Admin Features Info -->
<div class="glass rounded-2xl md:rounded-3xl p-5 md:p-8 shadow-xl">
    <div class="flex items-center mb-6">
        <div class="w-1 h-8 bg-gradient-to-b from-purple-500 to-pink-600 rounded-full mr-4"></div>
        <h2 class="text-lg md:text-xl font-bold text-gray-800 dark:text-white">🛡️ ความสามารถของผู้ดูแลระบบ</h2>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-100 dark:border-blue-800/50">
            <div class="text-2xl mb-2">👤</div>
            <h4 class="font-semibold text-blue-700 dark:text-blue-300">จัดการผู้ใช้</h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">เพิ่ม แก้ไข ลบ ผู้ใช้ทุกระดับ</p>
        </div>
        
        <div class="p-4 rounded-xl bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border border-emerald-100 dark:border-emerald-800/50">
            <div class="text-2xl mb-2">📑</div>
            <h4 class="font-semibold text-emerald-700 dark:text-emerald-300">ดูรายงาน</h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">ดูรายงานและสถิติทั้งหมด</p>
        </div>
        
        <div class="p-4 rounded-xl bg-gradient-to-br from-purple-50 to-violet-50 dark:from-purple-900/20 dark:to-violet-900/20 border border-purple-100 dark:border-purple-800/50">
            <div class="text-2xl mb-2">📊</div>
            <h4 class="font-semibold text-purple-700 dark:text-purple-300">วิเคราะห์ข้อมูล</h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">ดูข้อมูลเชิงลึกและแนวโน้ม</p>
        </div>
        
        <div class="p-4 rounded-xl bg-gradient-to-br from-pink-50 to-rose-50 dark:from-pink-900/20 dark:to-rose-900/20 border border-pink-100 dark:border-pink-800/50">
            <div class="text-2xl mb-2">⚙️</div>
            <h4 class="font-semibold text-pink-700 dark:text-pink-300">ตั้งค่าระบบ</h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">ปรับแต่งการตั้งค่าต่างๆ</p>
        </div>
    </div>
</div>
