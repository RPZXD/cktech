<?php
/**
 * Department Statistics View
 * MVC Pattern - Premium view for department data analysis
 */
?>

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    /* Daterangepicker Custom Styling */
    .daterangepicker { 
        border-radius: 1rem; 
        border: none; 
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); 
        font-family: 'Mali', sans-serif; 
        padding: 10px; 
    }
    .daterangepicker .ranges li.active { background-color: #3b82f6; }
    .daterangepicker .applyBtn { background-color: #3b82f6; border-radius: 0.75rem; border: none; }

    /* Card Styling */
    .stat-card {
        background: white;
        border-radius: 1.5rem;
        border: 1px solid rgba(0,0,0,0.06);
        box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }

    /* Summary Card Animation */
    .summary-card {
        transition: all 0.3s ease;
    }
    .summary-card:hover {
        transform: translateY(-5px) scale(1.02);
    }

    /* Quality Circle */
    .quality-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }
    .quality-circle:hover {
        transform: rotate(12deg) scale(1.05);
    }

    /* Mobile adjustments */
    @media (max-width: 640px) {
        .quality-circle { width: 60px; height: 60px; }
    }
</style>

<div class="space-y-6 pb-8">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-lg border border-slate-100 dark:border-slate-800">
        <div class="flex flex-col lg:flex-row justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-blue-500/30">
                    📊
                </div>
                <div>
                    <h1 class="text-xl lg:text-2xl font-black text-slate-800 dark:text-white">สถิติและวิเคราะห์ข้อมูล</h1>
                    <p class="text-slate-400 font-medium text-sm">กลุ่มสาระ<?php echo htmlspecialchars($department); ?></p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative">
                    <input type="text" id="dateRangePicker" class="w-full sm:w-64 bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 pl-10 focus:border-blue-500 transition-all font-bold text-sm text-slate-700 dark:text-slate-200 cursor-pointer" readonly>
                    <i class="fas fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-blue-500"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="summary-card bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-5 text-white shadow-lg shadow-blue-500/25">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-lg">📄</div>
                <span class="text-[9px] font-black uppercase opacity-70">Reports</span>
            </div>
            <p id="totalReports" class="text-3xl lg:text-4xl font-black leading-none">0</p>
            <p class="text-xs font-medium opacity-80 mt-1">รายงานทั้งหมด</p>
        </div>

        <div class="summary-card bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 text-white shadow-lg shadow-emerald-500/25">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-lg">👨‍🏫</div>
                <span class="text-[9px] font-black uppercase opacity-70">Teachers</span>
            </div>
            <p id="activeTeachers" class="text-3xl lg:text-4xl font-black leading-none">0</p>
            <p class="text-xs font-medium opacity-80 mt-1">ครูที่ส่งรายงาน</p>
        </div>

        <div class="summary-card bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-5 text-white shadow-lg shadow-amber-500/25">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-lg">📖</div>
                <span class="text-[9px] font-black uppercase opacity-70">Subjects</span>
            </div>
            <p id="subjectsWithReports" class="text-3xl lg:text-4xl font-black leading-none">0</p>
            <p class="text-xs font-medium opacity-80 mt-1">รายวิชาที่มีรายงาน</p>
        </div>

        <div class="summary-card bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl p-5 text-white shadow-lg shadow-rose-500/25">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-lg">⚡</div>
                <span class="text-[9px] font-black uppercase opacity-70">Daily Avg</span>
            </div>
            <p id="avgPerDay" class="text-3xl lg:text-4xl font-black leading-none">0</p>
            <p class="text-xs font-medium opacity-80 mt-1">เฉลี่ยต่อวัน</p>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Bar Chart: Report Count -->
        <div class="stat-card p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-1.5 h-7 bg-gradient-to-b from-blue-500 to-indigo-500 rounded-full"></div>
                <h3 class="font-black text-slate-800 dark:text-white">สถิติการส่งรายงานรายบุคคล</h3>
            </div>
            <div style="position: relative; height: 300px;">
                <canvas id="reportCountChart"></canvas>
            </div>
        </div>

        <!-- Line Chart: Daily Trend -->
        <div class="stat-card p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-1.5 h-7 bg-gradient-to-b from-emerald-500 to-teal-500 rounded-full"></div>
                <h3 class="font-black text-slate-800 dark:text-white">แนวโน้มการส่งรายงานรายวัน</h3>
            </div>
            <div style="position: relative; height: 300px;">
                <canvas id="dailyTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Secondary Charts Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
        <!-- Doughnut Chart: Subject -->
        <div class="stat-card p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-1.5 h-6 bg-gradient-to-b from-amber-500 to-orange-500 rounded-full"></div>
                <h3 class="font-bold text-slate-800 dark:text-white text-sm">สัดส่วนรายวิชา</h3>
            </div>
            <div style="position: relative; height: 220px;">
                <canvas id="subjectCountChart"></canvas>
            </div>
        </div>

        <!-- Radar Chart: Methods -->
        <div class="stat-card p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-1.5 h-6 bg-gradient-to-b from-purple-500 to-violet-500 rounded-full"></div>
                <h3 class="font-bold text-slate-800 dark:text-white text-sm">รูปแบบการจัดการเรียนรู้</h3>
            </div>
            <div style="position: relative; height: 220px;">
                <canvas id="methodsChart"></canvas>
            </div>
        </div>

        <!-- Quality Analysis -->
        <div class="stat-card p-6 sm:col-span-2 xl:col-span-1">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-1.5 h-6 bg-gradient-to-b from-rose-500 to-pink-500 rounded-full"></div>
                <h3 class="font-bold text-slate-800 dark:text-white text-sm">คุณภาพรายงาน</h3>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div class="text-center">
                    <div class="quality-circle bg-blue-50 border-4 border-blue-100 mx-auto mb-3">
                        <i class="fas fa-image text-blue-500 text-xl"></i>
                    </div>
                    <p class="text-[9px] font-black text-slate-400 uppercase mb-1">รูปภาพ</p>
                    <p id="reportsWithImages" class="text-lg font-black text-blue-600">0%</p>
                </div>
                <div class="text-center">
                    <div class="quality-circle bg-emerald-50 border-4 border-emerald-100 mx-auto mb-3">
                        <i class="fas fa-comment-dots text-emerald-500 text-xl"></i>
                    </div>
                    <p class="text-[9px] font-black text-slate-400 uppercase mb-1">สะท้อนผล</p>
                    <p id="reportsWithReflection" class="text-lg font-black text-emerald-600">0%</p>
                </div>
                <div class="text-center">
                    <div class="quality-circle bg-rose-50 border-4 border-rose-100 mx-auto mb-3">
                        <i class="fas fa-exclamation-triangle text-rose-500 text-xl"></i>
                    </div>
                    <p class="text-[9px] font-black text-slate-400 uppercase mb-1">ปัญหา</p>
                    <p id="reportsWithProblems" class="text-lg font-black text-rose-600">0%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Table -->
    <div class="stat-card p-6 overflow-hidden">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-7 bg-gradient-to-b from-indigo-500 to-purple-500 rounded-full"></div>
                <h3 class="font-black text-slate-800 dark:text-white">สถิติรายสัปดาห์</h3>
            </div>
            <span id="tableCount" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold">0 คน</span>
        </div>
        
        <!-- Desktop Table -->
        <div class="hidden sm:block overflow-x-auto">
            <table id="weeklyTable" class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-400 uppercase text-[10px] font-black tracking-wider">
                        <th class="p-4 text-left rounded-l-lg">ชื่อครู</th>
                        <th class="p-4 text-center">คาดหวัง</th>
                        <th class="p-4 text-center">ส่งจริง</th>
                        <th class="p-4 text-center rounded-r-lg">ความสมบูรณ์</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="4" class="p-8 text-center text-slate-400 font-medium">กำลังโหลด...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div id="mobileWeeklyList" class="sm:hidden space-y-3 max-h-[50vh] overflow-y-auto">
            <div class="text-center py-8 text-slate-400">กำลังโหลด...</div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="js/department-stat.js"></script>
<script>
    const manager = new DepartmentStatManager({
        department: '<?php echo $department; ?>'
    });
</script>
