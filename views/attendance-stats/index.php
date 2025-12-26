<?php
/**
 * Attendance Statistics View
 */
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="glass rounded-3xl p-6 md:p-8 shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold gradient-text">✅ สถิติการเช็คชื่อ</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    ภาพรวมข้อมูลการเข้าเรียนและการมาเรียนของนักเรียน
                </p>
            </div>
            <div class="mt-4 md:mt-0">
                <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-pink-500 to-rose-600 rounded-2xl text-white shadow-lg">
                    <i class="fas fa-user-check mr-2"></i>
                    <span class="font-medium">Dashboard</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">เช็คชื่อทั้งหมด</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($overallStats['total_records'] ?? 0); ?>
                </h3>
                <p class="text-xs text-gray-400 mt-1">รายการ</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-list-check text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">มาเรียน</p>
                <h3 class="text-2xl md:text-3xl font-bold text-green-600 dark:text-green-400 mt-1">
                    <?php echo number_format($overallStats['present_count'] ?? 0); ?>
                </h3>
                <p class="text-xs text-gray-400 mt-1">คน</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg shadow-green-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-check text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">ขาดเรียน</p>
                <h3 class="text-2xl md:text-3xl font-bold text-red-600 dark:text-red-400 mt-1">
                    <?php echo number_format($overallStats['absent_count'] ?? 0); ?>
                </h3>
                <p class="text-xs text-gray-400 mt-1">คน</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl shadow-lg shadow-red-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-times text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">อัตราการมาเรียน</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($overallStats['attendance_rate'] ?? 0, 1); ?>%
                </h3>
                <p class="text-xs text-gray-400 mt-1">เฉลี่ย</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-purple-500 to-violet-600 rounded-2xl shadow-lg shadow-purple-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-percentage text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats Row -->
<div class="grid grid-cols-3 gap-4 md:gap-6 mb-8">
    <div class="glass rounded-2xl p-4 md:p-5 text-center">
        <div class="w-10 h-10 mx-auto flex items-center justify-center bg-amber-100 dark:bg-amber-900/30 rounded-xl mb-2">
            <i class="fas fa-clock text-amber-600 dark:text-amber-400"></i>
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($overallStats['late_count'] ?? 0); ?></p>
        <p class="text-xs text-gray-500 dark:text-gray-400">มาสาย</p>
    </div>
    <div class="glass rounded-2xl p-4 md:p-5 text-center">
        <div class="w-10 h-10 mx-auto flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 rounded-xl mb-2">
            <i class="fas fa-thermometer text-blue-600 dark:text-blue-400"></i>
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($overallStats['sick_count'] ?? 0); ?></p>
        <p class="text-xs text-gray-500 dark:text-gray-400">ลาป่วย</p>
    </div>
    <div class="glass rounded-2xl p-4 md:p-5 text-center">
        <div class="w-10 h-10 mx-auto flex items-center justify-center bg-indigo-100 dark:bg-indigo-900/30 rounded-xl mb-2">
            <i class="fas fa-file-alt text-indigo-600 dark:text-indigo-400"></i>
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($overallStats['personal_count'] ?? 0); ?></p>
        <p class="text-xs text-gray-500 dark:text-gray-400">ลากิจ</p>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Status Distribution -->
    <div class="glass rounded-2xl p-6 shadow-xl">
        <div class="flex items-center mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-pink-500 to-rose-600 rounded-full mr-3"></div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-white">📊 สถานะการเช็คชื่อ</h2>
        </div>
        <div class="h-64">
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <!-- Monthly Trend -->
    <div class="glass rounded-2xl p-6 shadow-xl">
        <div class="flex items-center mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-green-500 to-emerald-600 rounded-full mr-3"></div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-white">📈 มาเรียน vs ขาดเรียน รายเดือน</h2>
        </div>
        <div class="h-64">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>
</div>

<!-- Top Absent Students Table -->
<div class="glass rounded-2xl p-6 shadow-xl">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <div class="w-1 h-6 bg-gradient-to-b from-red-500 to-rose-600 rounded-full mr-3"></div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-white">⚠️ นักเรียนที่ขาดเรียนมากที่สุด</h2>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left py-3 px-2 text-sm font-semibold text-gray-600 dark:text-gray-300">อันดับ</th>
                    <th class="text-left py-3 px-2 text-sm font-semibold text-gray-600 dark:text-gray-300">ชื่อนักเรียน</th>
                    <th class="text-left py-3 px-2 text-sm font-semibold text-gray-600 dark:text-gray-300">ห้อง</th>
                    <th class="text-right py-3 px-2 text-sm font-semibold text-gray-600 dark:text-gray-300">จำนวนครั้ง</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topAbsentStudents as $index => $student): ?>
                <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-white/50 dark:hover:bg-slate-800/50">
                    <td class="py-3 px-2">
                        <?php if ($index < 3): ?>
                            <span class="w-6 h-6 inline-flex items-center justify-center rounded-full text-sm font-bold bg-red-100 text-red-600">
                                <?php echo $index + 1; ?>
                            </span>
                        <?php else: ?>
                            <span class="text-gray-500 pl-2"><?php echo $index + 1; ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 px-2 text-sm text-gray-800 dark:text-gray-200"><?php echo htmlspecialchars($student['student_name']); ?></td>
                    <td class="py-3 px-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                            <?php echo htmlspecialchars($student['room']); ?>
                        </span>
                    </td>
                    <td class="py-3 px-2 text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                            <?php echo number_format($student['count']); ?> ครั้ง
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($topAbsentStudents)): ?>
                <tr>
                    <td colspan="4" class="py-8 text-center text-gray-500">ยังไม่มีข้อมูล</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
Chart.defaults.font.family = "'Mali', sans-serif";

// Status Distribution Chart
const statusData = <?php echo json_encode($attendanceByStatus ?? []); ?>;
const statusLabels = statusData.map(item => item.status);
const statusCounts = statusData.map(item => parseInt(item.count));

const statusColors = {
    'มาเรียน': 'rgba(34, 197, 94, 0.8)',
    'ขาดเรียน': 'rgba(239, 68, 68, 0.8)',
    'มาสาย': 'rgba(245, 158, 11, 0.8)',
    'ลาป่วย': 'rgba(59, 130, 246, 0.8)',
    'ลากิจ': 'rgba(99, 102, 241, 0.8)',
    'เข้าร่วมกิจกรรม': 'rgba(168, 85, 247, 0.8)',
    'โดดเรียน': 'rgba(107, 114, 128, 0.8)'
};

const colors = statusLabels.map(label => statusColors[label] || 'rgba(156, 163, 175, 0.8)');

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusCounts,
            backgroundColor: colors,
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
                labels: { padding: 15, usePointStyle: true }
            }
        }
    }
});

// Monthly Trend Chart
const monthData = <?php echo json_encode($attendanceByMonth ?? []); ?>;
const monthLabels = monthData.map(item => item.month);
const presentData = monthData.map(item => item.present);
const absentData = monthData.map(item => item.absent);

new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: monthLabels,
        datasets: [
            {
                label: 'มาเรียน',
                data: presentData,
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderRadius: 4
            },
            {
                label: 'ขาดเรียน',
                data: absentData,
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderRadius: 4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: { usePointStyle: true }
            }
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
</script>
