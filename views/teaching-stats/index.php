<?php
/**
 * Teaching Statistics View
 * Displays charts, cards, and tables for teaching report statistics
 */
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="glass rounded-3xl p-6 md:p-8 shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold gradient-text">📊 สถิติรายงานการสอน</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    ภาพรวมข้อมูลการบันทึกการสอนของครูในโรงเรียน
                </p>
            </div>
            <div class="mt-4 md:mt-0">
                <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl text-white shadow-lg">
                    <i class="fas fa-chart-line mr-2"></i>
                    <span class="font-medium">Dashboard</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
    <!-- Total Reports -->
    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">รายงานทั้งหมด</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($overallStats['total_reports'] ?? 0); ?>
                </h3>
                <p class="text-xs text-gray-400 mt-1">รายการ</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-file-alt text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Monthly Reports -->
    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">เดือนนี้</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($overallStats['monthly_reports'] ?? 0); ?>
                </h3>
                <p class="text-xs text-gray-400 mt-1">รายการ</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg shadow-green-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-calendar-alt text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Today Reports -->
    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">วันนี้</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($overallStats['today_reports'] ?? 0); ?>
                </h3>
                <p class="text-xs text-gray-400 mt-1">รายการ</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-purple-500 to-violet-600 rounded-2xl shadow-lg shadow-purple-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-clock text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Active Teachers -->
    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">ครูที่บันทึก</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($overallStats['active_teachers'] ?? 0); ?>
                </h3>
                <p class="text-xs text-gray-400 mt-1">คน</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl shadow-lg shadow-orange-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-chalkboard-teacher text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Monthly Reports Chart -->
    <div class="glass rounded-2xl p-6 shadow-xl">
        <div class="flex items-center mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-green-500 to-emerald-600 rounded-full mr-3"></div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-white">📈 รายงานรายเดือน</h2>
        </div>
        <div class="h-64">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    <!-- Reports by Day Chart -->
    <div class="glass rounded-2xl p-6 shadow-xl">
        <div class="flex items-center mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-purple-500 to-violet-600 rounded-full mr-3"></div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-white">📅 รายงานตามวัน</h2>
        </div>
        <div class="h-64">
            <canvas id="dayChart"></canvas>
        </div>
    </div>
</div>

<!-- Tables Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Top Teachers -->
    <div class="glass rounded-2xl p-6 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="w-1 h-6 bg-gradient-to-b from-blue-500 to-indigo-600 rounded-full mr-3"></div>
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">🏆 ครูที่บันทึกมากที่สุด</h2>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-2 text-sm font-semibold text-gray-600 dark:text-gray-300">อันดับ</th>
                        <th class="text-left py-3 px-2 text-sm font-semibold text-gray-600 dark:text-gray-300">ชื่อครู</th>
                        <th class="text-right py-3 px-2 text-sm font-semibold text-gray-600 dark:text-gray-300">จำนวน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topTeachers as $index => $teacher): ?>
                    <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-white/50 dark:hover:bg-slate-800/50">
                        <td class="py-3 px-2">
                            <?php if ($index < 3): ?>
                                <span class="w-6 h-6 inline-flex items-center justify-center rounded-full text-sm font-bold
                                    <?php echo $index === 0 ? 'bg-yellow-100 text-yellow-600' : ($index === 1 ? 'bg-gray-100 text-gray-600' : 'bg-orange-100 text-orange-600'); ?>">
                                    <?php echo $index + 1; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-500 pl-2"><?php echo $index + 1; ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-2 text-sm text-gray-800 dark:text-gray-200"><?php echo htmlspecialchars($teacher['teacher_name']); ?></td>
                        <td class="py-3 px-2 text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                <?php echo number_format($teacher['count']); ?> รายการ
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($topTeachers)): ?>
                    <tr>
                        <td colspan="3" class="py-8 text-center text-gray-500">ยังไม่มีข้อมูล</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Reports by Subject -->
    <div class="glass rounded-2xl p-6 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="w-1 h-6 bg-gradient-to-b from-orange-500 to-amber-600 rounded-full mr-3"></div>
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">📚 รายงานตามรายวิชา</h2>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-2 text-sm font-semibold text-gray-600 dark:text-gray-300">อันดับ</th>
                        <th class="text-left py-3 px-2 text-sm font-semibold text-gray-600 dark:text-gray-300">รายวิชา</th>
                        <th class="text-right py-3 px-2 text-sm font-semibold text-gray-600 dark:text-gray-300">จำนวน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportsBySubject as $index => $subject): ?>
                    <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-white/50 dark:hover:bg-slate-800/50">
                        <td class="py-3 px-2 text-gray-500"><?php echo $index + 1; ?></td>
                        <td class="py-3 px-2 text-sm text-gray-800 dark:text-gray-200"><?php echo htmlspecialchars($subject['subject_name'] ?? 'ไม่ระบุ'); ?></td>
                        <td class="py-3 px-2 text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                <?php echo number_format($subject['count']); ?> รายการ
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($reportsBySubject)): ?>
                    <tr>
                        <td colspan="3" class="py-8 text-center text-gray-500">ยังไม่มีข้อมูล</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Reports Table -->
