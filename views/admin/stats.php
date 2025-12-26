<?php
/**
 * Admin Statistics View
 * MVC Pattern - View for statistics and analytics
 * Enhanced UI/UX with Tailwind CSS - Mobile Responsive
 */
?>

<style>
    /* Floating Animation */
    .float-animation { animation: floating 3s ease-in-out infinite; }
    @keyframes floating { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
    
    /* Stat card glow */
    .stat-glow { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
    .stat-glow:hover { 
        transform: translateY(-4px);
        box-shadow: 0 10px 25px -5px rgba(168, 85, 247, 0.2); 
    }
    
    /* Chart Containers */
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    .chart-container-pie {
        position: relative;
        height: 380px;
        width: 100%;
    }
    
    @media (min-width: 768px) {
        .chart-container { height: 350px; }
        .chart-container-pie { height: 420px; }
    }

    /* Custom Scrollbar for Heatmap */
    .heatmap-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin: 0 -1rem;
        padding: 0 1rem;
    }
    .heatmap-scroll::-webkit-scrollbar {
        height: 4px;
    }
    .heatmap-scroll::-webkit-scrollbar-track {
        background: rgba(0,0,0,0.02);
    }
    .heatmap-scroll::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.1);
        border-radius: 10px;
    }

    /* Glass refinement for mobile */
    @media (max-width: 640px) {
        .glass-mobile-flat {
            padding: 1rem !important;
            border-radius: 1rem !important;
        }
    }
</style>

