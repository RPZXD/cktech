<?php
/**
 * Home View
 * MVC Pattern - View for displaying home page content
 * Variables available: $statistics, $quickLinks, $recentActivities, $currentUser, $global, $welcomeMessage
 */
?>

<!-- Welcome Section -->
<div class="mb-8">
    <div class="glass rounded-3xl p-6 md:p-8 shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex-1">
                <h1 class="text-2xl md:text-3xl font-bold gradient-text">
                    <?php echo $welcomeMessage ?? 'ยินดีต้อนรับ'; ?>
                    <?php if ($currentUser): ?>
                        <span class="text-gray-700 dark:text-gray-300">, <?php echo htmlspecialchars($currentUser['name']); ?></span>
                    <?php endif; ?>
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    <?php if ($currentUser): ?>
                        คุณเข้าสู่ระบบในฐานะ <span class="font-semibold text-primary-600 dark:text-primary-400"><?php echo htmlspecialchars($currentUser['role']); ?></span>
                    <?php else: ?>
                        ยินดีต้อนรับสู่ระบบวิชาการ <?php echo htmlspecialchars($global['nameschool'] ?? 'โรงเรียน'); ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="mt-4 md:mt-0">
                <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-500 to-accent-500 rounded-2xl text-white shadow-lg">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <span class="font-medium"><?php echo date('d/m/') . (date('Y') + 543); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
    <!-- Reports Card -->
    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] md:text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">รายงานทั้งหมด</p>
                <h3 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($statistics['total_reports'] ?? 0); ?>
                </h3>
                <p class="text-[10px] text-blue-500 font-bold mt-1">รายการ</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-file-alt text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Active Teachers Card -->
    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] md:text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">ครูผู้สอน</p>
                <h3 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($statistics['total_teachers'] ?? 0); ?>
                </h3>
                <p class="text-[10px] text-emerald-500 font-bold mt-1">คน</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg shadow-green-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-chalkboard-teacher text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Supervisions Card -->
    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] md:text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">การนิเทศสอน</p>
                <h3 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($statistics['total_supervisions'] ?? 0); ?>
                </h3>
                <p class="text-[10px] text-purple-500 font-bold mt-1">ครั้ง</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-purple-500 to-violet-600 rounded-2xl shadow-lg shadow-purple-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-eye text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Certificates Card -->
    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] md:text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">เกียรติบัตร</p>
                <h3 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($statistics['total_certificates'] ?? 0); ?>
                </h3>
                <p class="text-[10px] text-orange-500 font-bold mt-1">ใบ</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl shadow-lg shadow-orange-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-trophy text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links Section -->
