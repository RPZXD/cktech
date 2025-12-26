<?php
/**
 * Teacher Dashboard View
 * MVC Pattern - View for teacher index page
 */
?>

<!-- Welcome Section -->
<div class="mb-8">
    <div class="glass rounded-3xl p-6 md:p-8 shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex-1">
                <h1 class="text-2xl md:text-3xl font-bold gradient-text">
                    👩‍🏫 ยินดีต้อนรับ, <?php echo htmlspecialchars($user['name']); ?>
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    คุณเข้าสู่ระบบในฐานะ <span class="font-semibold text-emerald-600 dark:text-emerald-400"><?php echo htmlspecialchars($user['role']); ?></span>
                </p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-3">
                <a href="teaching-report.php" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-green-600 rounded-2xl text-white shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">
                    <i class="fas fa-plus mr-2"></i>
                    <span class="font-medium">บันทึกการสอน</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">รายงานทั้งหมด</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($quickStats['total_reports'] ?? 0); ?>
                </h3>
                <p class="text-xs text-gray-400 mt-1">รายการ</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-file-alt text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">เดือนนี้</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($quickStats['this_month'] ?? 0); ?>
                </h3>
                <p class="text-xs text-gray-400 mt-1">รายการ</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-calendar-check text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">รายวิชา</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($quickStats['total_subjects'] ?? 0); ?>
                </h3>
                <p class="text-xs text-gray-400 mt-1">วิชา</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-purple-500 to-violet-600 rounded-2xl shadow-lg shadow-purple-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-book text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">นักเรียน</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($quickStats['total_students'] ?? 0); ?>
                </h3>
                <p class="text-xs text-gray-400 mt-1">คน</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl shadow-lg shadow-orange-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-users text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Usage Guide -->
<div class="glass rounded-2xl p-6 md:p-8 shadow-xl">
    <div class="flex items-center mb-6">
        <div class="w-1 h-8 bg-gradient-to-b from-emerald-500 to-green-600 rounded-full mr-4"></div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">📖 คู่มือการใช้งานสำหรับครู</h2>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
        <?php 
        $colorClasses = [
            'blue' => ['from' => 'from-blue-50', 'to' => 'to-blue-100', 'hover_from' => 'hover:from-blue-100', 'hover_to' => 'hover:to-blue-200', 'text' => 'text-blue-600', 'bg' => 'bg-blue-100', 'text_dark' => 'text-blue-700'],
            'green' => ['from' => 'from-green-50', 'to' => 'to-green-100', 'hover_from' => 'hover:from-green-100', 'hover_to' => 'hover:to-green-200', 'text' => 'text-green-600', 'bg' => 'bg-green-100', 'text_dark' => 'text-green-700'],
            'purple' => ['from' => 'from-purple-50', 'to' => 'to-purple-100', 'hover_from' => 'hover:from-purple-100', 'hover_to' => 'hover:to-purple-200', 'text' => 'text-purple-600', 'bg' => 'bg-purple-100', 'text_dark' => 'text-purple-700'],
            'orange' => ['from' => 'from-orange-50', 'to' => 'to-orange-100', 'hover_from' => 'hover:from-orange-100', 'hover_to' => 'hover:to-orange-200', 'text' => 'text-orange-600', 'bg' => 'bg-orange-100', 'text_dark' => 'text-orange-700']
        ];
        foreach ($guides as $guide): 
            $color = $colorClasses[$guide['color']] ?? $colorClasses['blue'];
        ?>
        <a href="<?php echo htmlspecialchars($guide['link']); ?>" class="block">
            <div class="flex items-start gap-4 p-5 rounded-2xl bg-gradient-to-r <?php echo $color['from']; ?> <?php echo $color['to']; ?> <?php echo $color['hover_from']; ?> <?php echo $color['hover_to']; ?> transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg group">
                <span class="text-4xl group-hover:animate-bounce"><?php echo $guide['icon']; ?></span>
                <div>
                    <span class="font-semibold <?php echo $color['text']; ?> text-lg"><?php echo htmlspecialchars($guide['title']); ?></span>
                    <p class="text-gray-600 dark:text-gray-700 mt-1"><?php echo htmlspecialchars($guide['description']); ?></p>
                    <ul class="mt-2 space-y-1">
                        <?php foreach ($guide['details'] as $detail): ?>
                        <li class="text-sm text-gray-500 flex items-start">
                            <i class="fas fa-check-circle <?php echo $color['text']; ?> mr-2 mt-0.5 text-xs"></i>
                            <?php echo htmlspecialchars($detail); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    
    <div class="mt-8 text-center">
        <div class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-100 to-green-100 dark:from-emerald-900/30 dark:to-green-900/30 rounded-2xl shadow-md">
            <span class="text-gray-600 dark:text-gray-300">✨ ระบบนี้ออกแบบมาเพื่อช่วยให้ครูจัดการข้อมูลการสอนได้สะดวกและมีประสิทธิภาพมากขึ้น ✨</span>
        </div>
    </div>
</div>