<!-- Page Header -->
<div class="mb-4 md:mb-8">
    <div class="relative glass rounded-2xl md:rounded-3xl p-4 md:p-6 lg:p-8 shadow-xl overflow-hidden">
        <div class="absolute -top-20 -right-20 w-40 h-40 md:w-60 md:h-60 bg-gradient-to-br from-orange-400/20 to-amber-400/20 rounded-full blur-3xl"></div>
        
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex-1 flex items-center gap-3">
                <div class="w-12 h-12 md:w-16 md:h-16 flex-shrink-0 bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-orange-500/30">
                    <i class="fas fa-chart-line text-xl md:text-3xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="px-2 py-0.5 bg-orange-500 text-white text-[8px] md:text-[10px] font-black rounded-md uppercase tracking-widest shadow-sm">Realtime Analytics</span>
                    </div>
                    <h1 class="text-lg md:text-2xl lg:text-3xl font-black text-gray-900 dark:text-white leading-tight">
                        สถิติและวิเคราะห์ข้อมูล
                    </h1>
                    <p class="text-[10px] md:text-sm text-gray-500 dark:text-gray-400 font-bold opacity-80">
                        ภาพรวมการจัดการเรียนรู้รายภาคเรียน
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 w-full md:w-auto">
                <div class="relative flex-1 md:w-48">
                    <i class="fas fa-filter absolute left-3 top-1/2 -translate-y-1/2 text-orange-500 text-xs z-10"></i>
                    <select id="filterPeriod" class="w-full pl-8 pr-4 py-2 bg-white dark:bg-gray-800 border-2 border-orange-500/10 dark:border-gray-700 rounded-xl text-gray-700 dark:text-gray-200 text-xs font-black focus:ring-4 focus:ring-orange-500/10 outline-none transition-all shadow-sm appearance-none">
                        <option value="week">สัปดาห์นี้</option>
                        <option value="month" selected>เดือนนี้</option>
                        <option value="semester">ภาคเรียนนี้</option>
                        <option value="year">ปีการศึกษานี้</option>
                    </select>
                </div>
                <button id="btnRefresh" class="flex-shrink-0 w-10 h-10 md:w-auto md:px-5 inline-flex items-center justify-center bg-gradient-to-r from-orange-500 to-amber-600 rounded-xl text-white shadow-lg shadow-orange-500/20 hover:shadow-orange-500/40 active:scale-95 transition-all outline-none">
                    <i class="fas fa-sync-alt md:mr-2"></i>
                    <span class="hidden md:inline text-xs font-black">อัปเดตข้อมูล</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Summary Stats -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-4 md:mb-8">
    <div class="stat-glow glass rounded-2xl p-4 md:p-5 group relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-indigo-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative flex items-center justify-between">
            <div class="min-w-0 flex-1">
                <p class="text-[10px] md:text-sm font-bold text-gray-400 dark:text-gray-500 truncate uppercase tracking-wider">รายงานทั้งหมด</p>
                <h3 id="statTotalReports" class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white mt-0.5">0</h3>
                <p class="text-[10px] md:text-xs text-emerald-500 mt-1 flex items-center gap-1 font-bold">
                    <i class="fas fa-arrow-up"></i> <span id="statReportsGrowth">0%</span> <span class="hidden sm:inline opacity-60">จากเดือนก่อน</span>
                </p>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 flex-shrink-0 flex items-center justify-center bg-blue-500/10 dark:bg-blue-500/20 rounded-xl group-hover:scale-110 transition-transform">
                <i class="fas fa-file-alt text-blue-600 dark:text-blue-400 text-base md:text-lg"></i>
            </div>
        </div>
    </div>
    
    <div class="stat-glow glass rounded-2xl p-4 md:p-5 group relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-green-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative flex items-center justify-between">
            <div class="min-w-0 flex-1">
                <p class="text-[10px] md:text-sm font-bold text-gray-400 dark:text-gray-500 truncate uppercase tracking-wider">ครูที่ส่งรายงาน</p>
                <h3 id="statActiveTeachers" class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white mt-0.5">0</h3>
                <p class="text-[10px] md:text-xs text-gray-400 mt-1 font-bold opacity-60">
                    จาก <span id="statTotalTeachers" class="text-emerald-500">0</span> คน
                </p>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 flex-shrink-0 flex items-center justify-center bg-emerald-500/10 dark:bg-emerald-500/20 rounded-xl group-hover:scale-110 transition-transform">
                <i class="fas fa-chalkboard-teacher text-emerald-600 dark:text-emerald-400 text-base md:text-lg"></i>
            </div>
        </div>
    </div>
    
    <div class="stat-glow glass rounded-2xl p-4 md:p-5 group relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-violet-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative flex items-center justify-between">
            <div class="min-w-0 flex-1">
                <p class="text-[10px] md:text-sm font-bold text-gray-400 dark:text-gray-500 truncate uppercase tracking-wider">อัตราการเข้าเรียน</p>
                <h3 id="statAttendanceRate" class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white mt-0.5">0%</h3>
                <p class="text-[10px] md:text-xs text-gray-400 mt-1 font-bold opacity-60">เฉลี่ยทั้งโรงเรียน</p>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 flex-shrink-0 flex items-center justify-center bg-purple-500/10 dark:bg-purple-500/20 rounded-xl group-hover:scale-110 transition-transform">
                <i class="fas fa-user-check text-purple-600 dark:text-purple-400 text-base md:text-lg"></i>
            </div>
        </div>
    </div>
    
    <div class="stat-glow glass rounded-2xl p-4 md:p-5 group relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-orange-500/10 to-amber-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative flex items-center justify-between">
            <div class="min-w-0 flex-1">
                <p class="text-[10px] md:text-sm font-bold text-gray-400 dark:text-gray-500 truncate uppercase tracking-wider">รายงานวันนี้</p>
                <h3 id="statTodayReports" class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white mt-0.5">0</h3>
                <p class="text-[10px] md:text-xs text-gray-400 mt-1 font-bold opacity-60">
                    เป้าหมาย: <span id="statTodayTarget" class="text-orange-500">0</span>
                </p>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 flex-shrink-0 flex items-center justify-center bg-orange-500/10 dark:bg-orange-500/20 rounded-xl group-hover:scale-110 transition-transform">
                <i class="fas fa-calendar-check text-orange-600 dark:text-orange-400 text-base md:text-lg"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-4 md:mb-8">
    <!-- Reports by Department -->
    <div class="glass rounded-xl md:rounded-2xl shadow-lg overflow-hidden">
        <div class="p-3 md:p-5 border-b border-gray-200/50 dark:border-gray-700/50 bg-gradient-to-r from-blue-500/10 to-indigo-500/10">
            <div class="flex items-center gap-2 md:gap-3">
                <div class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg md:rounded-xl shadow-lg">
                    <i class="fas fa-chart-pie text-white text-sm md:text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-gray-800 dark:text-white text-sm md:text-base truncate">รายงานตามกลุ่มสาระ</h3>
                    <p class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400 truncate">สัดส่วนการส่งรายงานแต่ละกลุ่ม</p>
                </div>
            </div>
        </div>
        <div class="p-3 md:p-6">
            <div class="chart-container-pie">
                <canvas id="chartDepartment"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Reports Timeline -->
    <div class="glass rounded-xl md:rounded-2xl shadow-lg overflow-hidden">
        <div class="p-3 md:p-5 border-b border-gray-200/50 dark:border-gray-700/50 bg-gradient-to-r from-emerald-500/10 to-green-500/10">
            <div class="flex items-center gap-2 md:gap-3">
                <div class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center bg-gradient-to-br from-emerald-500 to-green-600 rounded-lg md:rounded-xl shadow-lg">
                    <i class="fas fa-chart-line text-white text-sm md:text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-gray-800 dark:text-white text-sm md:text-base truncate">แนวโน้มการส่งรายงาน</h3>
                    <p class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400 truncate">จำนวนรายงานรายวัน</p>
                </div>
            </div>
        </div>
        <div class="p-2 md:p-6">
            <div class="chart-container">
                <canvas id="chartTimeline"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- More Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-4 md:mb-8">
    <!-- Attendance Chart -->
    <div class="glass rounded-xl md:rounded-2xl shadow-lg overflow-hidden">
        <div class="p-3 md:p-5 border-b border-gray-200/50 dark:border-gray-700/50 bg-gradient-to-r from-purple-500/10 to-violet-500/10">
            <div class="flex items-center gap-2 md:gap-3">
                <div class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center bg-gradient-to-br from-purple-500 to-violet-600 rounded-lg md:rounded-xl shadow-lg">
                    <i class="fas fa-users text-white text-sm md:text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-gray-800 dark:text-white text-sm md:text-base truncate">อัตราการเข้าเรียน</h3>
                    <p class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400 truncate">เปรียบเทียบตามระดับชั้น</p>
                </div>
            </div>
        </div>
        <div class="p-3 md:p-6">
            <div class="chart-container">
                <canvas id="chartAttendance"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Top Teachers -->
    <div class="glass rounded-xl md:rounded-2xl shadow-lg overflow-hidden">
        <div class="p-3 md:p-5 border-b border-gray-200/50 dark:border-gray-700/50 bg-gradient-to-r from-orange-500/10 to-amber-500/10">
            <div class="flex items-center gap-2 md:gap-3">
                <div class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg md:rounded-xl shadow-lg">
                    <i class="fas fa-trophy text-white text-sm md:text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-gray-800 dark:text-white text-sm md:text-base truncate">ครูที่ส่งรายงานมากที่สุด</h3>
                    <p class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400 truncate">Top 10 ประจำเดือน</p>
                </div>
            </div>
        </div>
        <div class="p-3 md:p-6">
            <div id="topTeachersList" class="top-teachers-list space-y-2 md:space-y-3">
                <!-- JS will fill -->
                <div class="animate-pulse space-y-2 md:space-y-3">
                    <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Weekly Heatmap -->