<div class="mb-8">
    <div class="flex items-center mb-6">
        <div class="w-1 h-6 bg-gradient-to-b from-primary-500 to-accent-500 rounded-full mr-3"></div>
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">เมนูลัด</h2>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <?php foreach ($quickLinks ?? [] as $link): 
            $colorClasses = [
                'blue' => ['from-blue-500', 'to-indigo-600', 'shadow-blue-500/30', 'hover:shadow-blue-500/50', 'text-blue-600', 'dark:text-blue-400'],
                'green' => ['from-green-500', 'to-emerald-600', 'shadow-green-500/30', 'hover:shadow-green-500/50', 'text-green-600', 'dark:text-green-400'],
                'purple' => ['from-purple-500', 'to-violet-600', 'shadow-purple-500/30', 'hover:shadow-purple-500/50', 'text-purple-600', 'dark:text-purple-400'],
                'orange' => ['from-orange-500', 'to-amber-600', 'shadow-orange-500/30', 'hover:shadow-orange-500/50', 'text-orange-600', 'dark:text-orange-400'],
                'pink' => ['from-pink-500', 'to-rose-600', 'shadow-pink-500/30', 'hover:shadow-pink-500/50', 'text-pink-600', 'dark:text-pink-400'],
                'gray' => ['from-gray-500', 'to-slate-600', 'shadow-gray-500/30', 'hover:shadow-gray-500/50', 'text-gray-600', 'dark:text-gray-400'],
            ];
            $color = $colorClasses[$link['color']] ?? $colorClasses['blue'];
        ?>
        <a href="<?php echo htmlspecialchars($link['url']); ?>" class="card-hover glass rounded-2xl p-4 md:p-5 text-center group">
            <div class="w-14 h-14 mx-auto flex items-center justify-center bg-gradient-to-br <?php echo $color[0] . ' ' . $color[1]; ?> rounded-2xl shadow-lg <?php echo $color[2]; ?> group-hover:scale-110 transition-all <?php echo $color[3]; ?>">
                <i class="fas <?php echo htmlspecialchars($link['icon']); ?> text-white text-xl"></i>
            </div>
            <h3 class="mt-3 font-bold text-gray-800 dark:text-white text-sm"><?php echo htmlspecialchars($link['title']); ?></h3>
            <p class="mt-1 text-xs <?php echo $color[4] . ' ' . $color[5]; ?>"><?php echo htmlspecialchars($link['description']); ?></p>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Recent Activities & Info Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Activities -->
    <div class="glass rounded-2xl p-6 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="w-1 h-6 bg-gradient-to-b from-primary-500 to-accent-500 rounded-full mr-3"></div>
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">กิจกรรมล่าสุด</h2>
            </div>
            <a href="teacher/teaching_report.php" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">ดูทั้งหมด</a>
        </div>
        
        <?php if (!empty($recentActivities)): ?>
        <div class="space-y-3">
            <?php foreach ($recentActivities as $activity): ?>
            <div class="flex items-center p-3 bg-white/50 dark:bg-slate-800/50 rounded-xl hover:bg-white/80 dark:hover:bg-slate-800/80 transition-colors">
                <div class="w-10 h-10 flex items-center justify-center bg-gradient-to-br from-primary-500 to-accent-500 rounded-xl">
                    <i class="fas fa-file-alt text-white text-sm"></i>
                </div>
                <div class="ml-3 flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 dark:text-white truncate">
                        <?php echo htmlspecialchars($activity['teacher_name'] ?? 'ไม่ระบุ'); ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        <?php echo htmlspecialchars($activity['subject_name'] ?? 'ไม่ระบุวิชา'); ?>
                    </p>
                </div>
                <span class="text-xs text-gray-400">
                    <?php echo isset($activity['created_at']) ? date('d/m', strtotime($activity['created_at'])) : '-'; ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-8">
            <div class="w-16 h-16 mx-auto flex items-center justify-center bg-gray-100 dark:bg-slate-800 rounded-full mb-4">
                <i class="fas fa-inbox text-gray-400 text-2xl"></i>
            </div>
            <p class="text-gray-500 dark:text-gray-400">ยังไม่มีกิจกรรมล่าสุด</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- System Info -->
    <div class="glass rounded-2xl p-6 shadow-xl">
        <div class="flex items-center mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-accent-500 to-primary-500 rounded-full mr-3"></div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-white">ข้อมูลระบบ</h2>
        </div>
        
        <div class="space-y-4">
            <div class="p-4 bg-gradient-to-r from-primary-500/10 to-accent-500/10 rounded-xl border border-primary-200/50 dark:border-primary-800/50">
                <div class="flex items-center">
                    <div class="w-12 h-12 flex items-center justify-center bg-white dark:bg-slate-800 rounded-xl shadow-md">
                        <i class="fas fa-school text-primary-600 dark:text-primary-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">โรงเรียน</p>
                        <p class="font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($global['nameschool'] ?? 'ไม่ระบุ'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="p-4 bg-white/50 dark:bg-slate-800/50 rounded-xl">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">ระบบ</span>
                    <span class="font-semibold text-gray-800 dark:text-white"><?php echo htmlspecialchars($global['pageTitle'] ?? 'Vichakan System'); ?></span>
                </div>
            </div>
            
            <div class="p-4 bg-white/50 dark:bg-slate-800/50 rounded-xl">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">เวอร์ชัน</span>
                    <span class="font-semibold text-gray-800 dark:text-white">2.0</span>
                </div>
            </div>
            
            <div class="p-4 bg-white/50 dark:bg-slate-800/50 rounded-xl">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">สถานะ</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                        ออนไลน์
                    </span>
                </div>
            </div>
        </div>

        <!-- Help Button -->
        <?php if (!$currentUser): ?>
        <div class="mt-6 p-4 bg-gradient-to-r from-primary-500 to-accent-500 rounded-xl text-white">
            <h3 class="font-bold mb-2">ยังไม่ได้เข้าสู่ระบบ?</h3>
            <p class="text-sm opacity-90 mb-3">เข้าสู่ระบบเพื่อใช้งานฟีเจอร์ทั้งหมด</p>
            <a href="login.php" class="inline-flex items-center px-4 py-2 bg-white text-primary-600 font-bold rounded-xl hover:bg-gray-100 transition-colors">
                <i class="fas fa-sign-in-alt mr-2"></i>
                เข้าสู่ระบบ
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