<div class="glass rounded-2xl p-6 shadow-xl mb-8">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <div class="w-1 h-6 bg-gradient-to-b from-pink-500 to-rose-600 rounded-full mr-3"></div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-white">📋 รายงานล่าสุด</h2>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left py-3 px-2 text-sm font-semibold text-gray-600 dark:text-gray-300">วันที่</th>
                    <th class="text-left py-3 px-2 text-sm font-semibold text-gray-600 dark:text-gray-300">ครูผู้สอน</th>
                    <th class="text-left py-3 px-2 text-sm font-semibold text-gray-600 dark:text-gray-300">รายวิชา</th>
                    <th class="text-left py-3 px-2 text-sm font-semibold text-gray-600 dark:text-gray-300">ห้อง</th>
                    <th class="text-center py-3 px-2 text-sm font-semibold text-gray-600 dark:text-gray-300">คาบ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentReports as $report): ?>
                <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-white/50 dark:hover:bg-slate-800/50">
                    <td class="py-3 px-2 text-sm text-gray-600 dark:text-gray-300">
                        <?php 
                        if (!empty($report['report_date'])) {
                            $date = new DateTime($report['report_date']);
                            echo $date->format('d/m/') . ($date->format('Y') + 543);
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td class="py-3 px-2 text-sm text-gray-800 dark:text-gray-200"><?php echo htmlspecialchars($report['teacher_name'] ?? 'ไม่ระบุ'); ?></td>
                    <td class="py-3 px-2 text-sm text-gray-600 dark:text-gray-300"><?php echo htmlspecialchars($report['subject_name'] ?? 'ไม่ระบุ'); ?></td>
                    <td class="py-3 px-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                            <?php echo htmlspecialchars($report['class_room'] ?? '-'); ?>
                        </span>
                    </td>
                    <td class="py-3 px-2 text-center text-sm text-gray-600 dark:text-gray-300">
                        <?php echo ($report['period_start'] ?? '-') . ' - ' . ($report['period_end'] ?? '-'); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recentReports)): ?>
                <tr>
                    <td colspan="5" class="py-8 text-center text-gray-500">ยังไม่มีข้อมูลรายงาน</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly Chart Data
const monthlyData = <?php echo json_encode($reportsByMonth ?? []); ?>;
const monthlyLabels = monthlyData.map(item => item.month);
const monthlyCounts = monthlyData.map(item => item.count);

// Day Chart Data
const dayData = <?php echo json_encode($reportsByDay ?? []); ?>;
const dayLabels = dayData.map(item => item.day);
const dayCounts = dayData.map(item => item.count);

// Chart.js default config
Chart.defaults.font.family = "'Mali', sans-serif";

// Monthly Reports Chart
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: monthlyLabels,
        datasets: [{
            label: 'จำนวนรายงาน',
            data: monthlyCounts,
            backgroundColor: 'rgba(16, 185, 129, 0.8)',
            borderColor: 'rgba(16, 185, 129, 1)',
            borderWidth: 1,
            borderRadius: 8,
            barThickness: 40
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});

// Day of Week Chart
new Chart(document.getElementById('dayChart'), {
    type: 'doughnut',
    data: {
        labels: dayLabels,
        datasets: [{
            data: dayCounts,
            backgroundColor: [
                'rgba(239, 68, 68, 0.8)',
                'rgba(249, 115, 22, 0.8)',
                'rgba(234, 179, 8, 0.8)',
                'rgba(34, 197, 94, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(236, 72, 153, 0.8)'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    padding: 15,
                    usePointStyle: true
                }
            }
        }
    }
});
</script>