<div class="glass rounded-xl md:rounded-2xl shadow-lg overflow-hidden mb-4 md:mb-6">
    <div class="p-3 md:p-5 border-b border-gray-200/50 dark:border-gray-700/50 bg-gradient-to-r from-pink-500/10 to-rose-500/10">
        <div class="flex items-center gap-2 md:gap-3">
            <div class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center bg-gradient-to-br from-pink-500 to-rose-600 rounded-lg md:rounded-xl shadow-lg">
                <i class="fas fa-th text-white text-sm md:text-base"></i>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm md:text-base truncate">Heatmap การส่งรายงาน</h3>
                <p class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400 truncate">แสดงความเข้มข้นของการส่งรายงานตามวันและเวลา</p>
            </div>
        </div>
    </div>
    <div class="p-4 md:p-6 heatmap-scroll">
        <div id="heatmapContainer" class="min-w-[700px] lg:min-w-0">
            <!-- Heatmap Header -->
            <div class="grid grid-cols-10 gap-2 text-[10px] md:text-xs mb-3">
                <div class="flex items-center justify-center">
                    <span class="w-8"></span>
                </div>
                <?php for($i=1; $i<=9; $i++): ?>
                <div class="text-center text-gray-400 dark:text-gray-500 font-black uppercase tracking-tighter">คาบ <?= $i ?></div>
                <?php endfor; ?>
            </div>
            <div id="heatmapGrid" class="grid gap-2">
                <!-- JS will fill -->
            </div>
            <!-- Legend -->
            <div class="flex items-center justify-center gap-2 mt-4 text-[10px] md:text-xs text-gray-500 dark:text-gray-400">
                <span>น้อย</span>
                <div class="flex gap-1 md:gap-1.5">
                    <div class="w-5 h-5 md:w-6 md:h-6 bg-gray-100 dark:bg-gray-700 rounded-md"></div>
                    <div class="w-5 h-5 md:w-6 md:h-6 bg-green-200 dark:bg-green-900/50 rounded-md"></div>
                    <div class="w-5 h-5 md:w-6 md:h-6 bg-green-400 dark:bg-green-700 rounded-md"></div>
                    <div class="w-5 h-5 md:w-6 md:h-6 bg-green-600 dark:bg-green-500 rounded-md"></div>
                    <div class="w-5 h-5 md:w-6 md:h-6 bg-green-800 dark:bg-green-400 rounded-md"></div>
                </div>
                <span>มาก</span>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/admin-stats.js"></script>
