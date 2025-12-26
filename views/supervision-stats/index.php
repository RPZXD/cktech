<?php
/**
 * Supervision Statistics View
 */
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="glass rounded-3xl p-6 md:p-8 shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold gradient-text">📋 สถิตินิเทศการสอน</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    ภาพรวมข้อมูลการนิเทศการสอนของโรงเรียน
                </p>
            </div>
            <div class="mt-4 md:mt-0">
                <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-violet-600 rounded-2xl text-white shadow-lg">
                    <i class="fas fa-clipboard-check mr-2"></i>
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
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">นิเทศทั้งหมด</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($overallStats['total_supervisions'] ?? 0); ?>
                </h3>
                <p class="text-xs text-gray-400 mt-1">ครั้ง</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-purple-500 to-violet-600 rounded-2xl shadow-lg shadow-purple-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-clipboard-check text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">เดือนนี้</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($overallStats['monthly_supervisions'] ?? 0); ?>
                </h3>
                <p class="text-xs text-gray-400 mt-1">ครั้ง</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg shadow-green-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-calendar-alt text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">ผู้นิเทศ</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($overallStats['total_supervisors'] ?? 0); ?>
                </h3>
                <p class="text-xs text-gray-400 mt-1">คน</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-user-tie text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-hover glass rounded-2xl p-5 md:p-6 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">ครูที่ถูกนิเทศ</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo number_format($overallStats['teachers_supervised'] ?? 0); ?>
                </h3>
                <p class="text-xs text-gray-400 mt-1">คน</p>
            </div>
            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl shadow-lg shadow-orange-500/30 group-hover:scale-110 transition-transform">
                <i class="fas fa-chalkboard-teacher text-white text-lg md:text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Chart Section -->
<div class="glass rounded-2xl p-6 shadow-xl mb-8">
    <div class="flex items-center mb-4">
        <div class="w-1 h-6 bg-gradient-to-b from-purple-500 to-violet-600 rounded-full mr-3"></div>
        <h2 class="text-lg font-bold text-gray-800 dark:text-white">📈 การนิเทศรายเดือน</h2>
    </div>
    <div class="h-72">
        <canvas id="supervisionChart"></canvas>
    </div>
</div>

<!-- Info Card -->
<div class="glass rounded-2xl p-6 shadow-xl">
    <div class="text-center py-8">
        <div class="w-20 h-20 mx-auto flex items-center justify-center bg-gradient-to-br from-purple-500 to-violet-600 rounded-full mb-4">
            <i class="fas fa-clipboard-check text-white text-3xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">ระบบนิเทศการสอน</h3>
        <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">
            ติดตามและประเมินผลการจัดการเรียนการสอนของครูในโรงเรียน
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const monthData = <?php echo json_encode($supervisionsByMonth ?? []); ?>;
const labels = monthData.map(item => item.month);
const counts = monthData.map(item => item.count);

Chart.defaults.font.family = "'Mali', sans-serif";

new Chart(document.getElementById('supervisionChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'จำนวนการนิเทศ',
            data: counts,
            borderColor: 'rgba(139, 92, 246, 1)',
            backgroundColor: 'rgba(139, 92, 246, 0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: 'rgba(139, 92, 246, 1)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6
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
</script>
